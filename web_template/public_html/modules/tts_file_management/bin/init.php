<?php
	const PATH_ENV_DEFAULT_INFO = "/opt/interm/conf/default.json";
	const PATH_PRESET_DFLT_INFO = "/opt/interm/public_html/modules/tts_file_management/conf/default.json";
	
	$json_dflt_info = json_decode(file_get_contents(PATH_ENV_DEFAULT_INFO));
	
	// default.json 파일은 업그레이드/공장초기화 시에도 유지되는 파일로 해당 언어팩 관리 목록이 없다면 수동으로 추가
	if( !isset($json_dflt_info->tts_support_language) ) {
		$json_preset_info = json_decode(file_get_contents(PATH_PRESET_DFLT_INFO));
		
		$json_dflt_info->tts_support_language = $json_preset_info->tts_support_language;

		file_put_contents(PATH_ENV_DEFAULT_INFO, json_encode($json_dflt_info, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
	}
?>