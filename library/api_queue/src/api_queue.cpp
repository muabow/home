#include <stdio.h>
#include <unistd.h>
#include <errno.h>
#include <string.h>

#include <iostream>
#include <cstring>


#include "api_queue.h"

using namespace std;

QueueHandler::QueueHandler(bool _is_debug_print) {
	if( _is_debug_print ) {
		this->set_debug_print();
	}
	
	this->print_debug_info("QueueHandler() create instance\n");
	
	this->num_queue_unit  = DFLT_QUEUE_UNIT;
	this->num_queue_scale = DFLT_QUEUE_SCALE;
	return ;
}

QueueHandler::~QueueHandler(void) {
	this->print_debug_info("QueueHandler() instance destructed\n");
	
	this->free();
	
	return ;
}

void QueueHandler::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	fprintf(stdout, "QueueHandler::");

	va_list arg;
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

bool QueueHandler::init(int _scale) {
	if( this->is_run ) {
		this->print_debug_info("init() instance already created\n");
		
		return false;
	}
	
	if( _scale != DFLT_QUEUE_SCALE ) {
		this->print_debug_info("init() change queue sacle [%d] -> [%d]\n", this->num_queue_scale, _scale);
		this->num_queue_scale = _scale;
	}
	
	this->end_of_offset	= this->num_queue_unit * DFLT_QUEUE_COUNT  * this->num_queue_scale;
	
	
	this->queue  = new char[this->end_of_offset];
	this->buffer_cnt = 0;
	this->enq_offset = 0;

	this->last_idx	 = -1;
	this->enq_idx    = 0;
	this->deq_idx	 = 0;
	
	this->v_offset_list.clear();

	this->is_run = true;

	return true;
}

