#include <stdio.h>
#include <unistd.h>
#include <errno.h>
#include <string.h>

#include <iostream>
#include <cstring>

#include "class_queue_handler.h"

namespace COMMON {
	using namespace std;
	
	QueueHandler::QueueHandler(bool _is_debug_print) {
		this->is_debug_print = _is_debug_print;
		
		this->print_debug_info("QueueHandler() create instance\n");
		
		this->init();
		
		return ;
	}
	
	QueueHandler::~QueueHandler(void) {
		this->print_debug_info("QueueHandler() instance destructed\n");
		
		this->free();
		
		return ;
	}
	
	void QueueHandler::print_debug_info(const char *_format, ...) {
		if( !this->is_debug_print ) return ;
		
		va_list arg;
		
		fprintf(stderr, "QueueHandler::");
		
		va_start(arg, _format);
		vprintf(_format, arg);
		va_end(arg);
		
		return ;
	}
	
	void QueueHandler::set_debug_print(void) {
		this->is_debug_print = true;

		this->print_debug_info("set_debug_print() is set on\n");
		
		return ;
	}
	
	bool QueueHandler::init(void) {
		if( this->is_run ) {
			this->print_debug_info("init() instance already created\n");
			
			return false;
		}
		this->end_of_offset = this->DFLT_QUEUE_SIZE * DFLT_QUEUE_SCALE;
		
		this->queue  = new char[this->end_of_offset];
		this->is_run = true;

		return true;
	}
	
	void QueueHandler::free(void) {
		this->print_debug_info("free() called\n");
		
		if( this->is_run ) {
			this->is_run = false;
			
			if( this->tmp_queue != NULL ) {
				delete this->tmp_queue;
				this->tmp_queue = NULL;
			}
			
			if( this->queue != NULL ) {
				delete this->queue;
				this->queue = NULL;
				
				this->print_debug_info("delete queue success\n");
			}
		}
		
		return ;
	}

	void QueueHandler::enqueue(char *_data, int _length) {
		if( !this->is_run ) {
			this->print_debug_info("enqueue() instance not created\n");
		
			return ;
		}
		int data_size = _length;
		int remain_offset = 0;
		tuple<int, int, bool> offset_info;
		
		// queue data
		if( this->enq_offset == this->end_of_offset ) {
			this->enq_offset = 0;
		
		} else if( this->enq_offset + data_size > this->end_of_offset ) {
			remain_offset = this->end_of_offset - this->enq_offset;
			
			offset_info = make_tuple(this->enq_offset, remain_offset, this->is_rotate);

			memmove(this->queue + this->enq_offset, _data, remain_offset);
			data_size = data_size - remain_offset;
			
			this->enq_offset = 0;

			if( this->is_loop ) {
				this->v_offset_list[this->list_idx] = offset_info;
				this->is_rotate = !this->is_rotate;
				
			} else {
				this->v_offset_list.push_back(offset_info);
			
				this->last_idx  = this->v_offset_list.size(); 
				this->is_loop   = true;
				this->is_rotate = !this->is_rotate;
			}
			
			if( ++this->list_idx == this->last_idx && this->last_idx != -1 ) {
				this->list_idx = 0;
			}
		}
		offset_info = make_tuple(this->enq_offset, data_size, this->is_rotate);

		memmove(this->queue + this->enq_offset, _data + remain_offset, data_size);
		this->enq_offset += data_size;
		
		if( this->is_loop ) {
			this->v_offset_list[this->list_idx] = offset_info;

		} else {
			this->v_offset_list.push_back(offset_info);
			
		}
		
		if( ++this->list_idx == this->last_idx && this->last_idx != -1 ) {
			this->list_idx = 0;
		}

		this->buffer_cnt++;
		
		return ;
	}
	
	tuple<char *, int> QueueHandler::dequeue(void) {
		char *ptr_queue = NULL;
		
		if( !this->is_run ) {
			this->print_debug_info("dequeue() instance not created\n");
			return make_tuple((char *)NULL, 0);
		}
		
		if( this->buffer_cnt <= this->buffer_min_cnt ) {
			return make_tuple((char *)NULL, 0);
		}

		if( this->offset_idx == -1 ) { 
			this->offset_idx = this->list_idx - this->buffer_min_cnt < 0 ? 0 : this->list_idx - this->buffer_min_cnt;
		}
		this->buffer_cnt--;
		
		tuple<int, int, bool> offset_info = this->v_offset_list[this->offset_idx];
		if( ++this->offset_idx == this->last_idx && this->last_idx != -1 ) {
			this->offset_idx = 0;
		}
		
		int  offset    = get<0>(offset_info);
		int  data_size = get<1>(offset_info);
		bool rotate	   = get<2>(offset_info);
		
		tuple<int, int, bool> offset_info_r = this->v_offset_list[this->offset_idx];
		bool rotate_r  = get<2>(offset_info_r);
		
		if( offset + data_size == this->end_of_offset && (rotate != rotate_r) ) {
			if( ++this->offset_idx == this->last_idx && this->last_idx != -1 ) {
				this->offset_idx = 0;
			}

			int offset_r    = get<0>(offset_info_r);
			int data_size_r = get<1>(offset_info_r);
			
			if( this->tmp_queue != NULL ) {
				delete this->tmp_queue;
				this->tmp_queue = NULL;
			}
			
			this->tmp_queue = new char[data_size_r + data_size];

			memcpy(this->tmp_queue, this->queue + offset, data_size);
			memcpy(this->tmp_queue + data_size, this->queue + offset_r, data_size_r);
			
			ptr_queue = this->tmp_queue;
			data_size = data_size + data_size_r;
		
		} else {
			ptr_queue = this->queue + offset;
		}

		return make_tuple(ptr_queue, data_size);
	}
	
	void QueueHandler::set_min_dequeue_cnt(int _cnt) {
		if( _cnt < 2 ) {
			this->print_debug_info("set_min_dequeue_cnt() invalid valid [%d], value > 1 \n", _cnt);	
			return ;
		}
		this->print_debug_info("set_min_dequeue_cnt() buffer [%d] change to [%d]\n", this->buffer_min_cnt, _cnt);
		
		this->buffer_min_cnt = _cnt;
		
		return ;
	}
	
	void QueueHandler::set_offset_index(int _index) {
		if( _index < 1 ) {
			this->print_debug_info("set_offset_index() invalid valid [%d], value > 0 \n", _index);	
			return ;
		}
		this->print_debug_info("set_offset_index() change to [%d]\n", _index);
		
		this->offset_idx = _index;
	
		return ;
	}
	
	void QueueHandler::reset_queue_cnt(void) {
		this->buffer_cnt = 0;
		this->offset_idx = this->list_idx - this->buffer_min_cnt < 0 ? 0 : this->list_idx - this->buffer_min_cnt;
		
		return ;
	}
}
