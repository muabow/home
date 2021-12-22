<?php
	namespace System_mng_svr_info\Func {
		use System_mng_svr_info;
		use SQLite3;

		class SystemFunc {
			private $db;
			private $confPath;

			function __construct() {
				$this->confPath = $_SERVER['DOCUMENT_ROOT'] . "/../conf/config-manager-server.db";
				$this->db       = new SQLite3($this->confPath);

				$this->db->exec('PRAGMA journal_mode = wal;');

				return ;
			}

			function getSvrList() {
				$query   = "select * from mng_svr_info;";
				$results = $this->db->query($query);

				$svrList = "";
				$idx     = 0;
				while( $row = $results->fetchArray(1) ) {
					$checked = "";
					if( $row["mng_svr_used"] == 1 ) $checked = "checked";
					$disabled = ($row["mng_svr_enabled"] == "0" || $row["mng_svr_enabled"] == "") ? 'disabled' : '';
					$svrList .= '
							<div class="divTableRow">
									<div class="divTableCell_left">
										<input type="radio" name="radio_svr_select" value="' . $idx . '" ' . $checked . ' ' . $disabled .  ' />
									</div>
									<div class="divTableCell" id="div_svr_id_' . $idx . '">
									' . $row["mng_svr_id"] . '
									</div>
									<div class="divTableCell" id="div_svr_ip_' . $idx . '">
									' . $row["mng_svr_ip"] . '
									</div>
									<div class="divTableCell">
									' . $row["mng_svr_port"] . '
									</div>
									<div class="divTableCell">
									' . $row["mng_svr_name"] . '
									</div>
									<div class="divTableCell">
									' . $row["mng_svr_version"] . '
									</div>
									<div class="divTableCell" id="div_svr_date_' . $idx . '">
									' . date("Y-m-d h:i:s", $row["mng_svr_date"]) . '
									</div>
									<div class="divTableCell divTableCell_checkbox">
										<input type="checkbox" id="checkbox_svr_' . $idx . '" />
									</div>
								</div>
						';

					$idx++;
				}
				$this->db->close();

				return $svrList;
			}

			function setSvrList($_svrId) {
				$query = "update mng_svr_info set mng_svr_used = '0';";
				$results = $this->db->query($query);

				$query = "update mng_svr_info set mng_svr_used = '1'
						  where mng_svr_id = '{$_svrId}';";
				$results = $this->db->query($query);

				$this->db->close();

				echo $query;

				return ;
			}

			function removeSvrList($_svrId) {
				$query = "delete from mng_svr_info
						  where mng_svr_id in ({$_svrId});";
				$results = $this->db->query($query);

				$query = "select mng_svr_used from mng_svr_info where mng_svr_used = '1';";
				$results = $this->db->query($query);

				$cnt = 0;
				while( $row = $results->fetchArray(1) ) {
					$cnt++;
				}

				if( $cnt == 0 ) {
					$query = "update mng_svr_info set mng_svr_used = '1'
							  where mng_svr_id in (select mng_svr_id from mng_svr_info where mng_svr_enabled != '0' order by mng_svr_date desc limit 1);";
					$results = $this->db->query($query);
				}
				$this->db->close();

				echo $query;
				return ;
			}
		}

		include_once "common_script_etc.php";
	}
?>