void QueueHandler::free(void) {
	if( this->is_run ) {
		this->print_debug_info("free() called\n");
		
		this->is_run = false;
		
		if( this->tmp_queue != NULL ) {
			delete [] this->tmp_queue;
			this->tmp_queue = NULL;
		}
		
		if( this->queue != NULL ) {
			delete [] this->queue;
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
	
	int data_size	= _length;
	int remain_size	= 0; 
	int is_remain	= false;
	
	int end_queue;
	
	tuple<int, int, bool> offset_info;
	
	if( this->enq_offset == this->end_of_offset ) {
		if( !this->is_loop ) {
			offset_info = make_tuple(0, -1, false);
			
			this->v_offset_list.push_back(offset_info);
		}
		
		this->last_idx	 = this->enq_idx;
		this->is_loop	 = true;

		this->enq_offset = 0;
		this->enq_idx	 = 0;
	
	} else if( this->enq_offset + data_size > this->end_of_offset ) {
		is_remain = true;
		
		if( !this->is_loop ) {
			remain_size = this->end_of_offset - this->enq_offset;
			
			offset_info = make_tuple(this->enq_offset, remain_size, is_remain);
			memmove(this->queue + this->enq_offset, _data, remain_size);
			
			this->v_offset_list.push_back(offset_info);

			this->enq_idx++;
			
			offset_info = make_tuple(0, -1, false);
			this->v_offset_list.push_back(offset_info);
			
			this->last_idx	 = this->enq_idx;
			this->is_loop	 = true;

			this->enq_offset = 0;
			this->enq_idx	 = 0;
		
			data_size = data_size - remain_size;
			offset_info = make_tuple(this->enq_offset, data_size, is_remain);
			this->v_offset_list[this->enq_idx] = offset_info;

			memmove(this->queue + this->enq_offset, _data + remain_size, data_size);
			this->enq_offset += data_size;
			this->enq_idx++;
			
			this->buffer_cnt++;
			
			return ;
			
		} else {
			offset_info = this->v_offset_list[this->enq_idx];
			end_queue = get<1>(offset_info);
			
			if( end_queue == -1 ) { 
				this->enq_idx    = 0;
				this->enq_offset = 0;
				
				offset_info = make_tuple(this->enq_offset, data_size, false);
				this->v_offset_list[this->enq_idx] = offset_info;

				memmove(this->queue + this->enq_offset, _data, data_size);
				this->enq_offset += data_size;
				
				offset_info = make_tuple(0, 0, false);
				this->v_offset_list[this->enq_idx + 1] = offset_info;
				this->enq_idx++;

				this->buffer_cnt++;
				
				return ;
			}
			
			offset_info = this->v_offset_list[this->enq_idx + 1];
			end_queue = get<1>(offset_info);
			
			if( end_queue == -1 ) { 
				remain_size = this->end_of_offset - this->enq_offset;
						
				offset_info = make_tuple(this->enq_offset, remain_size, is_remain);
				this->v_offset_list[this->enq_idx] = offset_info;

				memmove(this->queue + this->enq_offset, _data, remain_size);
				this->enq_offset = 0;
				this->enq_idx    = 0;
				
				data_size = data_size - remain_size;
				offset_info = make_tuple(this->enq_offset, data_size, is_remain);
				this->v_offset_list[this->enq_idx] = offset_info;

				memmove(this->queue + this->enq_offset, _data + remain_size, data_size);
				this->enq_offset += data_size;
				
				offset_info = make_tuple(0, 0, false);
				this->v_offset_list[this->enq_idx + 1] = offset_info;
				this->enq_idx++;
				
				this->buffer_cnt++;
				
				return ;
			}
		}
	}
	
	offset_info = make_tuple(this->enq_offset, data_size, is_remain);
	memmove(this->queue + this->enq_offset, _data, data_size);
	this->enq_offset += data_size;
	
	if( !this->is_loop ) {
		this->v_offset_list.push_back(offset_info);
	
	} else {
		this->v_offset_list[this->enq_idx] = offset_info;
		
		offset_info = this->v_offset_list[this->enq_idx + 1];
		end_queue = get<1>(offset_info);
		
		if( end_queue != -1 ) { 
			offset_info = make_tuple(0, 0, false);
			this->v_offset_list[this->enq_idx + 1] = offset_info;
		}
	}
	this->enq_idx++;
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
	
	if( this->buffer_cnt <= 0 ) {
		return make_tuple((char *)NULL, 0);
	}

	tuple<int, int, bool> offset_info = this->v_offset_list[this->deq_idx];
	
	int  offset    = get<0>(offset_info);
	int  data_size = get<1>(offset_info);
	bool is_remain = get<2>(offset_info);
	
	if( data_size == 0 ) {
		return make_tuple((char *)NULL, 0);
	}
	
	if( is_remain ) {
		this->deq_idx = 0;
				
		tuple<int, int, bool> offset_info_r = this->v_offset_list[this->deq_idx];

		int offset_r    = get<0>(offset_info_r);
		int data_size_r = get<1>(offset_info_r);
		
		if( this->tmp_queue != NULL ) {
			delete [] this->tmp_queue;
			this->tmp_queue = NULL;
		}
		
		this->tmp_queue = new char[data_size_r + data_size];

		memcpy(this->tmp_queue, this->queue + offset, data_size);
		memcpy(this->tmp_queue + data_size, this->queue + offset_r, data_size_r);
		
		ptr_queue = this->tmp_queue;
		data_size = data_size + data_size_r;

	} else { 
		if( data_size == -1 ) {
			this->deq_idx = 0;
			offset_info = this->v_offset_list[this->deq_idx];
				
			offset    = get<0>(offset_info);
			data_size = get<1>(offset_info);
			is_remain = get<2>(offset_info);
		}
		
		ptr_queue = this->queue + offset;
	}
	this->deq_idx++;
	this->buffer_cnt--;
	
	return make_tuple(ptr_queue, data_size);
}

void QueueHandler::set_min_dequeue_cnt(int _cnt) {
	this->print_debug_info("set_min_dequeue_cnt() buffer [%d] change to [%d]\n", this->buffer_min_cnt, _cnt);
	
	this->buffer_min_cnt = _cnt;
	
	return ;
}

int QueueHandler::get_min_dequeue_cnt(void) {
	
	return this->buffer_min_cnt;
}


void QueueHandler::set_dequeue_index(int _index) {
	this->print_debug_info("set_dequeue_index() change to [%d]\n", _index);
	
	this->deq_idx = _index;

	return ;
}

int QueueHandler::get_dequeue_index(void) {
	
	return this->deq_idx;
}

int QueueHandler::get_enqueue_index(void) {
	
	return this->enq_idx;
}

void QueueHandler::reset_queue_cnt(void) {
	this->buffer_cnt = 0;
	this->deq_idx	 = 0;
	this->enq_idx    = 0;
	
	return ;
}

int  QueueHandler::get_queue_cnt(void) {
	
	return this->buffer_cnt;
}

void QueueHandler::decrease_buffer_cnt(void) {
	if( ++this->deq_idx == this->last_idx && this->last_idx != -1 ) {
		this->deq_idx = 0;
	}

	this->buffer_cnt--;
	
	return ;
}

int QueueHandler::get_queue_unit(void) {

	return this->num_queue_unit;
}

void QueueHandler::reset_queue_unit(int _unit_size) {
	if( this->num_queue_unit != _unit_size ) {
		this->print_debug_info("reset_queue_unit() queue unit [%d] change to [%d]\n", this->num_queue_unit, _unit_size);
		this->num_queue_unit = _unit_size;
		
		if( this->queue != NULL ) {
			delete [] this->queue;
			this->queue = NULL;
		}
		
		this->end_of_offset	= this->num_queue_unit * DFLT_QUEUE_COUNT  * this->num_queue_scale;
		this->queue = new char[this->end_of_offset];
		
		this->buffer_cnt = 0;
		this->enq_offset = 0;

		this->last_idx	 = -1;
		this->enq_idx    = 0;
		this->deq_idx	 = 0;
		
		this->v_offset_list.clear();
	}
	
	return ;
}

int	QueueHandler::get_queue_size(void) {
	
	return this->end_of_offset;
}