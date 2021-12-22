#include <stdio.h>
#include <unistd.h>

#include "api_web_log.h"

int main(void) {
	/*
		WebLogHandler는 지정된 module명의 로그 파일에 web과 동일한 패턴으로 남기는 것을 목적으로 함.
		특히 언어팩 적용이 어려운 process 에서 log_interface를 통해 별도의 logging function 없이
		공용 로그를 남길 수 있도록 함.
	*/
	
	// web log instance 생성
	// instance 생성 시 module명을 입력하는데 최대 32자까지 사용할 수 있음.
	// args(<string _module_name>)
	WebLogHandler log_audio("audio_setup");
	
	// debug print 출력
	log_audio.set_debug_print();
	
	// 로그 내용을 삭제한다.
	log_audio.clear();
	
	// 로그 파일을 삭제한다.
	log_audio.remove();
	
	// 로그를 기록한다.
	// 로그 레벨은 5단계로 나뉜다.
	// [fatal, error, debug, warn, info] 
	// 이중 [info] 레벨을 제외한 모든 로그 레벨은 공용 로그에서 출력되는 메시지 외 레벨 정보를 포함한다.
	// [info] 레벨 정보를 포함하기 위해선 set_info_level() method를 설정한다.
	// module 내의 언어팩을 적용할 땐 { }(대괄호) 사이에 언어팩의 상수명을 사용한다.
	// return - true: success, false: failed
	log_audio.info("라이브러리! {STR_MENU_NAME}");
	
	log_audio.fatal("로그 fatal {STR_MENU_NAME}");
	log_audio.error("로그 error {STR_MENU_NAME}");
	log_audio.debug("로그 debug {STR_MENU_NAME}");
	log_audio.warn("로그 warn {STR_MENU_NAME}");
	
	log_audio.info("로그 info {STR_MENU_NAME}");
	
	// [info] 레벨 정보 출력
	log_audio.set_info_level();
	log_audio.info("로그 [debug] info {STR_MENU_NAME}");
	
	/* 결과
	 [2019/08/09 14:16:29] 라이브러리! 오디오 설정
	 [2019/08/09 14:16:29] [FATAL] 로그 fatal 오디오 설정
	 [2019/08/09 14:16:29] [ERROR] 로그 error 오디오 설정
	 [2019/08/09 14:16:29] [DEBUG] 로그 debug 오디오 설정
	 [2019/08/09 14:16:29] [WARN] 로그 warn 오디오 설정
	 [2019/08/09 14:16:29] 로그 info 오디오 설정
	 [2019/08/09 14:16:29] [INFO] 로그 [debug] info 오디오 설정
	 */
	
	return 0;
}
