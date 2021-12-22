/*
 * devmem2.c: Simple program to read/write from/to any location in memory.
 *
 *  Copyright (C) 2000, Jan-Derk Bakker (J.D.Bakker@its.tudelft.nl)
 *
 *
 * This software has been developed for the LART computing board
 * (http://www.lart.tudelft.nl/). The development has been sponsored by
 * the Mobile MultiMedia Communications (http://www.mmc.tudelft.nl/)
 * and Ubiquitous Communications (http://www.ubicom.tudelft.nl/)
 * projects.
 *
 * The author can be reached at:
 *
 *  Jan-Derk Bakker
 *  Information and Communication Theory Group
 *  Faculty of Information Technology and Systems
 *  Delft University of Technology
 *  P.O. Box 5031
 *  2600 GA Delft
 *  The Netherlands
 *
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 */

#ifndef __DEV_MEM_H__
#define __DEV_MEM_H__

#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <string.h>
#include <errno.h>
#include <signal.h>
#include <fcntl.h>
#include <ctype.h>
#include <termios.h>
#include <sys/types.h>
#include <sys/mman.h>

#define MAP_SIZE 4096UL
#define MAP_MASK (MAP_SIZE - 1)

class DevMem {
public:
	DevMem(uint32_t physicalAddress) 
		: _fd(-1)
		, _mappedBaseAddress(NULL)
		, _virtualAddress(0)
	{
		if ((_fd = open("/dev/mem", O_RDWR | O_SYNC)) == -1) {
			throw "ERROR : Can not open memory";
//			return;
		}
//		printf("/dev/mem opened.\n"); 
//		fflush(stdout);
		
		/* Map one page */
		_mappedBaseAddress = mmap(0, MAP_SIZE, PROT_READ | PROT_WRITE, MAP_SHARED, _fd, physicalAddress & ~MAP_MASK);
		if (_mappedBaseAddress == (void *) -1) {
			throw "ERROR : Can not mmap memory";
//			return;
		}
//		printf("Memory mapped at address %p.\n", map_base); 
//		fflush(stdout);

		_virtualAddress = (char*)_mappedBaseAddress + (physicalAddress & MAP_MASK);	
	}
	
	virtual ~DevMem() {
		munmap(_mappedBaseAddress, MAP_SIZE);
	    close(_fd);
	}

	/**
		@brief	메모리에 접근하여 값을 설정 (32bit 단위)
	 */
	void set(uint32_t value) {
		// Write
		*((unsigned long *) _virtualAddress) = value;
	}

	/**
		@brief	메모리에 접근하여 값을 읽음 (32bit 단위)
	 */
	uint32_t get() {
		// Read
		const uint32_t read_result = *((unsigned long *) _virtualAddress);
		return read_result;
	}


private:
	int32_t _fd;
	void* _mappedBaseAddress;
	const void* _virtualAddress;
};

#endif //__DEV_MEM_H__