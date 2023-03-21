#include "thread_pool.h"

static int  g_thread_max_count = DFLT_THREAD_POOL_SIZE;

void set_thread_max_count(int _count)
{
	if( _count <= 0 )
	{
		printf("%s::invalid size: %d, set default size: %d\n", __func__, _count, DFLT_THREAD_POOL_SIZE);
		g_thread_max_count = DFLT_THREAD_POOL_SIZE;
		return ;
	}
	g_thread_max_count = _count;

	printf("%s::set thread pool size: %d\n", __func__, g_thread_max_count);
	return;
}

int get_thread_max_count(void)
{
	return g_thread_max_count;
}

int thread_pool_init( THREAD_POOL_t *_t_pool )
{
	int thread_max_cnt = get_thread_max_count();
	printf("%s::thread pool size: %d\n", __func__, thread_max_cnt);

	if( pthread_mutex_init(&_t_pool->queue_mutex, NULL) != 0 )
	{
		printf("%s::pthread_mutex_init() failed: [%02d] %s\n", __func__, errno, strerror(errno));
		return -1;
	}

	if( pthread_cond_init(&_t_pool->queue_not_empty, NULL) != 0 )
	{
		pthread_mutex_destroy(&_t_pool->queue_mutex);

		printf("%s::pthread_cond_init() queue_not_empty failed: [%02d] %s\n", __func__, errno, strerror(errno));
		return -1;
	}

	if (pthread_cond_init(&_t_pool->queue_not_full, NULL) != 0)
	{
		pthread_mutex_destroy(&_t_pool->queue_mutex);
		pthread_cond_destroy(&_t_pool->queue_not_empty);

		printf("%s::pthread_cond_init() queue_not_full failed: [%02d] %s\n", __func__, errno, strerror(errno));
		return -1;
	}

	if( (_t_pool->threads = (pthread_t *)malloc(sizeof(pthread_t) * thread_max_cnt)) == NULL )
	{
		pthread_mutex_destroy(&_t_pool->queue_mutex);
		pthread_cond_destroy(&_t_pool->queue_not_empty);
		pthread_cond_destroy(&_t_pool->queue_not_full);

		printf("%s::malloc() failed: [%02d] %s\n", __func__, errno, strerror(errno));
		return -1;
	}

	_t_pool->thread_count	= 0;
	_t_pool->head			= 0;
	_t_pool->tail			= 0;
	_t_pool->count			= 0;
	_t_pool->shutdown		= THREAD_POOL_STARTUP;

	int idx;
	for( idx = 0; idx < thread_max_cnt ; idx++ )
	{
		if (pthread_create(&(_t_pool->threads[idx]), NULL, worker_thread, (void *)_t_pool) != 0)
		{
			thread_pool_destroy(_t_pool);

			printf("%s::pthread_create() failed idx[%02d]: [%02d] %s\n", __func__, idx, errno, strerror(errno));
			return -1;
		}
		_t_pool->thread_count++;
	}

	printf("%s::init success\n", __func__);
	return 0;
}

int thread_pool_add_task(THREAD_POOL_t *_t_pool, void (*_function)(void *), void *_argument)
{
	pthread_mutex_lock(&_t_pool->queue_mutex);

	while( _t_pool->count == DFLT_TASK_QUEUE_SIZE && _t_pool->shutdown == THREAD_POOL_STARTUP )
	{
		printf("%s::queue full: %d/%d\n", __func__, _t_pool->count, DFLT_TASK_QUEUE_SIZE);
		pthread_cond_wait(&_t_pool->queue_not_full, &_t_pool->queue_mutex);
	}

	if( _t_pool->shutdown == THREAD_POOL_SHUTDOWN )
	{
		pthread_mutex_unlock(&_t_pool->queue_mutex);

		printf("%s::already destoryed\n", __func__);
		return -1;
	}

	_t_pool->task_queue[_t_pool->tail].function = _function;
	_t_pool->task_queue[_t_pool->tail].argument = _argument;
	_t_pool->tail++;
	_t_pool->tail %= DFLT_TASK_QUEUE_SIZE;
	_t_pool->count++;

	pthread_cond_signal(&_t_pool->queue_not_empty);
	pthread_mutex_unlock(&_t_pool->queue_mutex);

	return 0;
}

int thread_pool_destroy(THREAD_POOL_t *_t_pool)
{
	if( _t_pool->shutdown == THREAD_POOL_SHUTDOWN )
	{
		printf("%s::already destoryed\n", __func__);
		return -1;
	}
	_t_pool->shutdown = THREAD_POOL_SHUTDOWN;

	pthread_mutex_lock(&_t_pool->queue_mutex);
	pthread_cond_broadcast(&_t_pool->queue_not_empty);
	pthread_cond_broadcast(&_t_pool->queue_not_full);
	pthread_mutex_unlock(&_t_pool->queue_mutex);

	int idx;
	for( idx = 0 ; idx < _t_pool->thread_count ; idx++ )
	{
		pthread_join(_t_pool->threads[idx], NULL);
	}
	free(_t_pool->threads);

	pthread_mutex_destroy(&_t_pool->queue_mutex);
	pthread_cond_destroy(&_t_pool->queue_not_empty);
	pthread_cond_destroy(&_t_pool->queue_not_full);

	printf("%s::thread pool destoryed\n", __func__);
	return 0;
}

void *worker_thread(void *_arg)
{
	THREAD_POOL_t *t_pool = (THREAD_POOL_t *)_arg;
	TASK_t t_task;

	while( true )
	{
		pthread_mutex_lock(&t_pool->queue_mutex);
		while( t_pool->count == 0 && t_pool->shutdown == THREAD_POOL_STARTUP )
		{
			pthread_cond_wait(&t_pool->queue_not_empty, &t_pool->queue_mutex);
		}

		if( t_pool->shutdown == THREAD_POOL_SHUTDOWN )
		{
			pthread_mutex_unlock(&t_pool->queue_mutex);
			pthread_exit(NULL);
		}
		t_task = t_pool->task_queue[t_pool->head++];
		t_pool->head %= DFLT_TASK_QUEUE_SIZE;
		t_pool->count--;

		pthread_cond_signal(&t_pool->queue_not_full);
		pthread_mutex_unlock(&t_pool->queue_mutex);

		(*(t_task.function))(t_task.argument);
	}

	pthread_exit(NULL);
}
