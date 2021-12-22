<?php
	$info_file_path = $_SERVER['DOCUMENT_ROOT'] . "/../conf/common_link.info";
	$arr_modules = array();

	if( file_exists($info_file_path) ) {
		$file_info 	= file_get_contents($info_file_path);
		$info_array = explode("\n", $file_info);
		$contents 	= array_values(array_filter(array_map('trim', $info_array)));

		foreach( $contents as $num => $moduleInfo ) {
			$is_exist_rev_loop = false;
			$homepage = explode(" ", $moduleInfo);

			if( count($homepage) == 3 ) {
				$is_exist_rev_loop = true;
			}

			$arr_modules[$num]["path"]	   = $homepage[0];
			$arr_modules[$num]["module"]   = $homepage[1];
			$arr_modules[$num]["revision"] = "-";

			if( $is_exist_rev_loop ) {
				$arr_modules[$num]["revision"] = $homepage[2];
			}

			$division = explode("/", $homepage[0]);

			if( strpos($division[7], "@") !== false ) {
				$version_info = explode("@", $division[7])[0];

			} else {
				$version_info = $division[7];
			}

			$version = array_pop(explode('_', $version_info));
			$arr_modules[$num]["division"] = "TAG";
			$arr_modules[$num]["version"]  = $version;

			if ( stripos($division[6], "TAG") === false ) {
				$arr_modules[$num]["division"] = "TRUNK";
				$arr_modules[$num]["version"]  = "-";
			}
		}

		array_multisort($module, $arr_modules);
	}
?>

<div id="info-div_page_title_name"> 공용 모듈 정보 (Common module information) </div>

<hr class="title-hr" />

<div id="info-div_contents_table">
	<div class="info-div_table_content_inner">
		<div class="info-div_table">
			<div class="info-div_table_title info-div_table_row">
				<div class="info-div_common_module_title"> 모듈 </div>
				<div class="info-div_file_division"> 구분 </div>
				<div class="info-div_version"> 버전 </div>
				<div class="info-div_revision"> 리비전 </div>
				<div class="info-div_homepage_title"> 경로 </div>
			</div>
		</div>
		<?php
		if( count($arr_modules) == 0 ) {
			echo "<div class='info-div_common_module_list_info info-div_noDataInfo_sort'>공용모듈 정보가 존재하지 않습니다.</div>";
		}

		foreach( $arr_modules as $num => $moduleInfo ) {
			echo "<div class='info-div_common_module_list_info'>
					<div class='info-div_common_module info-div_content_sort'> {$arr_modules[$num]['module']} </div>
					<div class='info-div_file_division'> {$arr_modules[$num]['division']} </div>
					<div class='info-div_version'> {$arr_modules[$num]['version']} </div>
					<div class='info-div_revision'> {$arr_modules[$num]['revision']} </div>
					<div class='info-div_homepage info-div_content_sort'> {$arr_modules[$num]['path']} </div>
				  </div>";
		}
		?>
	</div>
</div>
