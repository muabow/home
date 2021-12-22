#ifndef __CLASS_QUEUE_HANDLER_H__
#define __CLASS_QUEUE_HANDLER_H__

#include <tuple>
#include <vector>
#include <stdarg.h>

namespace COMMON {
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
			void reset_queue_cnt(void);
			
			void print_debug_info(const char *_format, ...);
			void set_debug_print(void);
	};
}

#endif