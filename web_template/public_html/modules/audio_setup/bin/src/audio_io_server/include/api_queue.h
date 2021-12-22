#ifndef __API_QUEUE_H__
#define __API_QUEUE_H__

#include <stdio.h>
#include <unistd.h>
#include <errno.h>
#include <string.h>

#include <iostream>
#include <cstring>

#include <tuple>
#include <vector>
#include <mutex>

#include <stdarg.h>

using namespace std;

class QueueHandler {
	static const int	DFLT_QUEUE_UNIT	 = 1024;
	static const int 	DFLT_QUEUE_COUNT = 1000;
	static const int	DFLT_QUEUE_SCALE = 1;
	
	private:
		bool	is_run        	= false;
		
		bool	is_debug_print 	= false;
		
		int		buffer_cnt     	= 0;
		int		buffer_min_cnt 	= 0;
		
		int		end_of_offset  	= 0;
		int		enq_offset     	= 0;
		
		char	*queue         	= NULL;
		
		int		last_idx       	= -1;
		int		enq_idx        	= 0;
		int		deq_idx			= 0;
		
		int		num_queue_scale	= -1;
		int		num_queue_unit	= -1;
		
		void print_debug_info(const char *_format, ...);

		vector<tuple<int, int, bool>> v_offset_list;
		mutex	mutex_queue;

		string	str_name;
		
	public :
		QueueHandler(bool _is_debug_print = false);
		~QueueHandler(void);
		
		bool  	init(int _scale = DFLT_QUEUE_SCALE);
		void  	free(void);
		
		void  	enqueue(char *_data, int _length);
		tuple<char *, int> dequeue(void);
		
		void 	set_debug_print(string _name = "");
		void 	set_min_dequeue_cnt(int _cnt);
		void 	set_dequeue_index(int _index);
		void 	set_no_buffer_count(void);
		
		int  	get_dequeue_index(void);
		int  	get_enqueue_index(void);
		int  	get_queue_cnt(void);
		int  	get_min_dequeue_cnt(void);

		void 	reset_queue_cnt(void);
		void 	decrease_buffer_cnt(void);
		
		int		get_queue_unit(void);
		void	reset_queue_unit(int _unit_size);
		int		get_queue_size(void);
};

#endif