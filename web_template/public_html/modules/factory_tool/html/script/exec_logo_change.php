<?php

	if( isset($_POST["type"]) && $_POST["type"] == "change_image" ) {
		if( $_POST["image"] == "org" || $_POST["image"] == "org_mobile" ) {
			// env.json 파일 변경
			$env_data = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/../conf/env.json');
			$env_data = json_decode($env_data, true);
			
			// config.json 파일 변경
			$config_data = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/modules/factory_tool/conf/factory_config.json');
			$config_data = json_decode($config_data, true);
			
			if( $_POST["is_mobile"] == "true" ) {
				// 모바일 로고 이미지 원본으로 변경
				$env_data["company"]["ci_logo_mobile"] = "/img/ci_logo_mobile.png";
				
				// 모바일 로고 변경 false
				$config_data["custom_logo_mobile"] = "false";
				
			} else if( $_POST["is_mobile"] == "false" ) {
				// 로고 이미지 원본으로 변경
				$env_data["company"]["ci_logo"] = "/img/ci_logo.png";
				
				// PC 로고 변경 false
				$config_data["custom_logo"] = "false";
				
			}
			
			file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/../conf/env.json', json_encode($env_data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
			
			file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/modules/factory_tool/conf/factory_config.json', json_encode($config_data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
		
		} else if( $_POST["image"] == "tmp" || $_POST["image"] == "tmp_mobile" ) {
			// env.json 파일 변경
			$env_data = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/../conf/env.json');
			$env_data = json_decode($env_data, true);
			
			// config.json 파일 변경
			$config_data = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/modules/factory_tool/conf/factory_config.json');
			$config_data = json_decode($config_data, true);
			
			if( $_POST["is_mobile"] == "true" ) {
				// 모바일 로고 이미지 원본으로 변경
				$env_data["company"]["ci_logo_mobile"] = "/modules/factory_tool/html/img/custom/ci_custom_logo_mobile.png";
			
				// 모바일 로고 변경 true
				$config_data["custom_logo_mobile"] = "true";
				
			} else if( $_POST["is_mobile"] == "false" ) {
				// 로고 이미지 원본으로 변경
				$env_data["company"]["ci_logo"] = "/modules/factory_tool/html/img/custom/ci_custom_logo.png";
				
				// PC 로고 변경 true
				$config_data["custom_logo"] = "true";
				
			}
			
			file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/../conf/env.json', json_encode($env_data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
			
			file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/modules/factory_tool/conf/factory_config.json', json_encode($config_data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
			
		}

		exit ;
		
	} else if( isset($_POST["type"]) && $_POST["type"] == "upload_image" ) {
		$file_path = $_POST["filePath"];
		
		shell_exec("chmod 755 " . $file_path);
		
		if( $_POST["is_mobile"] == "true" ) {
			shell_exec("mv " . $file_path . " " . $_SERVER['DOCUMENT_ROOT'] . '/modules/factory_tool/html/img/custom/ci_custom_logo_mobile.png');
		} else if( $_POST["is_mobile"] == "false" ) {
			shell_exec("mv " . $file_path . " " . $_SERVER['DOCUMENT_ROOT'] . '/modules/factory_tool/html/img/custom/ci_custom_logo.png');
		}
		
		exit;
		
	}
	
	$config_data = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/modules/factory_tool/conf/factory_config.json');
	$config_data = json_decode($config_data, true);
	
	echo '<script type="text/javascript">' . "\n";
	if( $config_data["custom_logo"] == "false" ) {
		echo 'var logo_id = "org";' . "\n";
	} else if( $config_data["custom_logo"] == "true" ) {
		echo 'var logo_id = "tmp";' . "\n";
	}

	if( $config_data["custom_logo_mobile"] == "false" ) {
		echo 'var logo_mobile_id = "org_mobile";' . "\n";
	} else if( $config_data["custom_logo_mobile"] == "true" ) {
		echo 'var logo_mobile_id = "tmp_mobile";' . "\n";
	}

	echo '</script>' . "\n";
?>

<script type="text/javascript">

