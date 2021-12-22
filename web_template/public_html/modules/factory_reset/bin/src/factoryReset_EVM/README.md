# factoryReset : Factory Reset
----------------------------------------
※ 본 문서는 Markdown Format 으로 작성되어 Markdown 을 지원하는 Viewer / Editor 를 사용하시면 더 깔끔하게 보실 수 있습니다.


## 1. factoryReset 란?
보드에 내장된 Live LED 와 후면부에 내장된 RESET 버튼을 통하여 시스템 동작 여부 확인, 보드 테스트, 초기화 를 수행 할 수 있는 System Application

부팅시 자동실행을 위한 script 내장

### 1-2 보드테스트
- 후면 RESET 버튼을 누른 후 5초 이상 유지시 Live LED 가 조금 더 빠르게 깜빡임
- RESET 버튼을 떼면, 보드 테스트 진행
- /opt/interm/bin/factory_test.sh

### 1-2 초기화
- 후면 RESET 버튼을 누른 후 10초 이상 유지시 Live LED 가 아주 빠르게 깜빡임
- RESET 버튼을 떼면, 초기화 수행 (Package 재설치)
- /opt/interm/bin/factory_default.sh

### 1-3 시스템 동작 확인
- 시스템이 정상적으로 부팅 완료 되면 Live LED 가 1초 간격으로 상시 점멸

## 2. 설치
### 2-1 Package 다운 및 설치 (개발 보드에서 실행)

	## svn 에서 deb Package 받기
	$ svn checkout http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/debs/ .
	
	## 설치 (업데이트 동일)
	## 항상 최신 버전을 확인하여 설치 할 것!
	
	$ sudo dpkg -i factoryReset_0.0.0.1_armhf.deb


### 2-2 deb Package 정보 확인
  버전이 동일하여도 빌드 날짜가 다를 수 있음

	## Package 정보 확인
	$ dpkg -I factoryReset_0.0.0.1_armhf.deb
	 new debian package, version 2.0.
	 size 64590 bytes: control archive=538 bytes.
	     178 bytes,     6 lines      control
	     140 bytes,     8 lines   *  postinst             #!/bin/sh
	     219 bytes,    10 lines   *  preinst              #!/bin/sh
	     219 bytes,    10 lines   *  prerm                #!/bin/sh
	 Package: frontDisplay
	 Version: 0.0.0.1+20170810
	 Architecture: armhf
	 Depends: libavmsapi
	 Maintainer: DooGyeong Han <dghan@inter-m.com>
	 Description: Factory Reset


## 3. Package 생성 (개발 보드에서 실행 권장)
Build 및 Package 를 생성 하기 위해서는 libavmsapi 가 반드시 사전 설치되어 함

개발 보드 외에 x86/x64 의 Linux 머신에서도 cross-compile 이 가능 하나 
libavmsapi 설치를 위해 Library/Include Path 설정에 대한 이해가 필요

	## Source Code 받기
	$ svn checkout http://ctfprd.inter-m.com/svn/repos/avms_dev/STD_LINUX/api/factoryReset .
	
	## Version 명 수정
	$ vi version
	0.0.0.2
	
	## Build
	$ make
	## Package 생성 (Build 기능 포함)
	$ make dist
