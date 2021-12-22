#include "api_queue.h"

using namespace std;

QueueHandler::QueueHandler(bool _is_debug_print) {
	if( _is_debug_print ) {
		this->set_debug_print();
	}
	
	this->print_debug_info("QueueHandler() create instance\n");
	
	this->num_queue_unit  = DFLT_QUEUE_UNIT;
	this->num_queue_scale = DFLT_QUEUE_SCALE;

	this->queue = NULL;

	this->str_name = "";

	return ;
}

QueueHandler::~QueueHandler(void) {
	this->print_debug_info("QueueHandler() instance destructed\n");
	
	this->free();
	
	return ;
}

void QueueHandler::print_debug_info(const char *_format, ...) {
	if( !this->is_debug_print ) return ;
	
	if( this->str_name.compare("") == 0 ) {
		fprintf(stdout, "QueueHandler::");
	
	} else {
		fprintf(stdout, "QueueHandler[%s]::", this->str_name.c_str());
	}

	va_list arg;
	va_start(arg, _format);
	vprintf(_format, arg);
	va_end(arg);
	
	return ;
}

void QueueHandler::set_debug_print(string _name) {
	this->is_debug_print = true;
	this->str_name = _name;

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
	
	if( this->queue != NULL ) {
		delete [] this->queue;
		this->queue = NULL;
	}

	int num_queue_cnt   = DFLT_QUEUE_COUNT * this->num_queue_scale;;
	this->end_of_offset	= this->num_queue_unit * num_queue_cnt;

	this->queue = new char[this->end_of_offset];
	this->buffer_cnt = 0;
	this->enq_offset = 0;

	this->last_idx	 = -1;
	this->enq_idx    = 0;
	this->deq_idx	 = 0;
	
	this->v_offset_list.clear();
	
	this->print_debug_info("init() change vector size : [%d]\n", num_queue_cnt);
	this->v_offset_list.reserve(num_queue_cnt);

	this->is_run = true;

	return true;
}

void QueueHandler::free(void) {
	if( this->is_run ) {
		this->print_debug_info("free() called\n");
		
		this->is_run = false;
		
		if( this->queue != NULL ) {
			delete [] this->queue;
			this->queue = NULL;
			
			this->print_debug_info("delete queue success\n");
		}
		
		this->v_offset_list.clear();
	}
	
	return ;
}

void QueueHandler::enqueue(char *_data, int _length) {
	if( !this->is_run ) {
		this->print_debug_info("enqueue() instance not created\n");
	
		return ;
	}
	
	int data_size = _length;
	tuple<int, int, bool> tp_offset_info;
	
	if( this->enq_offset == this->end_of_offset ) {
		this->enq_offset = 0;
		this->enq_idx	 = 0;
	} 
	
	memmove(this->queue + this->enq_offset, _data, data_size);
	tp_offset_info = make_tuple(this->enq_offset, data_size, false);
	
	this->v_offset_list[this->enq_idx] = tp_offset_info;

	this->enq_offset += data_size;
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
	
	if( this->buffer_cnt < 0 ) {
		return make_tuple((char *)NULL, 0);
	}

	if( this->deq_idx == (DFLT_QUEUE_COUNT * this->num_queue_scale) ) {
		this->deq_idx = 0;
	}

	tuple<int, int, bool> tp_offset_info;
	tp_offset_info = this->v_offset_list[this->deq_idx];
	int  offset    = get<0>(tp_offset_info);
	int  data_size = get<1>(tp_offset_info);

	ptr_queue = this->queue + offset;
	this->deq_idx++;

	this->buffer_cnt--;

	return make_tuple(ptr_queue, data_size);
}

void QueueHandler::set_min_dequeue_cnt(int _cnt) {
	if( this->buffer_min_cnt != _cnt ) {
		this->print_debug_info("set_min_dequeue_cnt() buffer [%d] change to [%d]\n", this->buffer_min_cnt, _cnt);
		
		this->buffer_min_cnt = _cnt;
	}
	
	return ;
}

int QueueHandler::get_min_dequeue_cnt(void) {
	
	return this->buffer_min_cnt;
}


void QueueHandler::set_dequeue_index(int _index) {
	if( this->deq_idx != _index ) {
		this->print_debug_info("set_dequeue_index() change to [%d]\n", _index);
		
		this->deq_idx = _index;
	}
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