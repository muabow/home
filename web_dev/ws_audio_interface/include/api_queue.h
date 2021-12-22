#ifndef __API_QUEUE_H__
#define __API_QUEUE_H__

#include <tuple>
#include <vector>
#include <stdarg.h>

/* # QUEUE Handler API
  1. 개발 완료 목록 (2019.03.27, v0.0.0.1)
   + QueueHandler     
    : Circular queue
 
  2. 개발 예정 목록 
    : enqueue 시 mutex 처리에 대한 검토
    : enqueue / dequeue buffer gap 에 따른 latency 감소 방법 검토  
 */


using namespace std;

class QueueHandler {
	const int 	DFLT_QUEUE_SIZE	 = 1024 * 1024; // 1 Mbytes
	const int	DFLT_QUEUE_SCALE = 5;
	
	private:
		bool	is_run         = false;
		bool	is_loop        = false;
		bool	is_rotate      = false;
		bool	is_debug_print = false;
		
		int		buffer_cnt     = 0;
		int		buffer_min_cnt = 2;
		
		int		end_of_offset  = 0;
		int		enq_offset     = 0;
		
		char	*queue         = NULL;
		char	*tmp_queue     = NULL;
		
		int		list_idx       = 0;
		int		last_idx       = -1;
		int		offset_idx	   = -1;
		
		vector<tuple<int, int, bool>> v_offset_list;
	
	public :
		QueueHandler(bool _is_debug_print = false);
		~QueueHandler(void);
		
		bool  init(void);
		void  free(void);
		
		void  enqueue(char *_data, int _length);
		tuple<char *, int> dequeue(void);
		
		void set_min_dequeue_cnt(int _cnt);
		void set_offset_index(int _index);
		void set_debug_print(void);
		
		void print_debug_info(const char *_format, ...);
		void reset_queue_cnt(void);
};

#endif