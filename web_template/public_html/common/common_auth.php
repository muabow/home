<script type="text/javascript">
	$(document).ready(function() {
		var authFunc    = new AuthFunc();
		var displayFunc = new CommonDisplayFunc();

		var fileTarget = $('.filebox .upload-hidden');
		fileTarget.on('change', function(){ // 값이 변경되면
			if( window.FileReader ) { // modern browser
				if( $(this).val() == "" ) var filename = "<?=Common\Lang\STR_AUTH_FILE_FIND ?>";
				else var filename = $(this)[0].files[0].name;

			} else { // old IE
				var filename = $(this).val().split('/').pop().split('\\').pop(); // 파일명만 추출
			} // 추출한 파일명 삽입

			$(this).siblings('.upload-name').val(filename);
		});

		$("#div_login_form_submit").click(function() {
			var fileName = $("#file_uploadFile").val();
			var fileData = $("#file_uploadFile")[0].files[0];

			// 파일 첨부 검출
			if( !fileName ) {
				alert("<?=Common\Lang\STR_AUTH_FILE_SELECT ?>");

				return ;
			}
			// 파일 확장자 검출
			var arrFileName = fileName.split('.');
			var fileType = arrFileName[arrFileName.length - 1];

			if( !(fileType.toLowerCase() == "imkp" ) ) {
				alert("<?=Common\Lang\STR_AUTH_FILE_ALERT ?>");

				return ;
			}

			// 파일 사이즈 검출
			var maxFileSize = "<?php echo ini_get('upload_max_filesize'); ?>";
			var limitSize   = parseInt(maxFileSize) * 1024 * 1024;
			var fileSize    = fileData.size;

			if( fileSize >= limitSize ) {
				alert("<?=Common\Lang\STR_AUTH_UPLOAD_LIMIT ?> (" + maxFileSize + ")");

				return ;
			}

			if( !confirm("<?=Common\Lang\STR_AUTH_CONFIRM ?>") ) {
				return ;
			}

			displayFunc.showLoader();

			// 파일 업로드
			var formData   = new FormData();
			var uploadStat = false;
			var uploadMsg  = "";
			var result;

			formData.append('file', fileData);

			var request = new XMLHttpRequest();
			request.onreadystatechange = function(){
				if( request.readyState == 4 ) {
					try {
						result = JSON.parse(request.response);
						var time      = -1;
						var alertType;
						switch( result.code ) {
							case 0  :
								alertType = "<?=Common\Lang\STR_AUTH_FINISH ?>";
								time = 10;
							break;

							case -1 :
								alertType = "<?=Common\Lang\STR_AUTH_NOT_FOUND_FILE ?>";
							break;

							case -10 :
								alertType = "<?=Common\Lang\STR_AUTH_FAIL ?>";
							break;

							default :
								alertType = "<?=Common\Lang\STR_AUTH_UPLOAD_FAIL ?>";
							break;
						}

						displayFunc.hideLoader(time);

						if( time > 0 ) {
							setTimeout(function() {
								alert(alertType);
								location.reload();
							}, (time * 1000));

						} else {
							setTimeout(function() {
								alert(alertType);
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

			request.open('POST', "<?=Common\Def\PATH_AUTH_UPLOAD_PROCESS ?>");
			request.send(formData);
		});
	});

	class AuthFunc {
		constructor() {		}

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

	} // end of AuthFunc()

</script>
