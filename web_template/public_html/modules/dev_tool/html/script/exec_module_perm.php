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

    if( isset($_POST["type"]) && $_POST["type"] == "exec" ) {
        $num_invalid_perm = 0;
        foreach( read_file_info("/opt/interm") as $key => $fileName ) {
            $perms = fileperms($fileName);
            $perms = $perms - 32768;
    
            if( decoct($perms) != 755 ) $num_invalid_perm++;
        }
    
        $str_file_list = "";
        if( $num_invalid_perm != 0 ) {
            foreach( read_file_info("/opt/interm") as $key => $fileName ) {
                $perms = fileperms($fileName);
                $perms = $perms - 32768;
    
                if( decoct($perms) != 755 ) {
                    $str_file_list .= '<a style=font-size:10pt;>
                            &nbsp;&nbsp;&nbsp;&nbsp;' . $fileName . " : [" . decoct($perms) . "]
                          </a> <br />" ;
                }
            }
    
            shell_exec("sudo chown -R interm:interm /opt/interm");
            shell_exec("sudo chmod -R 755 /opt/interm");
        }

        $arr_res_info["count"] = $num_invalid_perm;
        $arr_res_info["info"]  = $str_file_list;

        echo json_encode($arr_res_info);
        return ;
    }
?>

<script type="text/javascript">
    function func_exec(_req) {
        if( _req.readyState == <?=Common\Def\READY_STAT_SUCCESS ?> ) {
            if( _req.status == <?=Common\Def\STATUS_SUCCESS ?> ) {
                var common_display_handler = new CommonDisplayFunc();

                var result = _req.responseText;
                var json_info = JSON.parse(result);
                
                $("#perm-span_notice_term_count").html(json_info.count);
                $("#perm-span_notice_term_list").html(json_info.info);

                
                $("#perm-div_notice_wait").hide();
                $("#perm-div_notice_term").show();
                common_display_handler.clearDotLoader();
            }
        }

        return ;
    }

	$(document).ready(function() {
		$("#perm-button_perm_apply").click(function() {
            $("#perm-div_notice_wait").show();
            $("#perm-div_notice_term").hide();
            
            common_display_handler.showLoader();

            var commonFunc = new CommonFunc();
            var args = commonFunc.makeArgs("type",      "exec");
            commonFunc.postArgsAsync("http://<?php echo $_SERVER["HTTP_HOST"]; ?>/modules/dev_tool/html/script/exec_module_perm.php", args, func_exec, "POST");
            
			return ;
        });
        
        var common_display_handler = new CommonDisplayFunc();
	});
</script>

<div id="perm-div_page_title_name"> 권한 및 소유자 변경 (Change permission & owner) </div>

<hr class="title-hr" />

<div id="perm-div_contents_table">
	<div class="perm-div_table_content_inner">
        <span style="font-weight: bold; font-size: 13px;"> * 잘못된 파일 권한과 소유자로 인해 웹 페이지가 정상적으로 동작하지 않을때 사용됩니다. </span>
        <br />
		<input type="button" id="perm-button_perm_apply" style="width: 100px; height: 30px; font-weight: bold; margin: 10px 10px 10px 10px;" value="적용" />

        <br /> <br />
        
        <div id="perm-div_notice">
            <div id="perm-div_notice_wait" style="display: none;">
                <div style="font-weight: bold;"> - 처리중입니다. 잠시만 기다려주세요. </div>
            </div>
            
            <div id="perm-div_notice_term" style="display: none;">
                <div style="font-weight: bold;"> - 권한 및 소유자 변경 변경이 필요한 대상 파일 : <span id="perm-span_notice_term_count"> - </span> 개 </div>
                <span id="perm-span_notice_term_list"> - </span>
                <div style="font-weight: bold;"> - 권한 및 소유자 변경 완료 </div>
            </div>
        </div>
	</div>
</div>
