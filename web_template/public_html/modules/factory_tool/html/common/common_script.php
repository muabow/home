<?php
	function read_file_info($_dir) {
		if( $fp = opendir($_dir) ) {
			$files = Array();
			$in_files = Array();

			while( $fileName = readdir($fp) ) {
				if( $fileName[0] != '.' ) {
					if( is_dir($_dir . "/" . $fileName) ) {
						$in_files = read_file_info($_dir . "/" . $fileName);

						if( is_array($in_files) ) {
							$files = array_merge ($files , $in_files);
						}

					} else {
						array_push($files, $_dir . "/" . $fileName);
					}
				}
			}
			closedir($fp);

			return $files;
		}
	}
?>
