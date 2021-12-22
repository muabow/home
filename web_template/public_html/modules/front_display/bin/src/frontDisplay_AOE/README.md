# frontDisplay : Front Display (OLED, SPI/GPIO)
----------------------------------------
※ 본 문서는 Markdown Format 으로 작성되어 Markdown 을 지원하는 Viewer / Editor 를 사용하시면 더 깔끔하게 보실 수 있습니다.


## 1. frontDisplay 란?
전면부에 내장된 OLED 와 Rotary Encoder 를 활용하여 시스템 정보를 출력하는 UI Application

부팅시 자동실행을 위한 script 내장

- Resolution : 128x64
- Color : 1bit (Black/White)
- Driver : ssd1306


## 2. 설치
### 2-1 Package 다운 및 설치 (개발 보드에서 실행)

	## svn 에서 deb Package 받기
	$ svn checkout http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/ .
	
	## 설치 (업데이트 동일)
	## 항상 최신 버전을 확인하여 설치 할 것!
	
	$ sudo dpkg -i frontDisplay_0.0.0.1_armhf.deb


### 2-2 deb Package 정보 확인
  버전이 동일하여도 빌드 날짜가 다를 수 있음

	## Package 정보 확인
	$ dpkg -I frontDisplay_0.0.0.1_armhf.deb
	 new debian package, version 2.0.
	 size 64590 bytes: control archive=538 bytes.
	     178 bytes,     6 lines      control
	     140 bytes,     8 lines   *  postinst             #!/bin/sh
	     219 bytes,    10 lines   *  preinst              #!/bin/sh
	     219 bytes,    10 lines   *  prerm                #!/bin/sh
	 Package: frontDisplay
	 Version: 0.0.0.1+20170530
	 Architecture: armhf
	 Depends: libavmsapi
	 Maintainer: DooGyeong Han <dghan@inter-m.com>
	 Description: Front Display (OLED, SPI/GPIO)


## 3. Package 생성 (개발 보드에서 실행 권장)
frontDisplay 의 Build 및 Package 를 생성 하기 위해서는 libavmsapi 가 반드시 사전 설치되어 함

개발 보드 외에 x86/x64 의 Linux 머신에서도 cross-compile 이 가능 하나 
libavmsapi 설치를 위해 Library/Include Path 설정에 대한 이해가 필요

	## Source Code 받기
	$ svn checkout http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/api/frontDisplay .
	
	## Version 명 수정
	$ vi version
	0.0.0.2
	
### 3-1 기본 Package 생성
	## Build
	$ make

	## Package 생성 (Build 기능 포함)
	$ make dist
	
### 3-2 OEM 별 Package 생성
	## OEM.lst 파일 수정
	$ vi OEM.lst
	INTERM	interm
	IMP	imp

	# Logo 파일 확인
	./resource/Logo_[OEM별 resource].bmp

	## Build 및 Package 생성
	$ ./makePackage.sh
