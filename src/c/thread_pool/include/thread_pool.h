#ifndef __THREAD_POOL_H__
#define __THREAD_POOL_H__

#include <stdio.h>
#include <stdlib.h>
#include <pthread.h>
#include <unistd.h>
#include <stdbool.h>
#include <string.h>
#include <errno.h>

#define DFLT_THREAD_POOL_SIZE	10	/* maximum number of threads in a thread pool */
#define DFLT_TASK_QUEUE_SIZE	100 /* maximum number of tasks in a task queue */

#define THREAD_POOL_STARTUP		0
#define THREAD_POOL_SHUTDOWN	1


typedef struct {
	void (*function)(void *); 		/* function pointer to a task function */
	void *argument;			  		/* arguments for a task function */
} TASK_t;

typedef struct {
	TASK_t task_queue[DFLT_TASK_QUEUE_SIZE];	/* task queue */
	int head;									/* task index in a task queue */
	int tail;
	int count;									/* current number of tasks in a task queue */

	pthread_mutex_t queue_mutex;				/* mutex for a queue */
	pthread_cond_t queue_not_empty; 			/* condition variable for signaling when the task queue is not empty */
	pthread_cond_t queue_not_full;				/* condition variable for signaling when the task queue is not full */
	
	pthread_t *threads;							/* array of threads in a thread pool */
	int thread_count;							/* current number of threads in a thread pool */
	
	char shutdown;								/* termination status of a thread pool */
	char rsvd[3];
} THREAD_POOL_t;


void	set_thread_max_count(int _count);
int		get_thread_max_count(void);

int 	thread_pool_init(THREAD_POOL_t *_t_pool);
int 	thread_pool_destroy(THREAD_POOL_t *_t_pool);
int     thread_pool_add_task(THREAD_POOL_t *_t_pool, void (*function)(void *), void *_argument);

void	*worker_thread(void *_arg);

#endif 