<?php
	//ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
	//error_reporting(0); // Disable all errors.

	// set process name
	cli_set_process_title("ws_priority_interface");

	$g_file_server_path    = realpath(__FILE__);
	$g_file_name           = basename(__FILE__);
	$g_server_path         = str_replace($g_file_name, "", $g_file_server_path);
	
	// damon test code : $g_server_path = "/home/interm/priority_daemon_test/"
	$g_server_path         = "/opt/interm/bin/";
	$g_sql_interface_path  = $g_server_path . "..//public_html/common/common_sqlite_interface.php";
	$g_priority_db_path    = $g_server_path . "../conf/priority_manager.ver.1.0.db";
	$g_em_priority_db_path = $g_server_path . "../conf/em_priority_manager.ver.1.0.db";
	
	// damon test code : $g_websocket_path = $g_server_path . "../public_html/api/api_websocket.php" -> "/home/interm/priority_daemon_test/api_websocket.php"
	$g_websocket_path      = $g_server_path . "../public_html/api/api_websocket.php";
	
	include_once $g_websocket_path;
	
	
	$g_ws_handler = new WebsocketHandler("127.0.0.1", "priority_interface", true);
	
	$g_priority = new priority_util($g_priority_db_path, $g_sql_interface_path, "normal", true);
	//$g_em_priority = new priority_util($g_em_priority_db_path, $g_sql_interface_path, "em", true);
	/*
	 * +------------------------+---------------------------+
	 * | Normal Priority Table  | Emergency Priority Table  |
	 * +------------------------+---------------------------+
	 * | f_no                   | f_no                      |
	 * | f_mode                 | f_mode           (em)     |
	 * | f_priority             | f_priority                |
	 * | f_channel              | f_channel                 |
	 * | f_type                 | f_type                    |
	 * | f_status               | f_status                  |
	 * | f_delayMs              | f_delayMs        (not use)|
	 * | f_ip                   | f_ip                      |
	 * | f_svr_channel          | f_svr_channel    (not use)|
	 * | f_port                 | f_port                    |
	 * | f_cp                   | f_cp                      |
	 * | f_rm                   | f_rm                      |
	 * | f_count                | f_count                   |
	 * | f_filename             | f_filename                |
	 * |                        | f_min_rec                 |
	 * |                        | f_max_rec                 |
	 * |                        | f_multi_broad             |
	 * +------------------------+---------------------------+
	 */
	
	while(true) {
		$ws_data =null;
		if(!$g_ws_handler->is_run()) {
			break;
		}
		// recv data
		while(!$ws_data = $g_ws_handler->recv()) {
			//echo "wait recv data\n";
			if( $g_ws_handler->is_term() ) {
				$g_ws_handler = new WebsocketHandler("127.0.0.1", "priority_interface", true);
			}
		}
		
		/*
	      Command List
	      +---------+---------------------------------+
	      | cmd_id  | Desc                            |
	      +---------+---------------------------------+
	      | 0x00    | Normal priority process         |
		  | 0x01    | Emergency priority process      |
	      | 0x10    | Normal force stop               |
		  | 0x11    | Emergency force stop            |
	      +---------+---------------------------------+
	    */
	    if($ws_data != null) {
	    	// $ws_data["cmd_id"]
			// $ws_data["route_case"]
			// $ws_data["is_binary"]
			// $ws_data["is_extend"]
			// $ws_data["length"]
			switch($ws_data["cmd_id"]) {
				case 0x00:
					echo "running Normal Priority Operation" . "\n";
					$tmp_data = $g_priority->execute_operation(json_decode($ws_data["data"], true));
					var_dump($tmp_data);
				break;
					
				case 0x01:
					echo "running Emergency Priority Operation" . "\n";
					$tmp_data = $g_em_priority->execute_operation(json_decode($ws_data["data"], true));
					if($g_em_priority->is_running()) {
						$g_priority->recovery_output();
					} else {
						$g_priority->recovery_action_output();
					}
					var_dump($tmp_data);
				break;
				
				case 0x10:
					echo "running Normal Force Stop" . "\n";
					$g_priority->force_stop();
				break;
					
				case 0x11:
					echo "running Emergency Force Stop" . "\n";
					$g_em_priority->force_stop();
				break;
				
				// 20.04.23 adding
				//  - yyo : 동작 회복
				// recovery priority
				case 0x20:
					$g_priority->set_is_norun_audio_event(false);
					$g_priority->recovery_action_output();
				break;
				
				// 20.04.23 adding
				//  - yyo : 오디오 사용 모듈 중지
				// stop audio module & local play module
				case 0x21:
					$g_priority->set_is_norun_audio_event(true);
					$g_priority->stop_audio_event();

				break;
					
				default:
					// do nothing
				break;
			}
		} else {
			echo "wsData is null\n";
		}
	}

	/**
	 * 초기 생성값
	 * @param $_priority_db_path   : 사용하는 우선순위 db 경로
	 * @param $_sql_interface_path : 사용하는 sqlite interface 소스 경로
	 * @param $_priority_type      : 우선순위 타입 정의 (normal : 일반 우선순위 | em : 비상 우선순위)
	 * @param $_is_debug           : 디버깅 사용 여부 확인
	 */
	class priority_util {
		private $add_priority_array;
		private $current_priority_info_array;
		private $del_priority_array;
		private $is_debug;
		private $mod_priority_array;
		private $old_priority_info_array;
		private $out_max_cnt;
		private $priority_db_path;
		private $priority_info_array;
		private $priority_type;
		private $sql_interface_path;
		private $ws_cmd_id = 0x20;
		private $is_running;
		private $is_norun_audio_event;
		
		//// common

		function __construct($_priority_db_path, $_sql_interface_path, $_priority_type, $_is_debug = false) {
			
			// init params
			{
				$this->is_debug                     = $_is_debug;
				$this->priority_db_path             = $_priority_db_path;
				$this->sql_interface_path           = $_sql_interface_path;
				$this->priority_type                = $_priority_type;
			}
			
			// get priority info
			{
				$this->priority_info_array          = $this->get_priority_info_array();
				$this->is_running					= count($this->priority_info_array) > 0 ? true : false;
				$this->out_max_cnt					= $this->get_output_max_count();
			}
			
			// set norun audio event flag ( default = false )
			{
				$this->is_norun_audio_event			= false;
			}
		}

		/**
		 * DB 정보 변경
		 * @param $_db_change_array
		 *  insert, update, delete 기능 수행하기 위한 변수
		 *  format : ["add" : "insert into ****", "del" : "delete ****", "mod" : "update ****"]
		 */
		function change_db_value($_db_change_array) {
			include_once $this->sql_interface_path;
			
			// insert
			$query = $_db_change_array["add"];
			if($query != "") {
				$this->custom_query_interface($this->priority_db_path, $query);
			}
			
			// delete
			$query = $_db_change_array["del"];
			if($query != "") {
				$this->custom_query_interface($this->priority_db_path, $query);
			}
			
			// update
			foreach($_db_change_array["mod"] as $db_update_string) {
				$query = $db_update_string;
				$this->custom_query_interface($this->priority_db_path, $query);
			}
		}
		
		/**
		 * 실행 post 비교함수
		 * old와 current 비교하여 동작할 대상을 반환
		 * old가 비어있을 경우     : current의 실행값을 반환
		 * current가 비어있을 경우 : old의 종료값을 반환
		 * 둘 다 존재할 경우       : current의 실행값을 반환
		 * @param $_old_running_action_string     : format( "0" : 종료 json, "1" : 실행 json )
		 * @param $_current_running_action_string : format( "0" : 종료 json, "1" : 실행 json )
		 * @return 종료 json 혹은 실행 json string
		 */
		function compare_post_running_action($_old_running_action_string, $_current_running_action_string) {
			if($_old_running_action_string == null) {
				// return running action
				return json_encode(json_decode($_current_running_action_string, true)["1"], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
			}
			if($_current_running_action_string == null) {
				// return stopping action
				return json_encode(json_decode($_old_running_action_string, true)["0"], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
			}
			return json_encode(json_decode($_current_running_action_string, true)["1"], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
		}
		
		/**
		 * 우선순위 실행할 데이터를 db 데이터로 변환
		 * @param $_priority_data_array : json 형식의 우선순위 동작 정보 배열
		 * @param $_mode : 동작 type 정의 (button, event, em)
		 * @return 변환된 우선순위 동작 배열
		 */
		function convert_priority_data_array($_priority_data_array, $_mode = null) {
			if($_priority_data_array == null) {
				return array();
			}
			$tmp_priority_info = array();
			if($_mode == null) {
				$_mode = $_priority_data_array["priority_mode"];
				unset($_priority_data_array["priority_mode"]);
			}
			
			foreach($_priority_data_array as $_priority_data) {
				if($this->priority_type == "normal") {
					array_push($tmp_priority_info, $this->convert_priority_data($_priority_data, $_mode));
				} else if($this->priority_type == "em") {
					array_push($tmp_priority_info, $this->convert_em_priority_data($_priority_data, $_mode));
				}
			}
			
			return $tmp_priority_info;
		}

		/**
		 * 접점정보를 병합하여 동작정보 반환
		 * @param $_priority_info : 변환된 우선순위 정보
		 * @param $_output_string : format( "0" : 종료 json, "1" : 실행 json )
		 * @return $_output_string
		 */
		function create_running_contact_data($_priority_info, $_output_string) {
			if(($_priority_info["f_cp"] != null && $_priority_info["f_cp"] != "") || ($_priority_info["f_rm"] != null && $_priority_info["f_rm"] != "")) {
				if($_output_string != "") {
					$tmp_output_json = json_decode($_output_string, true);
					$tmp_cp_string = implode(",", $tmp_output_json["1"]["data"]["CP"]);
					$tmp_rm_string = implode(",", $tmp_output_json["1"]["data"]["RM"]);
					$tmp_cp_string = $this->merge_output_value($tmp_cp_string, $_priority_info["f_cp"]);
					$tmp_rm_string = $this->merge_output_value($tmp_rm_string, $_priority_info["f_rm"]);
					$tmp_output_json["1"]["data"]["CP"] = explode(",", $tmp_cp_string);
					$tmp_output_json["1"]["data"]["RM"] = explode(",", $tmp_rm_string);
					
					return json_encode($tmp_output_json, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
				} else {
					$tmp_cp = $this->merge_output_value("", $_priority_info["f_cp"]);
					$tmp_rm = $this->merge_output_value("", $_priority_info["f_rm"]);
					
					$extData = array(
						"0"    => array(
							"type" => "post",
							"uri" => 'http://127.0.0.1/api/output/setOutputList',
							"data" => array(
									"CP"	=> array_fill(0, $this->out_max_cnt["CP"]["out"], "0"),
									"RM"	=> array_fill(0, $this->out_max_cnt["RM"]["out"], "0")
							)
						),
						"1"    => array(
							"type" => "post",
							"uri" => 'http://127.0.0.1/api/output/setOutputList',
							"data" => array(
									"CP"	=> explode(",", $tmp_cp),
									"RM"	=> explode(",", $tmp_rm)
							)
						)
					);
					return json_encode($extData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
				}
			} else {
				return $_output_string;
			}
		}

		/**
		 * 디버그용 함수(echo 와 동일한 함수로 파라미터를 받아서 화면에 보여줌)
		 * ex : dbg_printf("this is %s", "dbg_printf")
		 */
		function dbg_printf() {
			if($this->is_debug === false) {
				return;
			}

			$args = func_get_args();
			$fmt  = array_shift($args);

			echo vsprintf($fmt, $args);
		}
		
		/**
		 * 메인 함수, 저장 데이터 갱신 및 동작까지 수행(강제 종료 제외)
		 * @param $_priority_data : api에서 받은 post 데이터
		 */
		function execute_operation($_priority_data) {
			$this->init();
			//$this->dbg_printf()
			$tmp_priority_info_array                = $this->convert_priority_data_array($_priority_data);
			$this->old_priority_info_array          = $this->priority_info_array;
			
			// check not use priority info array
			$tmp_priority_info_array = $this->validation_priority_info_array($tmp_priority_info_array);
			if(count($tmp_priority_info_array) <= 0) {
				return array("result" => "do nothing");
			}
			
			if($this->priority_type == "normal") {
				$this->current_priority_info_array  = $this->apply_priority_info_array($tmp_priority_info_array);
			} else if($this->priority_type == "em") {
				$this->current_priority_info_array  = $this->apply_em_priority_info_array($tmp_priority_info_array);
			}
			
			// check normal prioriry process and running emergency priority status
			if($this->priority_type == "normal" && file_exists("/tmp/action_em_status")) {
				$tmp_output_running_info_string         = $this->get_running_contact_string($this->priority_info_array);
				$tmp_output_running_info_json           = json_decode($tmp_output_running_info_string, true);
				$tmp_output_cp_string                   = implode(",", $tmp_output_running_info_json["1"]["data"]["CP"]);
				$tmp_output_rm_string                   = implode(",", $tmp_output_running_info_json["1"]["data"]["RM"]);
				
				$tmp_current_output_running_info_string = $this->get_running_contact_string($this->current_priority_info_array);
				$tmp_current_output_running_info_json   = json_decode($tmp_current_output_running_info_string, true);
				$tmp_current_output_cp_string           = implode(",", $tmp_current_output_running_info_json["1"]["data"]["CP"]);
				$tmp_current_output_rm_string           = implode(",", $tmp_current_output_running_info_json["1"]["data"]["RM"]);
				
				$tmp_current_output_cp_string           = $this->tight_output_value($tmp_current_output_cp_string, $tmp_output_cp_string);
				$tmp_current_output_rm_string           = $this->tight_output_value($tmp_current_output_rm_string, $tmp_output_rm_string);
				
				$tmp_result_output_cp_array             = explode(",", $tmp_current_output_cp_string);
				$tmp_result_output_rm_array             = explode(",", $tmp_current_output_rm_string);
				if( in_array("1", $tmp_result_output_cp_array) || in_array("0", $tmp_result_output_cp_array) ||
					in_array("1", $tmp_result_output_rm_array) || in_array("0", $tmp_result_output_rm_array)
				) {
					$tmp_current_output_running_info_json["1"]["data"]["CP"] = $tmp_result_output_cp_array;
					$tmp_current_output_running_info_json["1"]["data"]["RM"] = $tmp_result_output_rm_array;

					// execute contact operation
					$tmp_result_output_string = json_encode($tmp_current_output_running_info_json["1"]["data"], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
					$this->run_post_data($tmp_current_output_running_info_json["1"]["uri"], $tmp_result_output_string);
				}
			}
			
			// change new priority info array
			$this->priority_info_array = $this->current_priority_info_array;

			$tmp_db_change_array = array();
			// change db values
			// insert
			$tmp_db_change_array["add"] = $this->get_db_string_add($this->add_priority_array);
			// update
			$tmp_db_change_array["mod"] = $this->get_db_string_array_mod($this->mod_priority_array);
			// delete
			$tmp_db_change_array["del"] = $this->get_db_string_del($this->del_priority_array);
			$this->change_db_value($tmp_db_change_array);

			// check normal prioriry process and running emergency priority status
			if($this->priority_type == "normal" && file_exists("/tmp/action_em_status")) {
				echo "file exist action_em_status\n";
			
				// unset insert, delete, update flag
				foreach($this->current_priority_info_array as $priority_info_key => $priority_info) {
					unset($priority_info["is_insert"]);
					unset($priority_info["is_update"]);
					unset($priority_info["is_delete"]);
					$this->current_priority_info_array[$priority_info_key] = $priority_info;
				}
				
				return $this->priority_info_array;
			}

			// create running information
			$tmp_old_running_info_string = array();
			$tmp_current_running_info_string = array();
			$tmp_post_running_info_string = array();
			if($this->priority_type == "normal") {
				// audio_stream, broad_file, broad_folder, contact
				// setOutputList
				// audio/client/initrun
				// source_file/setPlayInfo
				$tmp_old_running_info_string     = $this->get_running_priority_string($this->old_priority_info_array);
				$tmp_current_running_info_string = $this->get_running_priority_string($this->current_priority_info_array);
			} else if($this->priority_type == "em") {
				// emergency_audio_stream, emergency_play_file
				// setOutputList
				$tmp_old_running_info_string     = $this->get_running_em_priority_string($this->old_priority_info_array);
				$tmp_current_running_info_string = $this->get_running_em_priority_string($this->current_priority_info_array);
			}
			
			if($tmp_old_running_info_string["action"] != $tmp_current_running_info_string["action"]) {
				//$tmp_post_running_info_string["action"] = $tmp_current_running_info_string["action"];
				$tmp_post_running_info_string["action"] = $this->compare_post_running_action($tmp_old_running_info_string["action"], $tmp_current_running_info_string["action"]);
			}
			if($tmp_old_running_info_string["contact"] != $tmp_current_running_info_string["contact"]) {
				//$tmp_post_running_info_string["contact"] = $tmp_current_running_info_string["contact"];
				$tmp_post_running_info_string["contact"] = $this->compare_post_running_action($tmp_old_running_info_string["contact"], $tmp_current_running_info_string["contact"]);
			}
			
			// unset insert, delete, update flag
			foreach($this->current_priority_info_array as $priority_info_key => $priority_info) {
				unset($priority_info["is_insert"]);
				unset($priority_info["is_update"]);
				unset($priority_info["is_delete"]);
				$this->current_priority_info_array[$priority_info_key] = $priority_info;
			}
			
			// change new priority info array
			$this->priority_info_array = $this->current_priority_info_array;
			
			// running operation
			if(count($tmp_post_running_info_string) > 0) {
				foreach($tmp_post_running_info_string as $running_info) {
					$running_info_json = json_decode($running_info, true);
					if($running_info_json["type"] == "post") {
						$this->run_post_data($running_info_json["uri"], json_encode($running_info_json["data"], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
					} else if($running_info_json["type"] == "websocket") {
						$this->run_socket_data($running_info_json["uri"], $this->ws_cmd_id, json_encode($running_info_json["data"], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
					} else if($running_info_json["type"] == "get") {
						$this->run_get_data($running_info_json["uri"]);
					}
				}
			}
			
			echo "running operation end\n";
			// check emergency status
			if($this->priority_type == "em") {
				if(count($this->priority_info_array) > 0) {
					echo "running emergency.\n";
					shell_exec('touch /tmp/action_em_status');
					$this->is_running = true;
				} else {
					echo "not running emergency.\n";
					shell_exec('rm /tmp/action_em_status');
					$this->is_running = false;
				}
			}
			return $this->priority_info_array;
		}

		/**
		 * 강제 종료 함수
		 * 우선순위 db의 값(rows)을 제거하고, output 및 동작을 종료하는 함수
		 */
		function force_stop() {
			include_once $this->sql_interface_path;
			
			// delete table values
			$query                                  = "DELETE FROM tbl_priority;";
			$this->custom_query_interface($this->priority_db_path, $query);
			
			if($this->priority_type == "normal") {
				$this->off_normal_action();
			} else if($this->priority_type == "em") {
				$this->off_em_action();
			}
			$this->off_output_list();
			
			$this->init();
			$this->priority_info_array = $this->get_priority_info_array();
			return "ok";
		}

		/**
		 * db값을 추가하기 위해 우선순위 배열값을 받아서 sql 쿼리로 반환
		 * @param $_priority_info_array : 변환된 우선순위 배열
		 * @return insert sql string (insert into ****)
		 */
		function get_db_string_add($_priority_info_array) {
			if($_priority_info_array == null) {
				return "";
			}
			$return_string = "insert into tbl_priority (";
			$column_name_array = array();
			// column names setting
			foreach($_priority_info_array as $priority_info) {
				foreach($priority_info as $column_name => $column_value) {
					if(!in_array($column_name, $column_name_array)) {
						array_push($column_name_array, $column_name);
						$return_string .= $column_name . ",";
					}
				}
			}
			
			$return_string = substr($return_string, 0, strlen($return_string)-1) . ")";
			
			// column value setting
			$return_string .= " values ";
			// column names setting
			foreach($_priority_info_array as $priority_info) {
				$return_string .= "(";
				foreach($column_name_array as $column_name) {
					
					if($priority_info[$column_name] == null) {
						$return_string .= "null,";
					} else {
						$return_string .= "'" . $priority_info[$column_name] . "',";
					}
				}
				$return_string = substr($return_string, 0, strlen($return_string)-1) . "),";
			}
			$return_string = substr($return_string, 0, strlen($return_string)-1) . ";";
			return $return_string;
		}

		/**
		 * db값을 제거하기 위해 우선순위 배열값을 받아서 sql 쿼리로 반환
		 * @param $_priority_info_array : 변환된 우선순위 배열
		 * @return delete sql string (delete from ****)
		 */
		function get_db_string_del($_priority_info_array) {
			if($_priority_info_array == null) {
				return "";
			}
			$return_string = "delete from tbl_priority where f_priority in (";
			foreach($_priority_info_array as $priority_info) {
				$return_string .= "'" . $priority_info["f_priority"] . "',";
			}
			$return_string = substr($return_string, 0, strlen($return_string)-1) . ");";
			return $return_string;
		}

		/**
		 * db값을 수정하기 위해 우선순위 배열값을 받아서 sql 쿼리로 반환
		 * @param $_priority_info_array : 변환된 우선순위 배열
		 * @return update sql string (["update **** set ****", "update **** set ****", ...])
		 */
		function get_db_string_array_mod($_priority_info_array) {
			if($_priority_info_array == null) {
				return array();
			}
			$return_array = array();
			// column names setting
			foreach($_priority_info_array as $priority_info) {
				$return_string = "update tbl_priority set ";
				if($priority_info["f_mode"] != "button") {
					foreach($priority_info as $column_name => $column_value) {
						if($column_name != "f_priority") {
							$return_string .= $column_name . " = '" . $column_value . "',";
						}
					}
					$return_string = substr($return_string, 0, strlen($return_string)-1);
					$return_string .= " where f_priority = '" . $priority_info["f_priority"] . "';";
				} else {
					foreach($priority_info as $column_name => $column_value) {
						if($column_name != "f_mode") {
							$return_string .= $column_name . " = '" . $column_value . "',";
						}
					}
					$return_string = substr($return_string, 0, strlen($return_string)-1);
					$return_string .= " where f_mode = '" . $priority_info["f_mode"] . "';";
				}

				array_push($return_array, $return_string);
			}
			
			return $return_array;
		}

		/**
		 * output의 최대값 반환
		 * @return {"CP" : {"out": "36, "in" : "10}, "RM" : {"out" : "48", "in" : "48"}}
		 */
		function get_output_max_count() {
			$uri = "http://127.0.0.1/api/getDeviceInfo";
			$result = $this->run_get_data($uri);
			$result = json_decode($result, true);
			
			return $result["result"]["port"];
		}

		/**
		 * 우선순위 db에서 값을 조회하여 우선순위 정보 배열 반환
		 * @return 우선순위 정보 배열 반환
		 */
		function get_priority_info_array() {
			include_once $this->sql_interface_path;
			
			$query                                  = "SELECT * FROM tbl_priority;";
			$tmp_priority_info_array = $this->custom_query_interface($this->priority_db_path, $query);
			
			return $tmp_priority_info_array;
		}
		
		/**
		 * array 에서 우선순위를 비교하여 해당 index를 반환하는 함수
		 * array 에서 우선순위를 비교하여 있으면 0 이상의 숫자, 없으면 -1를 반환
		 * @param $_priority_info_array : priority_info array ( format : (priority_info, priority_info, priority_info, ...) )
		 * @param $_priority_info : 우선순위 정보 ( "f_no" : 256, "f_mode" : "event", "f_priority" : "1000", "f_channel" : "1", ... )
		 */
		function get_priority_info_key($_priority_info_array, $_priority_info) {
			foreach($_priority_info_array as $priority_info_key => $priority_info) {
				if($priority_info["f_priority"] == $_priority_info["f_priority"]) {
					return $priority_info_key;
				}
			}
			
			// 버튼모드일 경우에는 시작시 우선순위가 존재하고, 종료시 우선순위가 없음
			if($_priority_info["f_mode"] == "button") {
				foreach($_priority_info_array as $priority_info_key => $priority_info) {
					if($priority_info["f_mode"] == $_priority_info["f_mode"]) {
						return $priority_info_key;
					}
				}
			}
			return -1;
		}

		/**
		 * 해당 파일의 directory 반환
		 * @return /opt/interm/public_html
		 */
		function get_svr_root() {
			$file_server_path    = realpath(__FILE__);
			$file_name           = basename(__FILE__);
			//return $server_path         = str_replace($g_file_name, "", $g_file_server_path) . "../public_html/";
			return $server_path         = "/opt/interm/public_html/";
		}

		/**
		 * 관리 장비(controller)의 서버 정보 반환
		 * @return 서버 정보 배열
		 */
		function get_used_mng_svr_info() {
			$svr_root = $this->get_svr_root();
			$confPath = $svr_root . "../conf/config-manager-server.db";
	
			$query   = "select * from mng_svr_info where mng_svr_used='1'; ";
			$results = $this->custom_query_interface($confPath, $query);
			
			foreach($results as $result) {
				$row = $result;
				break;
			}
	
			/* API key matching check */
			$load_hashTable	= file_get_contents($svr_root . "../key_data/device_key_list.json");
			$hashTable		= json_decode($load_hashTable, true);
			$flagRemote		= false;
	
			foreach( $hashTable as $key => $keyInfo ) {
				if( is_array($keyInfo) ) {
					foreach( $keyInfo as $secretKey => $secretInfo ) {
						if( $row["mng_svr_ip"] == $secretInfo["server_addr"] ) {
							$flagRemote = true;
	
							break;
						}
					}
				}
	
				if( $flagRemote == true ) break;
			}
	
			$svrInfo = null;
			if( $flagRemote ) {
				$svrInfo["mng_svr_ip"] 		= $row["mng_svr_ip"];
				$svrInfo["mng_svr_port"]	= $row["mng_svr_port"];
				$svrInfo["api_key"]			= $key;
				$svrInfo["api_secret"]		= $secretKey;
			}
	
			return $svrInfo;
		}

		/**
		 * 동작을 위한 변수 초기화
		 */
		function init() {
			$this->old_priority_info_array          = null;
			$this->current_priority_info_array      = null;
			$this->add_priority_array				= null;
			$this->del_priority_array				= null;
			$this->mod_priority_array				= null;
			if( $this->out_max_cnt == null ) {
				$this->out_max_cnt					= $this->get_output_max_count();
			}
		}
		
		/**
		 * 왼쪽 값과 오른쪽 값을 병합하는 함수
		 * 왼쪽 값과 오른쪽 값중 하나가 -1 이면 -1이 아닌 값으로 대체됨<br>([-1  <->  1]  -->> [1])
		 * 왼쪽 값와 오른쪽 값이 -1이 아닌 다른 값이라면 왼쪽값으로 대체됨<br> ([0  <->  1] -->> [0])
		 * @param $_left_output_value : org value ( format : "-1,1,0,-1" )
		 * @param $_right_output_value : compare value ( format : "-1,1,0,-1" )
		 * @return "-1,1,0,-1,..." string 반환
		 */
		function merge_output_value($_left_output_value, $_right_output_value) {
			if($_left_output_value == null || $_left_output_value == "") {
				$_left_output_value = "-1,-1";
			}
			if($_right_output_value == null || $_right_output_value == "") {
				$_right_output_value = "-1,-1";
			}
			$left_output_value_array = explode(",", $_left_output_value);
			$right_output_value_array = explode(",", $_right_output_value);
			$is_switch = count($left_output_value_array) >= count($right_output_value_array) ? false : true;
			if($is_switch) {
				$tmp_array = $left_output_value_array;
				$left_output_value_array = $right_output_value_array;
				$right_output_value_array = $tmp_array;
			}
			
			foreach($right_output_value_array as $key => $r_value) {
				if($r_value == "-1"){
					continue;
				}
				if($left_output_value_array[$key] == "-1") {
					$left_output_value_array[$key] = $r_value;
					continue;
				}
				if($left_output_value_array[$key] != $r_value) {
					$left_output_value_array[$key] = $is_switch ? $r_value : $left_output_value_array[$key];
					continue;
				}
			}
			
			return implode(",", $left_output_value_array);
		}

		/**
		 * 왼쪽 값에서 오른쪽 값과 같은 값을 -1로 변경하는 함수
		 * 오른쪽 값이 1 이면 왼쪽의 값을 -1로 변경 ([1  <->  1]  -->>  [-1])
		 * 오른쪽 값이 0 이면 왼쪽의 값을 -1로 변경 ([1  <->  0]  -->>  [-1])
		 * 오른쪽 값이 더 많을 경우 무시됨
		 * @param $_left_output_value : org value ( format : "-1,1,0,-1" )
		 * @param $_right_output_value : compare value ( format : "-1,1,0,-1" )
		 * @return "-1,1,0,-1,..." string 반환
		 */
		function minus_output_value($_left_output_value, $_right_output_value) {
			if($_left_output_value == null || $_left_output_value == "") {
				$_left_output_value = "-1";
			}
			if($_right_output_value == null || $_right_output_value == "") {
				$_right_output_value = "-1";
			}
			
			$left_output_value_array = explode(",", $_left_output_value);
			$right_output_value_array = explode(",", $_right_output_value);
			
			foreach($left_output_value_array as $key => $r_value) {
				if($right_output_value_array[$key] == null){
					break;
				}
				
				if($right_output_value_array[$key] == "1" ||  $right_output_value_array[$key] == "0") {
					$left_output_value_array[$key] = "-1";
					continue;
				}
			}
			
			return implode(",", $left_output_value_array);
		}

		/**
		 * output 정보를 종료하는 함수
		 */
		function off_output_list() {
			// output list all off
			$post_data = array(
				"CP"	=> array_fill(0, $this->out_max_cnt["CP"]["out"], "0"),
				"RM"	=> array_fill(0, $this->out_max_cnt["RM"]["out"], "0")
			);
			$this->run_post_data("http://127.0.0.1/api/output/setOutputList", json_encode($post_data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
		}

		/**
		 * curl 로 get 방식 동작 후 결과 반환
		 * @return curl 로 get 방식 동작 후 결과값
		 */
		function run_get_data($_uri) {
			// 20.04.23 adding
			//  - yyo : norun event flag 가 true 일 경우 get 명령 실행하지 않음
			if( $this->is_norun_audio_event ) {
				return null;
			}
			
			if( ($svrInfo = $this->get_used_mng_svr_info()) == null ) {
				return null;
			}
	
			$device_id  = $svrInfo["api_key"];
			$secret_key = $svrInfo["api_secret"];

			$headers = array(
								'Content-type: application/json',
								'X-Interm-Device-ID: ' . $device_id,
								'X-Interm-Device-Secret: ' . $secret_key
							);
			$curlsession = curl_init();
			curl_setopt($curlsession, CURLOPT_URL, $_uri);
			curl_setopt($curlsession, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curlsession, CURLOPT_HTTPHEADER, $headers);
	
			$result = curl_exec($curlsession);
	
			curl_close($curlsession);
			return $result;
		}

		/**
		 * curl 로 post 방식 동작 후 결과 반환
		 * @return curl 로 post 방식 동작 후 결과값
		 * 20.04.23 adding
		 *  - yyo : is_norun_audio_event flag 반영. true 일 경우 audio client 와 local play는 실행하지 않음 
		 */
		function run_post_data($_uri, $_post_data) {
			// 20.04.23 adding
			//  - yyo : norun event flag 가 true 일 경우 post 명령 실행하지 않음
			if( $this->is_norun_audio_event ) {
				return null;
			}
			
			if( ($svrInfo = $this->get_used_mng_svr_info()) == null ) {
				return null;
			}
			$device_id  = $svrInfo["api_key"];
			$secret_key = $svrInfo["api_secret"];
	
			$headers = array(
								'Content-type: application/json',
								'X-Interm-Device-ID: ' . $device_id,
								'X-Interm-Device-Secret: ' . $secret_key
							);
	
			$curlsession = curl_init();
			curl_setopt($curlsession, CURLOPT_URL, $_uri);
			curl_setopt($curlsession, CURLOPT_POST, 1);
			curl_setopt($curlsession, CURLOPT_POSTFIELDS, $_post_data);
			curl_setopt($curlsession, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curlsession, CURLOPT_HTTPHEADER, $headers);
	
			$result = curl_exec($curlsession);
	
			curl_close($curlsession);
			
			return $result;
		}

		/**
		 * websocket 생성하여 데이터 전송 후 닫아줌
		 */
		function run_socket_data($_uri, $_cmd_id, $_post_data) {
			// 20.04.23 adding
			//  - yyo : norun event flag 가 true 일 경우 websocket 명령 실행하지 않음
			if( $this->is_norun_audio_event ) {
				return null;
			}
			
			$ws_handler = new WebsocketHandler("127.0.0.1", $_uri);
			if($_uri == "audio_client") {
				$postData   = array("emergency" => false);
				$data = json_encode($postData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
				$ws_handler->send($_cmd_id, $data);
				usleep(100000);
			}
			$ws_handler->send($_cmd_id, $_post_data);
			$ws_handler->term();
		}

		/**
		 * db에 추가할 우선순위 정보 저장
		 * @param $_priority_info : 변환된 우선순위 정보
		 */
		function set_add_priority_info($_priority_info) {
			if($this->add_priority_array == null) {
				$this->add_priority_array = array();
			}
			$priority_info_key = $this->get_priority_info_key($this->add_priority_array, $_priority_info);
			if($priority_info_key < 0) {
				array_push($this->add_priority_array, $_priority_info);
			} else {
				$this->add_priority_array[$priority_info_key] = $_priority_info;
			}
		}

		/**
		 * db에서 삭제할 우선순위 정보 저장
		 * @param $_priority_info : 변환된 우선순위 정보
		 */
		function set_del_priority_info($_priority_info) {
			if($this->del_priority_array == null) {
				$this->del_priority_array = array();
			}
			$priority_info_key = $this->get_priority_info_key($this->del_priority_array, $_priority_info);
			if($priority_info_key < 0) {
				array_push($this->del_priority_array, $_priority_info);
			} else {
				$this->del_priority_array[$priority_info_key] = $_priority_info;
			}
		}
		
		/**
		 * 20.04.23 adding
		 *  - yyo : 함수 추가
		 * 오디오 이벤트 실행 flag 변경
		 * @param $_flag : 변경할 flag값 ( true or false )
		 */
		function set_is_norun_audio_event($_flag) {
			$this->is_norun_audio_event = $_flag;
		}

		/**
		 * db에서 수정할 우선순위 정보 저장
		 * @param $_priority_info : 변환된 우선순위 정보
		 */
		function set_mod_priority_info($_priority_info) {
			if($this->mod_priority_array == null) {
				$this->mod_priority_array = array();
			}
			$priority_info_key = $this->get_priority_info_key($this->mod_priority_array, $_priority_info);
			if($priority_info_key < 0) {
				array_push($this->mod_priority_array, $_priority_info);
			} else {
				$this->mod_priority_array[$priority_info_key] = $_priority_info;
			}
		}
		
		/**
		 * 20.04.23 adding
		 *  - yyo : 함수 추가
		 * 오디오 이베트 종료
		 * 대상 : audio client / local play ( source file management )
		 */
		function stop_audio_event () {
			// event : stop audio client (API)
			$uri  = 'http://127.0.0.1/api/audio/client/stop';
			
			if( ($svrInfo = $this->get_used_mng_svr_info()) == null ) {
				return null;
			}
			
			$device_id  = $svrInfo["api_key"];
			$secret_key = $svrInfo["api_secret"];

			$headers = array(
								'Content-type: application/json',
								'X-Interm-Device-ID: ' . $device_id,
								'X-Interm-Device-Secret: ' . $secret_key
							);
			$curlsession = curl_init();
			curl_setopt($curlsession, CURLOPT_URL, $uri);
			curl_setopt($curlsession, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curlsession, CURLOPT_HTTPHEADER, $headers);
	
			$result = curl_exec($curlsession);
	
			curl_close($curlsession);
			
			// event : stop file play
			$uri = 'http://127.0.0.1/api/source_file/setPlayInfo';
			$post_data = array(
				"action"  	=> 'stop'
			);
			$curlsession = curl_init();
			curl_setopt($curlsession, CURLOPT_URL, $uri);
			curl_setopt($curlsession, CURLOPT_POST, 1);
			curl_setopt($curlsession, CURLOPT_POSTFIELDS, json_encode($post_data));
			curl_setopt($curlsession, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($curlsession, CURLOPT_HTTPHEADER, $headers);
	
			$result = curl_exec($curlsession);
	
			curl_close($curlsession);
			
			return true;
		}
		
		
		/**
		 * 첫번째 변수를 기준으로 1, 0, -1 로 표현해서 반환하는 함수
		 * old 값에서 current 값이 추가되면 1로 반환    ( ex : -1 | 1  ==>  1 )
		 * old 값에서 current 값이 추가되면 0으로 반환  ( ex : 1  | -1 ==>  0 )
		 * old 값과 current 값이 같으면 같은 값으로 반환( ex : -1 | -1 ==> -1 )<br>
		 * current	| old		| result
		 * 1,-1,-1	| -1,1,-1	| 1,0,-1
		 * @param $_current_output_string : 변경된 값 ( format : "1,-1" )
		 * @param $_old_output_string : 변경되기 전 값 ( format : "1,-1" )
		 * @return "-1,1,0,-1,..." string 반환
		 */
		function tight_output_value($_current_output_string, $_old_output_string) {
			if($_current_output_string == "") {
				$_current_output_string = "-1";
			}
			if($_old_output_string == "") {
				$_old_output_string = "-1";
			}
			$tmp_current_array = explode(",", $_current_output_string);
			$tmp_old_array = explode(",", $_old_output_string);
			$tmp_current_cnt = count($tmp_current_array);
			$tmp_old_cnt = count($tmp_old_array);
			if($tmp_old_cnt > $tmp_current_cnt) {
				foreach($tmp_old_array as $key => $output_value) {
					if(!isset($tmp_current_array[$key])) {
						$tmp_current_array[$key] = $output_value;
						continue;
					}
					// get current value
					$tmp_value = $tmp_current_array[$key];
					if($output_value == $tmp_value) {
						continue;
					} else if($output_value == "1" && $tmp_value == "-1") {
						$tmp_current_array[$key] = "0";
					}
				}
			} else {
				foreach($tmp_current_array as $key => $output_value) {
					if(!isset($tmp_old_array[$key])) {
						break;
					}
					// get old value
					$tmp_value = $tmp_old_array[$key];
					if($output_value == $tmp_value) {
						continue;
					} else if($tmp_value == "1" && $output_value == "-1") {
						$tmp_current_array[$key] = "0";
					}
				}
			}
			return implode(",", $tmp_current_array);	
		}

		/**
		 * 실행 가능한 우선순위 정보를 반환하는 함수
		 * 일부 정보 update, insert, delete 저장
		 * 버튼모드에서 동작할 경우 접점의 갯수에 따라 update 혹은 delete
		 * off 동작이 실행목록에 없을 경우 제거
		 * on 동작이 실행목록에 있을 경우 제거
		 * @param $_priority_info_array : 변환된 우선순위 정보 배열
		 * @return 동작가능한 우선순위 정보 배열
		 */
		function validation_priority_info_array($_priority_info_array) {
			foreach($_priority_info_array as $priority_info_key => $priority_info) {
				$tmp_priority_info_key = $this->get_priority_info_key($this->priority_info_array, $priority_info);
				if($priority_info["f_status"] == "1") {
					// unset exist priority info
					if($tmp_priority_info_key > -1) {
						if($priority_info["f_mode"] != "button") {
							unset($_priority_info_array[$priority_info_key]);
						} else {
							$this->set_mod_priority_info($_priority_info_array[$priority_info_key]);
							$_priority_info_array[$priority_info_key]["is_update"] = "true";
						}
					} else {
						$this->set_add_priority_info($_priority_info_array[$priority_info_key]);
						$_priority_info_array[$priority_info_key]["is_insert"] = "true";
					}
					continue;
				}
				
				if($priority_info["f_status"] == "0") {
					// unset not exist priority info
					if($tmp_priority_info_key < 0) {
						unset($_priority_info_array[$priority_info_key]);
					} else {
						if($priority_info["f_mode"] != "button") {
							$this->set_del_priority_info($_priority_info_array[$priority_info_key]);
							$_priority_info_array[$priority_info_key]["is_delete"] = "true";
						} else {
							// change cp or rm value ( 0 => 1 )
							$priority_info["f_cp"] = str_replace("0", "1", $priority_info["f_cp"]);
							$priority_info["f_rm"] = str_replace("0", "1", $priority_info["f_rm"]);
							
							// fill empty or null values ( db priority array )
							foreach($priority_info as $info_key => $info_value) {
								if($info_value == null || $info_value == "") {
									unset($priority_info[$info_key]);
								}
							}
							$_priority_info_array[$priority_info_key] = array_merge($this->priority_info_array[$tmp_priority_info_key], $priority_info);
							$priority_info = $_priority_info_array[$priority_info_key];
							if( $priority_info["f_cp"] == $this->priority_info_array[$tmp_priority_info_key]["f_cp"] &&
								$priority_info["f_rm"] == $this->priority_info_array[$tmp_priority_info_key]["f_rm"]
							) {
								echo "priority info is delete.\n";
								$this->set_del_priority_info($_priority_info_array[$priority_info_key]);
								$_priority_info_array[$priority_info_key]["is_delete"] = "true";
							} else if(	!in_array("1", explode(",", $this->minus_output_value($this->priority_info_array[$tmp_priority_info_key]["f_cp"], $priority_info["f_cp"]))) &&
										!in_array("1", explode(",", $this->minus_output_value($this->priority_info_array[$tmp_priority_info_key]["f_rm"], $priority_info["f_rm"])))
							) {
								echo "priority info is delete.(not operation.)\n";
								$this->set_del_priority_info($_priority_info_array[$priority_info_key]);
								$_priority_info_array[$priority_info_key]["is_delete"] = "true";
							} else {
								echo "priority info is update.\n";
								$this->set_mod_priority_info($_priority_info_array[$priority_info_key]);
								$_priority_info_array[$priority_info_key]["is_update"] = "true";
							}
						}
					}
					continue;
				}
			}
			
			return $_priority_info_array;
		}
		
		function custom_query_interface($_path, $_query) {
			$returnVal = null;
			if( strpos(strtolower($_query), 'select') > -1 ) {
				$returnVal = json_decode(query_interface($_path, $_query), true);

				while(gettype($returnVal) != "array") {
					$returnVal = json_decode(query_interface($_path, $_query), true);
					usleep(20000);
				}
				
			} else {
				$returnVal = query_interface($_path, $_query);
				
				while($returnVal != 1) {
					$returnVal = query_interface($_path, $_query);
				}
			}
			return $returnVal;
		}
		
		//// common end.
		
		//// normal priority
		/**
		 * 기존 실행목록에 새로운 우선순위 정보를 더하여 반환
		 * 버튼 모드의 종료 우선순위 정보일 경우 접점 전체가 종료일 경우 제거(아니라면 업데이트)
		 * 종료 우선순위 정보가 올 경우 제거
		 * 실행 우선순위 정보가 올 경우 추가
		 * @param $_priority_info_array : 변환된 새로운 우선순위 정보 배열
		 * @return 기존 실행 우선순위 배열 + 새로운 우선순위 정보 배열
		 */
		function apply_priority_info_array($_priority_info_array) {
			$tmp_priority_info_array = $this->old_priority_info_array;
			
			foreach($_priority_info_array as $priority_key => $priority_info) {
				if($priority_info["f_status"] == "0") {
					$tmp_priority_key                               = $this->get_priority_info_key($tmp_priority_info_array, $priority_info);
					if($tmp_priority_key > -1) {
						if($priority_info["f_mode"] == "button") {
							if(isset($priority_info["is_update"])) {
								$tmp_priority_info = $tmp_priority_info_array[$tmp_priority_key];
								$tmp_priority_info["f_cp"]                  = $this->minus_output_value($tmp_priority_info["f_cp"], $priority_info["f_cp"]);
								$tmp_priority_info["f_rm"]                  = $this->minus_output_value($tmp_priority_info["f_rm"], $priority_info["f_rm"]);
								$tmp_priority_info_array[$tmp_priority_key] = $tmp_priority_info;
								$this->set_mod_priority_info($tmp_priority_info);
							} else {
								unset($tmp_priority_info_array[$tmp_priority_key]);
							}
						} else {
							unset($tmp_priority_info_array[$tmp_priority_key]);
							
						}
					}
				} else if($priority_info["f_status"] == "1") {
					$tmp_priority_key                               = $this->get_priority_info_key($tmp_priority_info_array, $priority_info);
					if($tmp_priority_key > -1) {
						//$tmp_priority_info                          = $tmp_priority_info_array[$tmp_priority_key];
						$tmp_priority_info                          = $priority_info;
						// 입력된 우선순위와 실행중인 우선순위가 다를 경우에는 접점이나 입력값을 합치지 않는다.
						if($priority_info["f_priority"] == $tmp_priority_info_array[$tmp_priority_key]["f_priority"]) {
							$tmp_priority_info["f_cp"]                  = $this->merge_output_value($priority_info["f_cp"], $tmp_priority_info_array[$tmp_priority_key]["f_cp"]);
							$tmp_priority_info["f_rm"]                  = $this->merge_output_value($priority_info["f_rm"], $tmp_priority_info_array[$tmp_priority_key]["f_rm"]);
							
						}
						
						$tmp_priority_info_array[$tmp_priority_key] = $tmp_priority_info;
					} else {
						array_push($tmp_priority_info_array, $priority_info);
					}
				}
			}

			$tmp_top_priority = null;
			$tmp_top_priority_info = null;
			foreach($tmp_priority_info_array as $priority_key => $priority_info) {
				$priority_info["f_status"] = "0";
				$tmp_priority_info_array[$priority_key] = $priority_info;
				if($tmp_top_priority == null) {
					if($priority_info["f_type"] == "contact") {
						continue;
					}
					$tmp_top_priority = $priority_info["f_priority"];
					$tmp_top_priority_info = $priority_info;
					continue;
				}
				if($tmp_top_priority * 1 > $priority_info["f_priority"] * 1) {
					$tmp_top_priority = $priority_info["f_priority"];
					$tmp_top_priority_info = $priority_info;
				}
			}
			
			
			// 높은 우선순위가 파일 재생 혹은 폴더 재생 혹은 스크립트로 실행될 경우
			//   해당 우선순위 + 접점만 status를 1로 변경
			// 높은 우선순위가 방송 수신으로 실행될 경우(audio_stream)
			//   audio_stream의 소스가 같은 우선순위의 status를 1로 변경
			$is_all_running = $tmp_top_priority == null ? true : false;
			$tmp_top_source_ip = null;
			if($tmp_top_priority_info["f_type"] == "audio_stream") {
				$tmp_top_source_ip = $tmp_top_priority_info["f_ip"];
			}
			
			foreach($tmp_priority_info_array as $priority_key => $priority_info) {
				if($priority_info["f_type"] == "contact") {
					$priority_info["f_status"] = "1";
					$tmp_priority_info_array[$priority_key] = $priority_info;
					continue;
				}
				if($tmp_top_priority == $priority_info["f_priority"]) {
					$priority_info["f_status"] = "1";
					$tmp_priority_info_array[$priority_key] = $priority_info;
					continue;
				}
				if($tmp_top_source_ip != null && $priority_info["f_ip"] == $tmp_top_source_ip) {
					$priority_info["f_status"] = "1";
					$tmp_priority_info_array[$priority_key] = $priority_info;
				}
			}
			return $tmp_priority_info_array;
		}

		/**
		 * API에서 보낸 우선순위 정보를 db 우선순위 정보로 변환
		 * @param $_priority_info : api에서 보낸 우선순위 정보 json
		 * @param $_mode : 동작 type 정의 (button, event, em)
		 * @return 우선순위 정보의 배열형태
		 */
		function convert_priority_data($_priority_info, $_mode) {
			$tmp_priority_info                      = array();
			$tmp_priority_info["f_mode"]            = $_mode;
			$tmp_priority_info["f_priority"]        = $_priority_info["priority"];
			$tmp_priority_info["f_channel"]         = ($_priority_info["type"] == "audio_stream" || $_priority_info["type"] == "broad_file" || $_priority_info["type"] == "broad_folder") ? $_priority_info["actions"][0]["data"]["rx"] : null;
			$tmp_priority_info["f_type"]            = $_priority_info["type"];
			$tmp_priority_info["f_status"]          = $_priority_info["isRun"];
			$tmp_priority_info["f_delayMs"]         = $_priority_info["type"] == "audio_stream" ? (isset($_priority_info["actions"][0]["data"]["delayTime"]) ? $_priority_info["actions"][0]["data"]["delayTime"] : null) : (isset($_priority_info["actions"][0]["data"]["delay"]) ? $_priority_info["actions"][0]["data"]["delay"] : null);
			if($_priority_info["type"] == "audio_stream") {
				$tmp_priority_info["f_ip"]          = $_priority_info["actions"][0]["data"]["ip"];
				$tmp_priority_info["f_svr_channel"] = $_priority_info["actions"][0]["data"]["tx"];
				$tmp_priority_info["f_port"]        = $_priority_info["actions"][0]["data"]["port"];
				$tmp_priority_info["f_cast_type"]   = $_priority_info["actions"][0]["data"]["castType"];
			}
			$tmp_priority_info["f_cp"]              = $_priority_info["type"] == "contact" ? $_priority_info["actions"][0]["data"]["CP"] : $_priority_info["actions"][1]["data"]["CP"];
			$tmp_priority_info["f_rm"]              = $_priority_info["type"] == "contact" ? $_priority_info["actions"][0]["data"]["RM"] : $_priority_info["actions"][1]["data"]["RM"];
			if($_priority_info["type"] == "broad_file" || $_priority_info["type"] == "broad_folder") {
				$tmp_priority_info["f_count"]       = $_priority_info["actions"][0]["data"]["count"];
				$tmp_priority_info["f_filename"]    = $_priority_info["actions"][0]["data"]["fileName"];
			}
			return $tmp_priority_info;
		}
		
		/**
		 * 오디오 실행정보 반환
		 * 전달받은 우선순위 정보로 방송 수신 동작정보 생성 후 반환(종료와 생성)
		 * @param $_priority_info : 변환된 우선순위 정보
		 * @return format( "0" : 종료 json, "1" : 실행 json )
		 */
		function create_running_audio_stream_data($_priority_info) {
			if($_priority_info["f_type"] != "audio_stream") {
				return "";
			}
			if( $_priority_info['f_cast_type'] == "unicast" ) {
				$extDta = array(
					"0"    => array(
						"type" => "get",
						"uri" => 'http://127.0.0.1/api/audio/client/stop'
					),
					"1"    => array(
						"type" => "post",
						"uri" => 'http://127.0.0.1/api/audio/client/initrun',
						"data" => array(
								"castType"	=> $_priority_info['f_cast_type'],
								"ipAddr1"	=> $_priority_info['f_ip'],
								"port1"		=> $_priority_info['f_port'],
								"delayMs"	=> $_priority_info['f_delayMs'] != null ? $_priority_info['f_delayMs'] : "0"
						)
					)
				);
			} else {
				$extDta = array(
					"0"    => array(
						"type" => "get",
						"uri" => 'http://127.0.0.1/api/audio/client/stop'
					),
					"1"    => array(
						"type" => "post",
						"uri" => 'http://127.0.0.1/api/audio/client/initrun',
						"data" => array(
								"castType"	=> $_priority_info['f_cast_type'],
								"ipAddr1"	=> $_priority_info['f_ip'],
								"port1"		=> $_priority_info['f_port'],
								"delayMs"	=> $_priority_info['f_delayMs'] != null ? $_priority_info['f_delayMs'] : "0"
						)
					)
				);
			}
				
			
			return json_encode($extDta, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
		}
		
		/**
		 * 음원 파일 재생 실행정보 반환
		 * 전달받은 우선순위 정보로 음원 파일 재생 동작정보 생성 후 반환(종료와 생성)
		 * @param $_priority_info : 변환된 우선순위 정보
		 * @return format( "0" : 종료 json, "1" : 실행 json )
		 */
		function create_running_broad_file_data($_priority_info) {
			if($_priority_info["f_type"] != "broad_file") {
				return "";
			}
			$extDta = array(
				"0"    => array(
					"type" => "post",
					"uri" => 'http://127.0.0.1/api/source_file/setPlayInfo',
					"data" => array(
							"action"  	=> 'stop'
					)
				),
				"1"    => array(
					"type" => "post",
					"uri" => 'http://127.0.0.1/api/source_file/setPlayInfo',
					"data" => array(
							"is_dir"	=> false,
							"fileName"	=> $_priority_info['f_filename'],
							"count"		=> $_priority_info['f_count'],
							"action"  	=> 'play'
					)
				)
			);
			
			return json_encode($extDta, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
		}

		/**
		 * 음원 폴더 재생 실행정보 반환
		 * 전달받은 우선순위 정보로 음원 폴더 재생 동작정보 생성 후 반환(종료와 생성)
		 * @param $_priority_info : 변환된 우선순위 정보
		 * @return format( "0" : 종료 json, "1" : 실행 json )
		 */
		function create_running_broad_folder_data($_priority_info) {
			if($_priority_info["f_type"] != "broad_folder") {
				return "";
			}
			$extData = array(
				"0"    => array(
					"type" => "post",
					"uri" => 'http://127.0.0.1/api/source_file/setPlayInfo',
					"data" => array(
							"action"  	=> 'stop'
					)
				),
				"1"    => array(
					"type" => "post",
					"uri" => 'http://127.0.0.1/api/source_file/setPlayInfo',
					"data" => array(
							"is_dir"	=> true,
							"action"  	=> 'play'
					)
				)
			);
			
			return json_encode($extData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
		}

		/**
		 * 접점출력 실행 정보 반환
		 * 전달받은 우선순위 정보로 접점출력 동작정보 생성 후 반환(종료와 생성)
		 * @param $_priority_info : 변환된 우선순위 정보
		 * @return format( "0" : 종료 json, "1" : 실행 json )
		 */
		function get_running_contact_string($_priority_info_array) {
			$tmp_running_contact_string = "";
			foreach($_priority_info_array as $priority_info) {
				if($priority_info["f_type"] != "contact") {
					continue;
				}
				$tmp_running_contact_string = $this->create_running_contact_data($priority_info, $tmp_running_contact_string);
			}
			
			// not exist contact priority
			if($tmp_running_contact_string == "") {
				$tmp_running_contact_string = array(
					"0" => array(
						"type" => "post",
						"uri" => 'http://127.0.0.1/api/output/setOutputList',
						"data" => array(
								"CP"	=> array_fill(0, $this->out_max_cnt["CP"]["out"], "0"),
								"RM"	=> array_fill(0, $this->out_max_cnt["RM"]["out"], "0")
						)
					),
					"1" => array(
						"type" => "post",
						"uri" => 'http://127.0.0.1/api/output/setOutputList',
						"data" => array(
								"CP"	=> array_fill(0, $this->out_max_cnt["CP"]["out"], "-1"),
								"RM"	=> array_fill(0, $this->out_max_cnt["RM"]["out"], "-1")
						)
					)
				);
				$tmp_running_contact_string = json_encode($tmp_running_contact_string, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
			}
			return $tmp_running_contact_string;
		}

		/**
		 * 우선순위 동작정보를 동작 가능한 데이터로 변환해주는 함수
		 * 실행가능한 우선순위 배열을 동작 가능한 데이터로 변환하여 반환함
		 * @param $_priority_info_array : 동작 실행가능한 정보 배열
		 * @return 동작(action)과 접점(contact)로 구성된 배열
		 */
		function get_running_priority_string($_priority_info_array) {
			$tmp_running_string = "";
			$tmp_running_contact_string = "";
			foreach($_priority_info_array as $priority_info) {
				if($priority_info["f_status"] != "1") {
					continue;
				}
				if($priority_info["f_type"] == "audio_stream" && $tmp_running_string == "") {
					$tmp_running_string = $this->create_running_audio_stream_data($priority_info);
				} else if($priority_info["f_type"] == "broad_file" && $tmp_running_string == "") {
					$tmp_running_string = $this->create_running_broad_file_data($priority_info);
				} else if($priority_info["f_type"] == "broad_folder" && $tmp_running_string == "") {
					$tmp_running_string = $this->create_running_broad_folder_data($priority_info);
				}
				
				$tmp_running_contact_string = $this->create_running_contact_data($priority_info, $tmp_running_contact_string);
			}
			// change CP or RM value ( -1 => 0 )
			if($tmp_running_contact_string != "") {
				$tmp_running_contact_json = json_decode($tmp_running_contact_string, true);
				$tmp_cp_string = implode(",", $tmp_running_contact_json["1"]["data"]["CP"]);
				$tmp_rm_string = implode(",", $tmp_running_contact_json["1"]["data"]["RM"]);
				
				$tmp_cp_max_arr = array_fill(0, $this->out_max_cnt["CP"]["out"], "0");
				$tmp_cp_arr = explode(",", str_replace("-1", "0", $tmp_cp_string));
				$tmp_cp_arr = $tmp_cp_arr + $tmp_cp_max_arr;
				
				$tmp_rm_max_arr = array_fill(0, $this->out_max_cnt["RM"]["out"], "0");
				$tmp_rm_arr = explode(",", str_replace("-1", "0", $tmp_rm_string));
				$tmp_rm_arr = $tmp_rm_arr + $tmp_rm_max_arr;
				
				$tmp_running_contact_json["1"]["data"]["CP"] = $tmp_cp_arr;
				$tmp_running_contact_json["1"]["data"]["RM"] = $tmp_rm_arr;
				$tmp_running_contact_string = json_encode($tmp_running_contact_json, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
			}
			return array("action" => $tmp_running_string, "contact" => $tmp_running_contact_string);
		}

		/**
		 * 동작 종료 함수
		 * 오디오 동작 종료, 음원 파일(폴더) 재생 종료 함수
		 */
		function off_normal_action() {
			// audio_stream off
			$this->run_get_data("http://127.0.0.1/api/audio/client/stop");
			// file off
			$post_data = array(
				"action"  	=> 'stop'
			);
			$this->run_post_data("http://127.0.0.1/api/source_file/setPlayInfo", json_encode($post_data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
		}
		
		/**
		 * 동작 + 접점 출력 기능 동작 함수
		 * 실행목록에서 실행값을 반환받아 실행하는 함수
		 */
		function recovery_action_output() {
			$tmp_post_running_info_string = $this->get_running_priority_string($this->priority_info_array);
			
			// running operation
			if(count($tmp_post_running_info_string) > 0) {
				foreach($tmp_post_running_info_string as $running_info) {
					if($running_info == "") {
						continue;
					}
					
					$running_info_json = json_decode($running_info, true);
					if($running_info_json["1"]["type"] == "post") {
						$this->run_post_data($running_info_json["1"]["uri"], json_encode($running_info_json["1"]["data"], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
					} else if($running_info_json["1"]["type"] == "websocket") {
						$this->run_socket_data($running_info_json["1"]["uri"], $this->ws_cmd_id, json_encode($running_info_json["1"]["data"], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
					} else if($running_info_json["1"]["type"] == "get") {
						$this->run_get_data($running_info_json["1"]["uri"]);
					}
				}
			}
		}

		/**
		 * 접점 출력 동작 함수
		 * 정보 갱신 없이 접점 출력 동작만 하는 함수
		 */
		function recovery_output() {
			$tmp_priority_info_array = $this->priority_info_array;
			$tmp_output_string = "";
			foreach($tmp_priority_info_array as $priority_info) {
				if($priority_info["f_type"] == "contact") {
					$tmp_output_string = $this->create_running_contact_data($priority_info, $tmp_output_string);
				}
			}
			if($tmp_output_string != "") {
				$tmp_output_json = json_decode($tmp_output_string, true);
				$this->run_post_data($tmp_output_json["1"]["uri"], json_encode($tmp_output_json["1"]["data"], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
			}
		}

		//// normal priority end.
		
		//// emergerncy priority
		/**
		 * 기존 실행목록에 새로운 우선순위 정보를 더하여 반환
		 * 종료 우선순위 정보가 올 경우 제거
		 * 실행 우선순위 정보가 올 경우 추가
		 * @param $_priority_info_array : 변환된 새로운 우선순위 정보 배열
		 * @return 기존 실행 우선순위 배열 + 새로운 우선순위 정보 배열
		 */
		function apply_em_priority_info_array($_priority_info_array) {
			$tmp_priority_info_array = $this->old_priority_info_array;
			
			// 들어온 우선순위 정보가 종료 정보일 때
			//   해당 정보가 old 우선순위 정보에 있을 때
			foreach($_priority_info_array as $priority_info) {
				if($priority_info["f_status"] == "0") {
					$tmp_priority_key = $this->get_priority_info_key($tmp_priority_info_array, $priority_info);
					if($tmp_priority_key > -1) {
						unset($tmp_priority_info_array[$tmp_priority_key]);
					}
				} else if($priority_info["f_status"] == "1") {
					array_push($tmp_priority_info_array, $priority_info);
				}
			}
			
			$tmp_top_priority_info = null;
			$tmp_top_priority = null;
			foreach($tmp_priority_info_array as $priority_key => $priority_info) {
				$priority_info["f_status"] = "0";
				$tmp_priority_info_array[$priority_key] = $priority_info;
				
				if($tmp_top_priority == null) {
					$tmp_top_priority = $priority_info["f_priority"];
					$tmp_top_priority_info = $priority_info;
					continue;
				}
				if($tmp_top_priority * 1 > $priority_info["f_priority"]) {
					$tmp_top_priority = $priority_info["f_priority"];
				}
			}
			
			// 높은 우선순위가 시나리오 비상방송일 경우
			//   중복방송 허용체크값을 확인하여 허용할 경우에만 여러개의 우선순위의 status가 1이 된다.
			// 높은 우선순위가 최우선 라이브 비상방송일 경우와 시나리오 비상방송이지만 중복방송 허용이 아닐 경우
			//   해당 우선순위만 status를 1로 변경
			$is_multi_broad = false;
			if($tmp_top_priority_info["f_type"] == "emergency_play_file" && $tmp_top_priority_info["f_multi_broad"] == "true") {
				$is_multi_broad = true;
			}
			
			foreach($tmp_priority_info_array as $priority_key => $priority_info) {
				if($priority_info["f_priority"] == $tmp_top_priority) {
					$priority_info["f_status"]                  = "1";
					$tmp_priority_info_array[$priority_key]     = $priority_info;
					continue;
				}
				if($is_multi_broad) {
					if($priority_info["f_type"] == "emergency_play_file" && $priority_info["f_multi_broad"] == "true") {
						$priority_info["f_status"]              = "1";
						$tmp_priority_info_array[$priority_key] = $priority_info;
					}
				}
			}
			return $tmp_priority_info_array;
		}

		/**
		 * API에서 보낸 우선순위 정보를 db 우선순위 정보로 변환
		 * @param $_priority_info : api에서 보낸 우선순위 정보 json
		 * @param $_mode : 동작 type 정의 (button, event, em)
		 * @return 우선순위 정보의 배열형태
		 */
		function convert_em_priority_data($_priority_info, $_mode) {
			$tmp_priority_info                      = array();
			$tmp_priority_info["f_mode"]			= $_mode;
			$tmp_priority_info["f_priority"]        = $_priority_info["priority"];
			$tmp_priority_info["f_channel"]         = null;
			$tmp_priority_info["f_type"]            = $_priority_info["type"];
			$tmp_priority_info["f_status"]          = $_priority_info["isRun"];
			$tmp_priority_info["f_delayMs"]         = null;
			$tmp_priority_info["f_cp"]              = isset($_priority_info["actions"][1]["data"]["CP"]) ? $_priority_info["actions"][1]["data"]["CP"] : "";
			$tmp_priority_info["f_rm"]              = isset($_priority_info["actions"][1]["data"]["RM"]) ? $_priority_info["actions"][1]["data"]["RM"] : "";
			if($_priority_info["type"] == "emergency_audio_stream") {
				$tmp_priority_info["f_ip"]          = $_priority_info["actions"][0]["data"]["em_info"]["network_info"]["unicast_primary_ip_addr"];
				$tmp_priority_info["f_svr_channel"] = "1";
				$tmp_priority_info["f_port"]        = $_priority_info["actions"][0]["data"]["em_info"]["network_info"]["unicast_primary_port"];
				$tmp_priority_info["f_cast_type"]   = $_priority_info["actions"][0]["data"]["em_info"]["network_info"]["network_cast_type"];
				$tmp_priority_info["f_count"]       = $_priority_info["actions"][0]["data"]["em_info"]["em_play_record_info"]["play_count"];
				$tmp_priority_info["f_min_rec"]     = $_priority_info["actions"][0]["data"]["em_info"]["em_play_record_info"]["min_record_time"];
				$tmp_priority_info["f_max_rec"]     = $_priority_info["actions"][0]["data"]["em_info"]["em_play_record_info"]["max_record_time"];
			} else if($_priority_info["type"] == "emergency_play_file") {
				$tmp_priority_info["f_count"]       = $_priority_info["actions"][0]["data"]["em_info"]["em_play_file_info"]["play_info_list"][0]["count"];
				$tmp_priority_info["f_filename"]    = implode(",", $_priority_info["actions"][0]["data"]["em_info"]["em_play_file_info"]["play_info_list"][0]["list"]);;
				$tmp_priority_info["f_multi_broad"] = $_priority_info["actions"][0]["data"]["multi_broad"] ? "true" : "false";
			}
			
			return $tmp_priority_info;
		}

		/**
		 * 최우선 라이브 비상방송 동작정보 반환
		 * @param $_priority_info : 변환된 우선순위 정보
		 * @return $_output_string : format( "0" : 종료 json, "1" : 실행 json )
		 */
		function create_running_em_audio_stream_data($_priority_info) {
			if($_priority_info["f_type"] != "emergency_audio_stream") {
				return "";
			}
			$extData = array(
				"0"    => array(
					"type" => "websocket",
					"uri"  => "audio_client",
					"data" => array(
							"emergency" => false
					)
				),
				"1"    => array(
					"type" => "websocket",
					"uri"  => 'audio_client',
					"data" => array(
							"emergency"	=> true,
							"mode"		=> "record",
							"em_info"	=> array(
									"network_info"			=> array(
											"network_cast_type"			=> $_priority_info["f_cast_type"],
											"unicast_primary_ip_addr"	=> $_priority_info["f_ip"],
											"unicast_primary_port"		=> $_priority_info["f_port"]
									),
									"em_play_record_info"	=> array(
											"play_count"		=> $_priority_info["f_count"],
											"min_record_time"	=> $_priority_info["f_min_rec"],
											"max_record_time"	=> $_priority_info["f_max_rec"]
									)
							)
					)
				)
					
			);
			return json_encode($extData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
		}

		/**
		 * 시나리오 비상방송 동작정보 반환
		 * 중복방송 허용 대상이 있을 경우 방송 리스트에 추가
		 * @param $_priority_info : 변환된 우선순위 정보
		 * @return $_output_string : format( "0" : 종료 json, "1" : 실행 json )
		 */
		function create_running_em_play_file_data($_priority_info, $_em_play_file_string) {
			if($_priority_info["f_type"] != "emergency_play_file") {
				return "";
			}
			if($_em_play_file_string != "") {
				$tmp_em_play_file_json = json_decode($_em_play_file_string, true);
				$play_list = $tmp_em_play_file_json["1"]["data"]["em_info"]["em_play_file_info"]["play_info_list"];
				array_push($play_list, array("count" => (int)$_priority_info["f_count"], "list" => explode(",", $_priority_info["f_filename"])));
				
				$tmp_em_play_file_json["1"]["data"]["em_info"]["em_play_file_info"]["play_info_list"] = $play_list;
				$tmp_em_play_file_json["1"]["data"]["em_info"]["em_play_file_info"]["play_info_count"] = count($play_list);
				return json_encode($tmp_em_play_file_json, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
			} else{
				$play_list = array();
				array_push($play_list, array("count" => (int)$_priority_info["f_count"], "list" => explode(",", $_priority_info["f_filename"])));
				$extData = array(
					"0"    => array(
						"type" => "websocket",
						"uri"  => "audio_client",
						"data" => array(
								"emergency" => false
						)
					),
					"1"    => array(
						"type" => "websocket",
						"uri" => 'audio_client',
						"data" => array(
								"emergency"	=> true,
								"mode"		=> "file",
								"em_info"	=> array(
										"em_play_file_info"	=> array(
												"play_info_count"	=> count($play_list),
												"play_info_list"	=> $play_list
										)
								)
						)
					)
						
				);
				return json_encode($extData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
			}
		}

		/**
		 * 우선순위 동작정보를 동작 가능한 데이터로 변환해주는 함수
		 * 실행가능한 우선순위 배열을 동작 가능한 데이터로 변환하여 반환함
		 * @param $_priority_info_array : 동작 실행가능한 정보 배열
		 * @return 동작(action)과 접점(contact)로 구성된 배열
		 */
		function get_running_em_priority_string($_priority_info_array) {
			$tmp_running_string = "";
			$tmp_running_contact_string = "";
			foreach($_priority_info_array as $priority_info) {
				if($priority_info["f_status"] != "1") {
					continue;
				}
				if($priority_info["f_type"] == "emergency_audio_stream" && $tmp_running_string == "") {
					$tmp_running_string = $this->create_running_em_audio_stream_data($priority_info);
				} else if($priority_info["f_type"] == "emergency_play_file") {
					if($tmp_running_string == "") {
						$tmp_running_string = $this->create_running_em_play_file_data($priority_info, $tmp_running_string);
					} else {
						if($priority_info["f_multi_broad"] == "true") {
							$tmp_running_string = $this->create_running_em_play_file_data($priority_info, $tmp_running_string);
						}
					}
				}
				
				$tmp_running_contact_string = $this->create_running_contact_data($priority_info, $tmp_running_contact_string);
			}
			// change CP or RM value ( -1 => 0 )
			if($tmp_running_contact_string != "") {
				$tmp_running_contact_json = json_decode($tmp_running_contact_string, true);
				$tmp_cp_string = implode(",", $tmp_running_contact_json["1"]["data"]["CP"]);
				$tmp_rm_string = implode(",", $tmp_running_contact_json["1"]["data"]["RM"]);
				$tmp_running_contact_json["1"]["data"]["CP"] = explode(",", str_replace("-1", "0", $tmp_cp_string));
				$tmp_running_contact_json["1"]["data"]["RM"] = explode(",", str_replace("-1", "0", $tmp_rm_string));
				$tmp_running_contact_string = json_encode($tmp_running_contact_json, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
			}
			return array("action" => $tmp_running_string, "contact" => $tmp_running_contact_string);
		}

		/**
		 * 비상동작 상태 반환
		 */
		function is_running() {
			return $this->is_running;
		}
		
		/**
		 * em 동작 종료 함수
		 * 최우선 라이브 및 시나리오 비상방송 종료
		 */
		function off_em_action() {
			// audio_stream off(emergency)
			$ws_handler = new WebsocketHandler("127.0.0.1", "audio_client");
			$postData   = array("emergency" => false);
			$data = json_encode($postData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
			$ws_handler->send($this->ws_cmd_id, $data);
			usleep(100000);
			$ws_handler->term();
		}

		//// emergerncy priority end.
	}
?>
