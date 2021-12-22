<?php
		if( isset($_POST["type"]) ) {
		$moduleName = $_POST["moduleName"];
		$text 		= $_POST["text"];

		// language pack
		$load_envData	= file_get_contents("/opt/interm/conf/env.json");
		$envData   		= json_decode($load_envData);
		$env_langSet  	= $envData->info->language_set;
		$env_pathModule = "/opt/interm/public_html/modules/" . $moduleName;

		if( !file_exists($env_pathModule) ) {
			$moduleName = "common";
			include_once "/opt/interm/public_html/" . $envData->language_pack->$env_langSet->path;

		}else {
			include_once "/opt/interm/public_html/modules/{$moduleName}/html/" . $envData->language_pack->$env_langSet->path;
		}

		// constant string match
		preg_match_all("#\{(.*?)\}#", $text, $matches);
		foreach( $matches[1] as $match ) {
			if( @constant(ucfirst($moduleName) . "\Lang\\". $match) ) {
					$text = preg_replace("#\{{$match}\}#", constant(ucfirst($moduleName) . "\Lang\\". $match), $text);
			}
		}

		echo $text;


		exit ;
	}
?>
