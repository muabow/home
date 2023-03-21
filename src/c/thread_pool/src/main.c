#include <stdio.h>
#include <stdlib.h>
#include <stdbool.h>
#include <unistd.h>
#include "thread_pool.h"

/* function declaration */
void task_function(void *argument);


/* task function */
void task_function(void *_argument)
{
	int task_number = *(int *)_argument;
	printf("Task %d started.\n", task_number);
	sleep(1);
	printf("Task %d completed.\n", task_number);


	/* freeing a variable allocated */
	if( _argument != NULL ) 
	{
		free(_argument);
		_argument = NULL;
	}

	return;
}


/* main function */
int main()
{
	THREAD_POOL_t t_thread_pool;

	/* change thread pool max thread count [Optional] */
	set_thread_max_count(20);

	/* Default: thread pool max thread count: 10 */
	if( thread_pool_init(&t_thread_pool) < 0 )
		return -1;

	int task_count = 30;	
	int idx;
	for( idx = 0 ; idx < task_count ; idx++ )
	{
		int *argument = (int *)malloc(sizeof(int));
		*argument = idx;
		thread_pool_add_task(&t_thread_pool, task_function, (void *)argument);
	}

	int loop_idx = 0;
	while( true )
	{
		sleep(1);
		printf("%s::wait..\n", __func__);

		int *argument = (int *)malloc(sizeof(int));
		*argument = loop_idx++;
		thread_pool_add_task(&t_thread_pool, task_function, argument);

		sleep(1);
	}

	thread_pool_destroy(&t_thread_pool);

	return 0;
}