$(document).ready(function () {
	var displayFunc = new CommonDisplayFunc();
	var logo_change_func = new LogoChangeFunc();
	
	$("#" + logo_id).prop("checked", true);
	$("#" + logo_mobile_id).prop("checked", true);
	
	$("input[name=image]").click(function() {
		var id = $(this).attr("id");
		
		var submit_args = "";
		submit_args += logo_change_func.makeArgs("type", "change_image");
		submit_args += logo_change_func.makeArgs("image", id);
		submit_args += logo_change_func.makeArgs("is_mobile", "false");
		
		logo_change_func.postArgs(logo_change_func.path, submit_args);
		alert("로고를 변경합니다. 화면이 갱신됩니다.");
		location.reload();
	});
	
	$("input[name=image_mobile]").click(function() {
		var id = $(this).attr("id");
		
		var submit_args = "";
		submit_args += logo_change_func.makeArgs("type", "change_image");
		submit_args += logo_change_func.makeArgs("image", id);
		submit_args += logo_change_func.makeArgs("is_mobile", "true");
		
		logo_change_func.postArgs(logo_change_func.path, submit_args);
		alert("로고를 변경합니다. 화면이 갱신됩니다.");
		location.reload();
	});
	
	var fileTarget = $('.upload-hidden');
	fileTarget.on('change', function(){ // 값이 변경되면
		if( window.FileReader ) { // modern browser
			if( $(this).val() == "" ) var filename = "파일 선택";
			else var filename = $(this)[0].files[0].name;

		} else { // old IE
			var filename = $(this).val().split('/').pop().split('\\').pop(); // 파일명만 추출
		} // 추출한 파일명 삽입
		$(this).siblings('.upload-name').val(filename);
		
		var str_source_name = filename;

		// 파일명 길이 체크
		for( bc = idx = 0 ; c = str_source_name.charCodeAt(idx++) ; bc += (c >> 11 ? 3 : (c >> 7 ? 2 : 1)) );
		if( bc >= 255 ) {
			alert("파일명은 확장자 포함 255글자를 넘을 수 없습니다.");
			
			var arr_id = $(this).attr("id").split("_");
			var label_id = "label_" + arr_id[1] + "_" + arr_id[2];
			
			$(this).val("");
			$("#" + label_id).val("파일 선택");	
			
			return ;
		}

		// 파일명 띄어쓰기 2개 연속 체크
		var invalid_case_double_space = "  ";
		if( str_source_name.indexOf(invalid_case_double_space) > -1 ) {
			alert("파일명이 연속된 공백을 포함하고 있습니다.");
						
			var arr_id = $(this).attr("id").split("_");
			var label_id = "label_" + arr_id[1] + "_" + arr_id[2];
			
			$(this).val("");
			$("#" + label_id).val("파일 선택");	
			
			return ;
		}

		// 파일명 특수문자 여부 체크
		var invalid_case_special = /[`'*|\\\"\/?#%:<>&$+ ]/g;
		if( invalid_case_special.test(str_source_name) == true ) {
			alert("파일명에 적절하지 않은 특수문자(` * | \\\ / ? \\\" : < > + # $ % 공백)가 입력되어있습니다.");
						
			var arr_id = $(this).attr("id").split("_");
			var label_id = "label_" + arr_id[1] + "_" + arr_id[2];
			
			$(this).val("");
			$("#" + label_id).val("파일 선택");	
			
			return ;
		}

		if( $(this).val() != "" ) {
			var id = $(this).attr("id");
			if( confirm("이미지를 업로드 하시겠습니까?") ) {
				
				var fileData = $(this)[0].files[0];
				
				// 파일 사이즈 검출
				var maxFileSize = "<?php echo ini_get('upload_max_filesize'); ?>";
				var limitSize   = parseInt(maxFileSize) * 1024 * 1024;
				var fileSize    = fileData.size;
	
				if( fileSize >= limitSize ) {
					alert("업로드 용량을 초과하였습니다. (" + maxFileSize + ")");
	
					return ;
				}
				displayFunc.showLoader();
				
				// 파일 업로드
				var formData   = new FormData();
				var result;
	
	
				formData.append('file', fileData);
	
				var request = new XMLHttpRequest();
				request.onreadystatechange = function(){
					if( request.readyState == 4 ) {
						$(".container").hide();
						try {
							result = JSON.parse(request.response);
	
							if( result.code == 0 ) {
								displayFunc.hideDotLoader();
	
								var submitParams = logo_change_func.makeArgs("filePath", result.msg);
								submitParams += logo_change_func.makeArgs("type", "upload_image");
								
								if( id == "file_upload_mobile" ) {
									submitParams += logo_change_func.makeArgs("is_mobile", "true");
								} else if( id == "file_upload_pc" ) {
									submitParams += logo_change_func.makeArgs("is_mobile", "false");
								}
								
								var filePath = logo_change_func.postArgs(logo_change_func.path, submitParams);
								displayFunc.clearDotLoader();
								alert("로고 이미지 업로드에 성공하였습니다. 화면이 갱신됩니다.");
								location.reload();
							} else {
								displayFunc.hideLoader(-1);
	
								setTimeout(function() {
									switch( result.code ) {
										case -1 :
											alert("파일을 찾을 수 없습니다.");
										break;
	
										default :
											alert("업로드에 실패 했습니다.");
										break;
									}
								}, 800);
							}
	
						} catch (e){
							var resp = {
								status: 'error',
								data: 'Unknown error occurred: [' + request.responseText + ']'
							};
						}
					}
				};
	
				request.upload.addEventListener('progress', function(e) {
					if( id == "file_upload_mobile" ) {
						var progressBar = document.getElementById('div_fileUpload_progress_mobile');
		
						var percentage = parseInt(e.loaded/e.total * 100) + '%';
						progressBar.style.width = percentage;
		
						$("#upload_mobile_container").show();
						$("#div_fileUpload_progress_mobile").html("&nbsp;&nbsp;" + percentage);
						
					} else if( id == "file_upload_pc" ) {
						var progressBar = document.getElementById('div_fileUpload_progress');
		
						var percentage = parseInt(e.loaded/e.total * 100) + '%';
						progressBar.style.width = percentage;
		
						$("#upload_container").show();
						$("#div_fileUpload_progress").html("&nbsp;&nbsp;" + percentage);
						
					}
	 				
	
				}, false);
	
				request.open('POST', "modules/system_management/html/common/common_upload.php");
				request.send(formData);
	
				return ;
			
			} else {
				var arr_id = $(this).attr("id").split("_");
				var label_id = "label_" + arr_id[1] + "_" + arr_id[2];
				
				$(this).val("");
				$("#" + label_id).val("파일 선택");	
			}
				
		}
	});
});

