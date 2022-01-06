<?php
	function get_file_list($_target_dir) {
		if( $fp = opendir($_target_dir) ) {
			$files = Array();
			$in_files = Array();

			while( $file = readdir($fp) ) {
				if( $file[0] != '.' ) {
					if( is_dir("{$_target_dir}/{$file}") ) {
						$in_files = get_file_list("{$_target_dir}/{$file}");

						if( is_array($in_files) ) {
							$files = array_merge($files, $in_files);
						}

					} else {
						array_push($files, "{$_target_dir}/{$file}");
					}
				}
			}
			closedir($fp);

			return $files;
		}
	}

	$arr_file_list = get_file_list("/var/log/apache2");
	print_r($arr_file_list);
?>
