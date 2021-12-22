/**
	@file
	@brief	ANSI Color 정의
	@author	Han, DooGyeong (dghan@inter-m.com)
	@date	2020.01.30
 */

#ifndef __ANSI_COLOR_H__
#define __ANSI_COLOR_H__



// ANSI : 출력용 - 
const char* const ANSI_DELETE_LINE		= "\033[1M";
const char* const ANSI_COLOR_RED		= "\033[1;31m";
const char* const ANSI_COLOR_GREEN		= "\033[1;32m";
const char* const ANSI_COLOR_YELLOW		= "\033[1;33m";
const char* const ANSI_COLOR_BLUE		= "\033[1;34m";
const char* const ANSI_COLOR_MAGENTA	= "\033[1;35m";
const char* const ANSI_COLOR_CYAN		= "\033[1;36m";
const char* const ANSI_COLOR_RESET		= "\033[0m";


const char* const COLOR_FATAL			= ANSI_COLOR_RED;
const char* const COLOR_ERROR			= ANSI_COLOR_RED;
const char* const COLOR_WARNING			= ANSI_COLOR_MAGENTA;
const char* const COLOR_INFO			= ANSI_COLOR_YELLOW;
const char* const COLOR_DEBUG			= ANSI_COLOR_GREEN;
const char* const COLOR_TRACE			= ANSI_COLOR_CYAN;

const char* const COLOR_RESET			= ANSI_COLOR_RESET;


#endif	// __ANSI_COLOR_H__