class LogoChangeFunc {
	constructor() {
		this.path = "http://<?php echo $_SERVER["HTTP_HOST"]; ?>/modules/factory_tool/html/script/exec_logo_change.php";
	}

	makeArgs(_key, _value) {
		var args = "&" + _key + "=" + _value;

		return args;
	}

	postArgs(_target, _args) {
		var result;

		$.ajax({
			type	: "POST",
			url		: _target,
			data	: _args,
			async	: false,
			success	: function(data) {
				if( data != null ) {
					result = data;
				}
			}
		});

		return result;
	}
}
	

</script>

<div id="system-div_page_title_name" name="logo_change"> 로고 이미지 설정 (Logo image setting) </div>

<hr class="title-hr" style="width: 890px"/>

<div id="system-div_contents_table">
	<div class="system-div_contents_cell">
		<div class="system-div_contents_cell_line" style="padding-top: 10px; font-weight: bolder; border-top: 0px;">
			1. PC 버전 로고 업로드 
		</div>
		<div class="system-div_contents_cell_line" style="padding-bottom: 5px; border-top: 0px; font-size: 12px;">
			&nbsp; - 컨트롤러 제품군 권장 사이즈 (150 x 45 pixels), 그 외 제품군 권장 사이즈 (225 x 75 pixels)
		</div>

		<div class="system-div_contents_cell_line">
			<div class="system-div_contents_cell_title">
				업로드
			</div>

			<div class="system-div_contents_cell_contents">
				<input class="upload-name" id="label_upload_pc" value="파일 선택" disabled="disabled">
				<label for="file_upload_pc">업로드</label>
				<input type="file" accept="image/gif,image/jpg,image/jpeg,image/png" id="file_upload_pc" class="upload-hidden" />

				<div class="container" id="upload_container">
					<div class="progress_outer">
						<div id="div_fileUpload_progress" class="progress"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="system-div_contents_cell_line">
			<div class="system-div_contents_cell_title">
				이미지
			</div>
			
			<div class="system-div_contents_cell_contents">
				<div class="system-div_table">
					<div class="system-div_table_title system-div_table_row">
						<div class="logo-div_org_logo" style="border-right: 1px dotted #2f312e;">원본</div>
						<div class="logo-div_tmp_logo">업로드</div>
					</div>
					<div class="div_table_content" id="sortable">
						<div class="logo-div_table_row">
							<div class="logo-div_org_logo" style="background: #2f312e; border-right: 1px dotted #b8b8b8;">
								<input type="radio" id="org" name="image" style="margin-right:10px;"/>
								<img id="img_org_ci" src="/img/ci_logo.png" style="border: 1px solid #b8b8b8; padding: 1px;">
							</div>
							<div class="logo-div_tmp_logo" style="background: #2f312e;">
								<input type="radio" id="tmp" name="image" style="margin-right:10px;" />
								<img id="img_tmp_ci" src="/modules/factory_tool/html/img/custom/ci_custom_logo.png"  style="border: 1px solid #b8b8b8; padding: 1px;">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
			// env.json 파일 변경
			$env_data = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/../conf/env.json');
			$env_data = json_decode($env_data, true);
			if( $env_data["company"]["ci_logo_mobile"] == null ) {
				echo "<!--";
			}
		?>
		<div class="system-div_contents_cell_line" style="padding-top: 20px; font-weight: bolder;">
			2. 모바일 버전 로고 업로드
		</div>
		<div class="system-div_contents_cell_line" style="padding-bottom: 5px; border-top: 0px; font-size: 12px;">
			&nbsp; - 권장 사이즈 (50 x 45 pixels)
		</div>

		<div class="system-div_contents_cell_line">
			<div class="system-div_contents_cell_title">
				업로드
			</div>

			<div class="system-div_contents_cell_contents">
				<input class="upload-name" id="label_upload_mobile" value="파일 선택" disabled="disabled">
				<label for="file_upload_mobile">업로드</label>
				<input type="file" accept="image/gif,image/jpg,image/jpeg,image/png" id="file_upload_mobile" class="upload-hidden" />

				<div class="container" id="upload_mobile_container">
					<div class="progress_outer">
						<div id="div_fileUpload_progress_mobile" class="progress"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="system-div_contents_cell_line">
			<div class="system-div_contents_cell_title">
				이미지
			</div>
			
			<div class="system-div_contents_cell_contents">
				<div class="system-div_table">
					<div class="system-div_table_title system-div_table_row">
						<div class="logo-div_org_logo" style="border-right: 1px dotted #2f312e;">원본</div>
						<div class="logo-div_tmp_logo">업로드</div>
					</div>
					<div class="div_table_content" id="sortable">
						<div class="logo-div_table_row">
							<div class="logo-div_org_logo" style="background: #2f312e; border-right: 1px dotted #b8b8b8;">
								<input type="radio" id="org_mobile" name="image_mobile" style="margin-right:10px;"/>
								<img id="img_org_ci" src="/img/ci_logo_mobile.png"  style="border: 1px solid #b8b8b8; padding: 1px;">
							</div>
							<div class="logo-div_tmp_logo" style="background: #2f312e;">
								<input type="radio" id="tmp_mobile" name="image_mobile" style="margin-right:10px;" />
								<img id="img_tmp_ci" src="/modules/factory_tool/html/img/custom/ci_custom_logo_mobile.png"  style="border: 1px solid #b8b8b8; padding: 1px;">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="system-div_contents_cell_line">
			<span style="padding-top: 10px; font-weight: bolder;">* 업로드한 이미지로 갱신되지 않으면 [CTRL + F5] 키를 눌러주세요. </span>
		</div>

		<?php
			// env.json 파일 변경
			$env_data = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/../conf/env.json');
			$env_data = json_decode($env_data, true);
			if( $env_data["company"]["ci_logo_mobile"] == null ) {
				echo "-->";
			}
		?>
	</div>
</div>
