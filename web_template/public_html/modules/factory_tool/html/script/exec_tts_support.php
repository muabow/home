<?php
	function load_tts_support_list() {
		$json_info = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/../conf/default.json"));

		$str_option_list = "";

		foreach( $json_info->tts_support_language as $item ) {
			foreach( $item as $lang_type => $lang_info ) {
				$is_enable_toggle = "";
				$is_enable_switch = "";

				if( $lang_info->is_enable == true ) {
					$is_enable_toggle = "tts-div_switch_background";
					$is_enable_switch = "tts-div_switch_back_on_off";
				}

				$str_option_list .= '<div class="tts-div_table_row">
				<div class="tts-div_enable_check">
					<div class="tts-div_toggle ' . $is_enable_toggle . '">
						<span class="tts-span_check_on"> ON </span>
						<span class="tts-span_check_off"> OFF </span>
						<div class="tts-div_check_toggle ' . $is_enable_switch . '"></div>
					</div>
				</div>
				<div class="tts-div_language_type">
					' . $lang_type . '
				</div>
				<div class="tts-div_language_name">
					' . $lang_info->name . '
				</div>
			</div>';
			}
		}

		return $str_option_list;
	}

	if( isset($_POST["type"]) && $_POST["type"] == "change_language_enable" ) {
		$json_info = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/../conf/default.json"));
		$data_info = json_decode($_POST["data"])[0];
		
		$is_matched = false;
		foreach( $json_info->tts_support_language as $idx => $item ) {
			foreach( $item as $lang_type => $lang_info ) {
				if( $lang_type == $data_info->type ) {
					echo $json_info->tts_support_language[$idx]->$lang_type->is_enable = $data_info->is_enable;
					$is_matched = true;
					break;
				}
			}
			if( $is_matched ) break;
		}

		file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/../conf/default.json", json_encode($json_info, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

		return ;


	} else if( isset($_POST["type"]) && $_POST["type"] == "change_language_list" ) {
		$json_info = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/../conf/default.json"));
		$data_info = json_decode($_POST["data"]);

		$arr_support_list = array();

		foreach( $data_info as $data_idx => $data_type ) {
			$is_matched = false;
			foreach( $json_info->tts_support_language as $tts_idx => $tts_item ) {
				foreach( $tts_item as $lang_type => $lang_info ) {
					if( $lang_type == $data_type ) {
						$arr_support_list[] = $tts_item;
						$is_matched = true;
						break;
					}
				}
				if( $is_matched ) break;
			}
		}

		$json_info->tts_support_language = $arr_support_list;

		file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/../conf/default.json", json_encode($json_info, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

		return ;
	}
?>

<script type="text/javascript">
	class TTS_Handler {
		constructor() {
			this.path = "http://<?php echo $_SERVER["HTTP_HOST"]; ?>/modules/factory_tool/html/script/exec_tts_support.php";
		}

		makeArgs(_key, _value) {
			var args = "&" + _key + "=" + _value;

			return args;
		}

		postArgs(_args) {
			var result;

			$.ajax({
				type	: "POST",
				url		: this.path,
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
	};

	$(document).ready(function() {
		var tts_handle = new TTS_Handler();

		$(".tts-div_toggle").click(function() {
			var is_enable = false;

			var table_row 	  = $(this).closest(".tts-div_table_row");
			var language_type = $.trim(table_row.find(".tts-div_language_type").html());

			$(this).find(".tts-div_check_toggle").toggleClass("tts-div_switch_back_on_off", 0, "easeOutSine");
			$(this).toggleClass("tts-div_switch_background", 0, "easeOutSine");

			if( $(this).hasClass("tts-div_switch_background") == true ) {
				is_enable = true;
			}

			var lang_info = [];
			lang_info.push({
							"type"      : language_type,
							"is_enable" : is_enable,
			});

			lang_info = JSON.stringify(lang_info);
			
			var args = "";
			args += tts_handle.makeArgs("type",   "change_language_enable");
			args += tts_handle.makeArgs("data",   lang_info);
			
			tts_handle.postArgs(args);

			return ;
		});

		$("#tts-sortable").sortable({
			items: ".tts-div_table_row:not(.tts-div_table_title)",
			update : function () {
				var lang_info = [];

				$(".tts-div_table").find(".tts-div_table_row").each(function (i, e) {
					var language_type = $.trim($(e).find(".tts-div_language_type").html());
					lang_info.push(language_type);
				});
				lang_info = JSON.stringify(lang_info);
				
				var args = "";
				args += tts_handle.makeArgs("type",   "change_language_list");
				args += tts_handle.makeArgs("data",   lang_info);

				tts_handle.postArgs(args);

				return ;
			}
		});
	});
</script>


<div id="tts-div_page_title_name" name="tts_support"> TTS 지원 언어 설정 (TTS support language setting) </div>

<hr class="title-hr" style="width: 890px"/>

<div id="tts-div_contents_table">
	<div class="tts-div_contents_cell">
		<div class="tts-div_contents_cell_line">
			<div class="tts-div_contents_cell_title">
				TTS 지원 언어 목록
			</div>

			<div class="tts-div_contents_cell_contents">
				<div class="tts-div_table">
					<div class="tts-div_table_title tts-div_table_row">
						<div class="tts-div_enable_check">활성</div>
						<div class="tts-div_language_type">구분</div>
						<div class="tts-div_language_name">언어명</div>
					</div>
					<div class="div_table_content" id="tts-sortable">
						<?=load_tts_support_list() ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
