<?php
	namespace Common\Func {
		use Common;
		use SQLite3;

		class CommonFunc {
			/* variables */
			private $envData, $env_langSet;
			private $header_info_hostname, $header_info_location;
			private $main_menu, $main_contents;
			private $env_language_name, $env_language_list;
			private $env_homepage_url;
			private $versionAuth;
			private $arrMenuList;

			private $str_admin_user	= "admin";
			private $str_super_user	= "";
			private $arr_auth_list	= "";


			/* constructor */
			function __construct() {
				$load_envData  				= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/" . Common\Def\PATH_SYS_ENV_JSON);
				$envData   					= json_decode($load_envData);
				$env_langSet  	   			= $envData->info->language_set;

				// 버전 정보 열람 권한
				$this->versionAuth			= $envData->device->version_auth; // [setup] 권한 이상

				// 언어팩 설정
				$this->env_language_name 	= $envData->language_pack->$env_langSet->name;
				include $_SERVER['DOCUMENT_ROOT'] . "/" . $envData->language_pack->$env_langSet->path;

				// network 정보
				$network_envData  			= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/modules/network_setup/conf/network_stat.json");
				$networkData				= json_decode($network_envData);

				$this->header_info_hostname = $networkData->hostname;
				$this->header_info_location = $networkData->location;

				$this->envData				= $envData;
				$this->env_langSet			= $env_langSet;
				$this->main_contents 		= $envData->info->main_set;

				// 언어팩 리스트 생성
				$this->env_language_list	= $this->makeLanguageList();

				// 언어팩에 따른 홈페이지 링크 변경
				$this->env_homepage_url		= $this->makeHomepageLink();

				// 메인 좌측 메뉴 생성
				$this->main_menu			= $this->makeModuleMenu();

				// 슈퍼 유저 및 관리자 계정 정보 처리
				$this->arr_auth_list = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/../key_data/user_auth_list.json"));

				$num_low_level = 0;
				$str_low_user  = "";

				$is_first_user = false;
				foreach( $this->arr_auth_list as $user => $level ) {
					if( !$is_first_user ) {
						$is_first_user = true;

						$str_low_user  = $user;
						$num_low_level = $level;

						continue;
					}

					if( $num_low_level > $level ) {
						$str_low_user  = $user;
						$num_low_level = $level;
					}

				}

				$this->str_super_user = $str_low_user;

				return ;
			}

			/* getters */
			function getEnvData()				{	return $this->envData;							}
			function getEnvCompanyName()		{	return $this->envData->company->name;			}
			function getEnvCompanyLogo()		{	return $this->envData->company->ci_logo;		}
			function getEnvCompanyLogoMobile()	{	return $this->envData->company->ci_logo_mobile;	}
			function getEnvDeviceName()			{	return $this->envData->device->name;			}
			function getEnvInfoLanguageName() 	{	return $this->env_language_name;				}
			function getEnvInfoLanguageList()	{	return $this->env_language_list;				}
			function getEnvInfoMadeYear() 		{	return $this->envData->info->made_year;			}
			function getEnvInfoHomepageURL()	{	return $this->env_homepage_url;					}
			function getHeaderInfoHostName()	{	return $this->header_info_hostname;				}
			function getHeaderInfoLocation()	{	return $this->header_info_location;				}
			function getMainMenu()				{	return $this->main_menu;						}

			function getEnvDeviceVersion() {
				if( !isset($_SESSION['username']) ) return "-";

				if( !$this->checkUserAuth($_SESSION['username'], $this->versionAuth) ) return "-";
				return $this->envData->device->version;
			}

			function getMainContents() {
				$mainSet     = basename($this->main_contents);
				$contentName = substr($mainSet, 0, strrpos($mainSet, "."));

				if( !$this->getModuleListStat($contentName) ) {
					if( $contentName != "help" ) {
						$this->setMainContents();
					}
				}

				if( !file_exists($this->main_contents) ) {
					$this->setMainContents();
					$page = $_SERVER['PHP_SELF'];
					echo '<meta http-equiv="Refresh" content="0;' . $page . '">';

					return ;
				}

				return $this->main_contents;
			}

			function getModuleAuth($_name) {
				foreach( $this->envData->module->list as $module_list ) {
					if( ($module_list->type == Common\Def\JSON_MODULE_MENU ) && ($_name == ($module_list->name . ".php")) ) {
						if( !$this->checkUserAuth($_SESSION['username'], $module_list->auth) ) {
							$this->setMainContents();
							$page = $_SERVER['PHP_SELF'];
							echo '<meta http-equiv="Refresh" content="0;' . $page . '">';

							exit ;
						}
					}
				}

				return ;
			}

			// 인증, 모바일 등등 Module관련된 처리 function
			function procModuleStatus($_moduleName) {
				// auth 처리
				$this->getModuleAuth($_moduleName);

				// mobile 처리
				$arrModName = explode(".", $_moduleName);
				$moduleName = $arrModName[0];
				$modulePath = $_SERVER["DOCUMENT_ROOT"] . "/modules/" . $moduleName . "/html/";
				$mobilePath = $modulePath . $moduleName . "_m.php";

				if( preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $_SERVER['HTTP_USER_AGENT'])
					 || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i', substr($_SERVER['HTTP_USER_AGENT'], 0, 4)) ) {
					if( !isset($_SESSION["pc_view"]) ) {

						if( file_exists($mobilePath) ) {
							include_once $mobilePath;

							return true;
						}
					}
				}

				return false;
			}

			function getStandSupportAPIStat($_name) {
				$stat = false;

				if( isset($this->envData->mode) ) {
					if( $this->envData->mode->set == "STAND ALONE" && $this->envData->mode->stand_support_api == false ) {
						if( $_name == "api" ) {
							$stat = true;
						}
					}
				}

				return $stat;
			}

			/* Setters */
			function setEnvInfoLanguage($_langSet) {
				$this->envData->info->language_set = $_langSet;
				file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/" . Common\Def\PATH_SYS_ENV_JSON, json_encode($this->envData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

				echo Common\Def\RESPONSE_SUCCESS;
				return ;
			}

			function setMainContents($_mainContents) {
				if( !$_mainContents ) {
					$_mainContents = "main_contents.php";
				}

				$this->envData->info->main_set = $_mainContents;
				file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/" . Common\Def\PATH_SYS_ENV_JSON, json_encode($this->envData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

				return Common\Def\RESPONSE_SUCCESS;
			}

			/* functions */
			// Main : 메뉴 화면 구성
			function makeModuleMenu() {
				$envData	  = $this->envData;
				$mainMenu	  = "";
				$categoryName = "";

				if( !isset($_SESSION['username']) ) return ;
				
				$mainSet     = basename($this->main_contents);
				$contentName = substr($mainSet, 0, strrpos($mainSet, "."));

				foreach( $envData->module->list as $module_list ) {
					$set_menu_focus = "";
					$set_span_focus = "";
					
					$env_langSet  = $envData->info->language_set;
					switch( $module_list->type ) {
						case Common\Def\JSON_MODULE_CATEGORY :
							$categoryName = $module_list->name;

							if( !$this->checkUserAuth($_SESSION['username'], $module_list->auth) ) break;

							// STAND ALONE 모드 중 API 를 제공하지 않는 경우 출력 해제
							if( $this->getStandSupportAPIStat($module_list->name) ) break;

							// category명 호출 (화면 출력)
							$module_list_name = strtoupper($module_list->name);
							$category_name	  = constant("Common\Lang\STR_MENU_SETUP_" . $module_list_name);

							if( is_null($category_name) ) {
								$category_name = $module_list->type . " / " . $module_list->name;
							}

							// menu 명세
							$mainMenu .= '<div class="div_main_menu_category">
												<span class="span_main_menu_category"> ' . $category_name . ' </span>
										   </div>' . "\n";
						break;

						case Common\Def\JSON_MODULE_MENU :
							if( !$this->checkUserAuth($_SESSION['username'], $module_list->auth) ) break;

							// STAND ALONE 모드 중 API 를 제공하지 않는 경우 출력 해제
							if( $this->getStandSupportAPIStat($categoryName) ) break;

							// mode를 사용하고 mode가 설정된 모듈만 출력하도록 제한
							if( isset($envData->mode->set) && isset($module_list->mode) ) {
								if( $envData->mode->set != $module_list->mode ) break;
							}

							$module_path = $module_list->path;

							// main_contents 파일 호출 (link)
							$module_contents = $module_path . "/html/" . $module_list->name . '.php';
							if( !file_exists($module_contents) ) {
								break;
							}

							// version 비교
							$module_version = $module_path . "/conf/version.json";
							if( !file_exists($module_version) ) {
								break;
							}

							$verEnv = json_decode(file_get_contents($module_version));
							if( $module_list->version != $verEnv->version ) {
								break;
							}

							// 설정된 언어에 맞춰 해당 언어팩 호출
							if ( !file_exists($module_path . "/html/" . $envData->language_pack->$env_langSet->path) ) {
								$env_langSet = "eng";
							}
							$module_path_lang_pack = $module_path . "/html/" . $envData->language_pack->$env_langSet->path;
							include $module_path_lang_pack;

							// menu명 호출 (화면 출력)
							$module_list_name = ucfirst($module_list->name);
							$module_name	  = constant($module_list_name . "\Lang\STR_MENU_NAME");

							if(isset($module_list->display) && ("disabled" == $module_list->display)) {
								break;
							}

							// menu 명세
							if( $module_list->name == $contentName ) {
								$set_menu_focus = "div_main_menu_focus";
								$set_span_focus = "span_main_menu_focus";
							}
							$mainMenu .= '<div class="div_main_menu_sub ' . $set_menu_focus . '" id="div_main_menu_id_' . $module_list->name . '" name="' . $module_contents .'">
												<span class="span_main_menu_sub ' . $set_span_focus . '"> ' . $module_name . ' </span>
										   </div>' . "\n";
						break;

						case Common\Def\JSON_MODULE_VIEW :
							if( !$this->checkUserAuth($_SESSION['username'], $module_list->auth) ) continue;

							$module_path		   = $module_list->path;

							// main_contents 파일 호출 (link)
							$module_contents = $module_path . "/html/" . $module_list->name . '.php';
							if( !file_exists($module_contents) ) {
								break;
							}

							// 설정된 언어에 맞춰 해당 언어팩 호출
							if ( !file_exists($module_path . "/html/" . $envData->language_pack->$env_langSet->path) ) {
								$env_langSet = "eng";
							}
							$module_path_lang_pack = $module_path . "/html/" . $envData->language_pack->$env_langSet->path;
							include $module_path_lang_pack;

							$module_path		   = $module_list->path;
							include $module_path . "/html/" . $module_list->name . '.php';

						break;

					}

					next($envData->module);
				}

				return $mainMenu;
			}

			function getModuleListStat($_moduleName) {
				$envData	   = $this->envData;
				$env_langSet   = $this->env_langSet;
				$categoryName  = "";
				$arrModuleList = array();

				foreach( $envData->module->list as $module_list ) {
					switch( $module_list->type ) {
						case Common\Def\JSON_MODULE_CATEGORY :
							$categoryName = $module_list->name;

							if( !$this->checkUserAuth($_SESSION['username'], $module_list->auth) ) break;

							// STAND ALONE 모드 중 API 를 제공하지 않는 경우 출력 해제
							if( $this->getStandSupportAPIStat($module_list->name) ) break;

							// category명 호출 (화면 출력)
							$module_list_name = strtoupper($module_list->name);
							$category_name	  = constant("Common\Lang\STR_MENU_SETUP_" . $module_list_name);

							if( is_null($category_name) ) {
								$category_name = $module_list->type . " / " . $module_list->name;
							}
							$arrModuleList[] = $module_list->name;

						break;

						case Common\Def\JSON_MODULE_MENU :
							if( !$this->checkUserAuth($_SESSION['username'], $module_list->auth) ) break;

							// STAND ALONE 모드 중 API 를 제공하지 않는 경우 출력 해제
							if( $this->getStandSupportAPIStat($categoryName) ) break;

							// mode를 사용하고 mode가 설정된 모듈만 출력하도록 제한
							if( isset($envData->mode->set) && isset($module_list->mode) ) {
								if( $envData->mode->set != $module_list->mode ) break;
							}

							$module_path = $module_list->path;

							// main_contents 파일 호출 (link)
							$module_contents = $module_path . "/html/" . $module_list->name . '.php';
							if( !file_exists($module_contents) ) {
								break;
							}

							// version 비교
							$module_version = $module_path . "/conf/version.json";
							if( !file_exists($module_version) ) {
								break;
							}

							$verEnv = json_decode(file_get_contents($module_version));
							if( $module_list->version != $verEnv->version ) {
								break;
							}

							$arrModuleList[] = $module_list->name;

						break;

						case Common\Def\JSON_MODULE_VIEW :
							if( !$this->checkUserAuth($_SESSION['username'], $module_list->auth) ) continue;

							$module_path		   = $module_list->path;

							// main_contents 파일 호출 (link)
							$module_contents = $module_path . "/html/" . $module_list->name . '.php';
							if( !file_exists($module_contents) ) {
								break;
							}

							$arrModuleList[] = $module_list->name;

						break;

					}

					next($envData->module);
				}

				return in_array($_moduleName, $arrModuleList);
			}

			function checkUserAuth($_userName, $_auth) {
				$envAuth  = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/user_auth_list.json");
				$userList = json_decode($envAuth, true);

				if( !array_key_exists($_userName, $userList) ) return false;

				if( $userList[$_userName] <= $userList[$_auth] ) {
					return true;

				} else {
					return false;
				}
			}

			// Footer : 언어팩 목록 작성
			function makeLanguageList() {
				$envData = $this->envData;

				$languageList = "";
				$envLangCnt   = count((array)$envData->language_pack);
				$envCnt	   = 0;

				foreach( $envData->language_pack as $lang_type => $element ) {
					$languageList .= '<a id="a_language_' . $lang_type . '" class="a_footer_language_list">' . $element->name . '</a>';

					if( $envLangCnt > $envCnt + 1 ) {
						$languageList .= '&nbsp;|&nbsp;';
						$envCnt++;
					}
					$languageList .= "\n";

				}
				return $languageList;
			}

			// Footer : 언어팩에 따른 홈페이지 링크 변경
			function makeHomepageLink() {
				$envData	 = $this->envData;
				$env_langSet = $this->env_langSet;

				return $envData->language_pack->$env_langSet->homepage;
			}

			function login($_username, $_password, $_is_check) {
				$envAuth  = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/user_auth_list.json");
				$userList = json_decode($envAuth, true);

				if( !array_key_exists($_username, $userList) ) return false;
				if( !($pubKey = trim($this->getPubKey($_username))) ) return false;

				$rc = hash('sha256', $_username . $pubKey . $_password, false);

				$passHash = $_SERVER["DOCUMENT_ROOT"]. "/../key_data/" . $_username . "/passhash";
				if( !file_exists($passHash) ) return false;

				$passhash = trim(file_get_contents($passHash));

				$path_try_count = $_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/" . $_username . "/try_count";

				if( $passhash == $rc ) {
					session_start();

					$_SESSION["username"] = $_username;
					$_SESSION['timeout']  = time();

					if( !$this->is_admin_auth($_username) ) {
						file_put_contents($path_try_count, 0);
					}

					if( $_is_check ) {
						$keepIp = $_SERVER['REMOTE_ADDR'];
						if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
							$keepIp = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
						}

						$keepIpList = array();
						$keepIpList[$keepIp] = $_username;

						if( file_exists("/opt/interm/key_data/keep_session_list.json") ) {
							$getData = file_get_contents("/opt/interm/key_data/keep_session_list.json");
							$addKeepIpList = json_decode($getData);

							if( !empty($addKeepIpList) ) {
								foreach ($addKeepIpList as $keepIp => $keepUsername) {
									$keepIpList[$keepIp] = $keepUsername;
								}
							}
						}

						file_put_contents("/opt/interm/key_data/keep_session_list.json", json_encode($keepIpList, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
					}

					return true;
				}

				if( !$this->is_admin_auth($_username) ) {
					$try_cnt = file_get_contents($path_try_count);
					if( $try_cnt < Common\Def\NUM_LOGIN_TRY_COUNT ) {
						$try_cnt = $try_cnt + 1;
						file_put_contents($path_try_count, $try_cnt);
					}
				}
				return false;
			}

			function checkLogin($_username, $_password) {
				$envAuth  = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/user_auth_list.json");
				$userList = json_decode($envAuth, true);

				if( !array_key_exists($_username, $userList) ) return false;
				if( !($pubKey = trim($this->getPubKey($_username))) ) return false;

				$rc = hash('sha256', $_username . $pubKey . $_password, false);

				$passHash = $_SERVER["DOCUMENT_ROOT"]. "/../key_data/" . $_username . "/passhash";
				if( !file_exists($passHash) ) return false;

				$passhash = trim(file_get_contents($passHash));

				if( $passhash == $rc ) {

					return true;
				}

				return false;
			}

			function changePassHash($_username, $_password) {
				if( !($pubKey = trim($this->getPubKey($_username))) ) return false;

				$rc = hash('sha256', $_username . $pubKey . $_password, false);

				$passHash = $_SERVER["DOCUMENT_ROOT"]. "/../key_data/" . $_username . "/passhash";
				return file_put_contents($passHash, $rc);
			}

			function makePassHash($_username, $_password) {
				if( !($pubKey = trim($this->getPubKey($_username))) ) return false;

				$rc = hash('sha256', $_username . $pubKey . $_password, false);

				$passHash = $_SERVER["DOCUMENT_ROOT"]. "/../key_data/" . $_username . "/passhash";
				if( !file_exists($passHash) ) return false;

				$passhash = trim(file_get_contents($passHash));

				if( $passhash == $rc ) {
					session_start();

					$_SESSION["username"] = $_username;
					$_SESSION['timeout']  = time();

					return true;
				}

				return false;
			}

			function logout() {
				session_start();
				session_destroy();

				// remove keep session
				if( file_exists("/opt/interm/key_data/keep_session_list.json") ) {
					$load_data = file_get_contents("/opt/interm/key_data/keep_session_list.json");
					$arr_keep_list = json_decode($load_data);

					// set remote ip address
					$remote_addr = $_SERVER['REMOTE_ADDR'];
					if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
						$remote_addr = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
					}

					unset($arr_keep_list->$remote_addr);
					file_put_contents("/opt/interm/key_data/keep_session_list.json", json_encode($arr_keep_list, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
				}

				return ;
			}

			function getPubKey($_username) {
				$keyPath = $_SERVER["DOCUMENT_ROOT"]. "/../key_data/" . $_username . "/public.pem";
				if( !file_exists($keyPath) ) return null;

				return file_get_contents($keyPath);
			}

			function getPriKey($_username) {
				$keyPath = $_SERVER["DOCUMENT_ROOT"].  "/../key_data/" . $_username . "/private.pem";
				if( !file_exists($keyPath) ) return null;

				return file_get_contents($keyPath);
			}

			function is_super_auth($_user) {
				if( !isset($this->arr_auth_list->$_user) ) {
					return false;
				}

				if( $this->arr_auth_list->$_user != $this->str_super_user ) {
					return false;
				}

				return true;
			}

			function is_admin_auth($_user) {
				if( !isset($this->arr_auth_list->$_user) ) {
					return false;
				}

				if( $this->arr_auth_list->{$this->str_admin_user} < $this->arr_auth_list->$_user ) {
					return false;
				}

				return true;
			}

			function is_set_main_contents() {
				$mainSet     = basename($this->main_contents);
				$contentName = substr($mainSet, 0, strrpos($mainSet, "."));

				if( $contentName == "main_contents" ) {
					return true;
				}

				return false;
			}

		} // end of CommonFunc()

		class CommonXmlFunc {
			private $env_xmls;

			/* constructor */
			function __construct($_xmlFile = null) {
				if( $_xmlFile == null ) {
					echo "<h5> " . $_SERVER['PHP_SELF'] . " : Class [CommonXmlFunc]를 사용할 수 없습니다. XML 파일 경로를 입력하세요. </h5>";
					return ;
				}

				if( file_exists($_xmlFile) ) {
					echo "<h5> " . $_SERVER['PHP_SELF'] . " : Class [CommonXmlFunc]를 사용할 수 없습니다. XML 파일 경로를 입력하세요. </h5>";
					return ;
				}

				$this->$env_xmls = simplexml_load_file($_xmlFile);
			}

			function XmlReader($_nodePath) {
				$nodeExists = $this->$env_xmls->xpath($_nodePath);

				return $nodeExists[0];
			}

			function XmlWriter($_nodePath, $_value) {
				$nodeExists = $this->$env_xmls->xpath($_nodePath);
				$nodeExists[0][0] = $_value;

				$xml -> asXML($_xmlFile);

				// reload
				$this->$env_xmls = simplexml_load_file($_xmlFile);
			}
		} // end of CommonXmlFunc()

		class CommonLogFunc {
			const TIMEZONE_TYPE		= 0;
			const TIMEZONE_GMT		= 1;
			const TIMEZONE_NAME		= 2;
			const TIMEZONE_PATH		= 3;

			//* variables */
			private $env_logPath;
			private $env_logName;
			private $env_setInfoFlag;

			/* constructor */
			function __construct($_moduleName = null) {
				// 모듈명 지정안하면 사용할 수 없음
				if( $_moduleName == null ) {
					echo "<h5> " . $_SERVER['PHP_SELF'] . " : Class [CommonLogFunc]를 사용할 수 없습니다. Module명을 입력하세요. </h5>";

					return ;
				}

				if( is_null($_moduleName) ) {
					echo "<h5> " . $_SERVER['PHP_SELF'] . " : Class [CommonLogFunc]를 사용할 수 없습니다. Module명을 입력하세요. </h5>";

					return ;
				}

				$env_pathModule = $_SERVER['DOCUMENT_ROOT'] . "/modules/" . $_moduleName;

				if( !file_exists($env_pathModule) ) {
					$env_pathModule = $_SERVER['DOCUMENT_ROOT'] . "/..";
				}

				$this->env_logPath		= $env_pathModule . "/log";
				$this->env_logName		= $_moduleName .".log";
				$this->env_setInfoFlag	= false;

				if( ($logFp = $this->openFile()) == null ) return ;
				else fclose($logFp);
			}

			/* functions */
			function packInt32Be($_idx) {
				return pack('C4', ($_idx >> 24) & 0xFF, ($_idx >> 16) & 0xFF, ($_idx >>  8) & 0xFF, ($_idx >>  0) & 0xFF);
			}

			function packInt32Le($_idx) {
			   return pack('C4', ($_idx >>  0) & 0xFF, ($_idx >>  8) & 0xFF, ($_idx >> 16) & 0xFF, ($_idx >> 24) & 0xFF);
			}

			function openFile() {
                $logFp = null;

                // 파일 용량 정상 체크 (비정상 시 삭제)
                if( file_exists($this->env_logPath . "/" . $this->env_logName) ) {
                    $fileSize  = filesize($this->env_logPath . "/" . $this->env_logName);
                    $existSize = Common\Def\SIZE_LOG_BYTE * Common\Def\SIZE_LOG_LINE + 4;

                    if( $existSize != $fileSize ) {
                        unlink($this->env_logPath . "/" . $this->env_logName);
                    }
                }

                // 파일 유무 체크 (유: 정상 진행 / 무: 파일 생성)
                if( !($logFp = fopen($this->env_logPath . "/" . $this->env_logName, "r+b")) ) {
                    // 파일 생성 가능 체크 (가능: dummy 파일 생성 / 불가능 : false)
                    if( !($logFp = fopen($this->env_logPath . "/" . $this->env_logName, "w+b")) ) {

                        return null;

                    } else {
                        if( flock($logFp, LOCK_EX) ) {
                            fseek($logFp, Common\Def\SIZE_LOG_BYTE * Common\Def\SIZE_LOG_LINE, SEEK_CUR);
                            fwrite($logFp, $this->packInt32Le(0));

                            flock($logFp, LOCK_UN);
                        }
                    }
                }

                return $logFp;
            }

			function clearLog() {
				unlink($this->env_logPath . "/" . $this->env_logName);

				if( ($logFp = $this->openFile()) == null ) return ;
				else fclose($logFp);

				return ;
			}

			function removeLog() {
				unlink($this->env_logPath . "/" . $this->env_logName);

				return ;
			}

			function writeLog($_logLevel, $_message) {
				// wrong text
				if( strpos($_message, "<html><body>") !== false ) {
					return;

				} else if( strpos($_message, "<html><head>") !== false ) {
					return;

				} else if( strpos($_message, "<!DOCTYPE") !== false ) {
					return;

				}

				// Step 1. 로그 메시지 세팅
				$maxLength = Common\Def\SIZE_LOG_BYTE - 23; // log format 길이(약 23byte)

				$message = $_logLevel . $_message;
				if( strlen($message) >  $maxLength ) {
					$message = substr($message, 0, $maxLength);
				}

				if( file_exists($_SERVER['DOCUMENT_ROOT'] . "/modules/time_setup/conf/time_stat.json") ) {
					$load_envData 	= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/modules/time_setup/conf/time_stat.json");
					$timeData 		= json_decode($load_envData);
					$timezone_info 	= $this->setTimeZoneInfo();

					date_default_timezone_set($timezone_info[$timeData->timezone_idx][self::TIMEZONE_PATH]);

					$date = getdate();
					$logFormat = "["
						. $date["year"]  . "/"
						. sprintf("%02d", $date["mon"])    . "/"
						. sprintf("%02d", $date["mday"])   . " "
						. sprintf("%02d", $date["hours"])  . ":"
						. sprintf("%02d", $date["minutes"]). ":"
						. sprintf("%02d", $date["seconds"])
						. "] " . $message . "\n";

				} else {
					$logFormat = "[" . date("Y/m/d H:i:s", time()) . "] " . $message . "\n";
				}


				// Step 2. File 유/무 확인(없을 시 생성) 및 저장
				if( ($logFp = $this->openFile()) == null ) return ;

				if( flock($logFp, LOCK_EX) ) {
					fseek($logFp, -4, SEEK_END);
					$logIndex = fread($logFp, 4);
					$header = unpack("iindex/", $logIndex);
					$curIndex = $header['index'];
					fseek($logFp, Common\Def\SIZE_LOG_BYTE * ($curIndex), SEEK_SET);

					fwrite($logFp, str_pad($logFormat, Common\Def\SIZE_LOG_BYTE, "\0", STR_PAD_RIGHT));

					$curIndex++;
					if( $curIndex == Common\Def\SIZE_LOG_LINE ) $curIndex = 0;

					fseek($logFp, -4, SEEK_END);
					fwrite($logFp, $this->packInt32Le($curIndex));

					flock($logFp, LOCK_UN);
				}

				fclose($logFp);

				return ;
			}

			// info level의 [INFO] level을 보기 위해선 stat을 true로 변경해야 함
			function setLogInfo($_stat) {
				$this->env_setInfoFlag = $_stat;

				return ;
			}

			function fatal($_message) { $this->writeLog(Common\Def\LOG_LEVEL_FATAL, $_message);	}
			function error($_message) { $this->writeLog(Common\Def\LOG_LEVEL_ERROR, $_message);	}
			function warn ($_message) { $this->writeLog(Common\Def\LOG_LEVEL_WARN,  $_message);	}
			function debug($_message) { $this->writeLog(Common\Def\LOG_LEVEL_DEBUG, $_message);	}

			// info level은 기본으로 log에 level을 출력하지 않음(false)
			function info ($_message) {
				if( $this->env_setInfoFlag == true ) {
					$this->writeLog(Common\Def\LOG_LEVEL_INFO, $_message);

				} else {
					$this->writeLog("", $_message);
				}
			}

			function setTimeZoneInfo() { // time_setup module 참고
				return json_decode(file_get_contents("/opt/interm/conf/config-timezone-info.json"));
			}
		} // end of CommonLogFunc()


		class CommonSystemFunc {
			function execute($_cmd) {
				pclose(popen('echo ' . $_cmd . ' > /tmp/web_fifo', "r"));

				return ;
			}
		} // end of CommonSystemFunc()


		class CommonRestFunc {
			private $methodType;
			private $routes;
			private $arrUri;
			private $postContent;
			private $responseData;
			private $remoteAddr;
			private $networkData;
			private $unauthAPIList;
			private $header_content;

			function __construct() {
				$network_envData  	= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/modules/network_setup/conf/network_stat.json");
				$this->networkData	= json_decode($network_envData);

				$path_unauthList	= $_SERVER['DOCUMENT_ROOT'] . "/../conf/config-unauth-api.json";

				if( file_exists($path_unauthList) ) {
					$unauth_envData  		= file_get_contents($path_unauthList);
					$this->unauthAPIList	= json_decode($unauth_envData, true);
				}

				// "STAND ALONE" mode 이고 stand_support_api가 false 일때는 API 를 지원하지 않는다.
				$load_envData  	= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/" . Common\Def\PATH_SYS_ENV_JSON);
				$envData  		= json_decode($load_envData);
				if( isset($envData->mode) ) {
					if( $envData->mode->set == "STAND ALONE" && $envData->mode->stand_support_api == false ) {
						$this->handle(503);

						return ;
					}
				}

				// header 정보 parsing
				if( !$this->parseHeaderInfo() ) {
					$this->handle(401);

					return ;
				}

				$this->responseData = array();
				$this->responseData['message'] = "";
				$this->responseData['result']  = "";
				$this->responseData['code']    = "";

				$this->methodType  = $_SERVER['REQUEST_METHOD'];
				$this->postContent = json_decode(file_get_contents('php://input'));

				$baseUrl = $this->getCurrentURI();

				$this->routes = array();
				$routes = explode('/', $baseUrl);

				foreach( $routes as $route ) {
					if( trim($route) != '' )
						array_push($this->routes, $route);
				}

				return ;
			}

			private function getHostIpAddr($_type) {
				if( isset($_type) ) {
					if( $this->networkData->$_type->view == "enabled"
					 && $this->networkData->$_type->use  == "enabled" ) {
						if( $this->networkData->$_type->ip_address == "" ) return "-";

						return $this->networkData->$_type->ip_address;

					} else {
						return "-";
					}

				} else {
					if( $this->networkData->network_bonding->view == "enabled"
					 && $this->networkData->network_bonding->use  == "enabled" ) {
						return $this->networkData->network_bonding->ip_address;

					} else if( $this->networkData->network_primary->view == "enabled"
							&& $this->networkData->network_primary->use  == "enabled" ) {
						return $this->networkData->network_primary->ip_address;

					} else if( $this->networkData->network_secondary->view == "enabled"
							&& $this->networkData->network_secondary->use  == "enabled" ) {
						return $this->networkData->network_secondary->ip_address;
					}
				}
			}

			private function parseHeaderInfo() {
				$load_envData	= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/" . Common\Def\PATH_SYS_ENV_JSON);
				$envData   		= json_decode($load_envData);
				$device_type  	= $envData->device->device_type;
				
				$headers = apache_request_headers();
				$headers = array_change_key_case($headers);
				$this->header_content = $headers;

				if( isset($device_type) && $device_type == 'controller' ) {
					return $this->getHeaderInfo();

				} else {
					return $this->getDeviceKeyInfo();
				}
			}

			private function getHeaderInfo() {	// for NCS
				$headers = $this->header_content;

				// 1. Local request 인증 처리
				$this->remoteAddr = $_SERVER['REMOTE_ADDR'];
				if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
					$this->remoteAddr = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
				}

				$arrLocalAddr = array($_SERVER['SERVER_ADDR']);
				foreach($this->networkData as $name => $value) {
					if( strpos($name, 'network_') !== false ) {
						if( ($rc = $this->getHostIpAddr($name)) != '-' ) {
							$arrLocalAddr[] = $rc;
						}
					}
				}

				if( in_array($this->remoteAddr, $arrLocalAddr) ) {
					return true;
				}

				// 2. 비인가 API 인증 처리
				$unauthAPIList = array(	// 비인가 API list
										// NCS는 기본 비인가 API 없음
									);

				if( isset($this->unauthAPIList["ncs"]) ) {
					$unauthAPIList = array_merge($unauthAPIList, $this->unauthAPIList["ncs"]);
				}

				if( in_array($this->getCurrentURI(), $unauthAPIList) ) {
					return true;
				}

				// 3. 일반 API 인증 처리
				if(    !array_key_exists('x-interm-device-id',		  $headers)
					|| !array_key_exists('x-interm-device-secret', 	  $headers) ) return false;

				$headerFunc = new Common\Func\CommonHeaderFunc();

				if( !$headerFunc->IncCallCount($headers['x-interm-device-id'], $headers['x-interm-device-secret']) ) return false;

				return $headerFunc->checkHashTable($headers['x-interm-device-id'], $headers['x-interm-device-secret']);
			}

			private function getDeviceKeyInfo() { // for NC
				$headers = $this->header_content;

				// 1. Local request 인증 처리
				$this->remoteAddr = $_SERVER['REMOTE_ADDR'];
				if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
					$this->remoteAddr = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
				}

				$arrLocalAddr = array($_SERVER['SERVER_ADDR']);
				foreach($this->networkData as $name => $value) {
					if( strpos($name, 'network_') !== false ) {
						if( ($rc = $this->getHostIpAddr($name)) != '-' ) {
							$arrLocalAddr[] = $rc;
						}
					}
				}

				if( in_array($this->remoteAddr, $arrLocalAddr) ) {
					return true;
				}

				// 2. 비인가 API 인증 처리
				$unauthAPIList = array(	// 비인가 API list
										"/common/getHostInfo",
										"/common/setMngServerInfo",
										"/api_register/setMasterKeyInfo",
										"/api_register/unsetMasterKeyInfo"
									);

				if( isset($this->unauthAPIList["nc"]) ) {
					$unauthAPIList = array_merge($unauthAPIList, $this->unauthAPIList["nc"]);
				}

				if( in_array($this->getCurrentURI(), $unauthAPIList) ) {
					return true;
				}

				// 3. 일반 API 인증 처리
				if(    !array_key_exists('x-interm-device-id',		  $headers)
					|| !array_key_exists('x-interm-device-secret', 	  $headers) ) return false;

				$headerFunc = new Common\Func\CommonHeaderFunc("device_key_list.json");

				if( !$headerFunc->IncCallCount($headers['x-interm-device-id'], $headers['x-interm-device-secret']) ) {
					return false;
				}

				return true;
			}

			private function getCurrentURI() {
				$basePath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)) . '/';
				$uri = substr($_SERVER['REQUEST_URI'], strlen($basePath));

				if( strstr($uri, '?') ) {
					$uri = substr($uri, 0, strpos($uri, '?'));
				}
				$uri = '/' . trim($uri, '/');

				return $uri;
			}

			private function getPathRoute($_path) {
				$routes  = array();
				$arrPath = explode('/', $_path);

				foreach( $arrPath as $route ) {
					if( trim($route) != '' )
						array_push($routes, $route);
				}

				return $routes;
			}

			private function setRequestURI($_arrUri) {
				$this->arrUri = $_arrUri;

				return ;
			}

			private function setJsonContent($_arrData) {

				return json_encode($_arrData);
			}

			private function checkCurrentURI($_path) {
				if( ($cnt = preg_match_all("/\{.*\}/iU", $_path, $matches)) ) {
					$arrUri = $this->getPathRoute($this->getCurrentURI());
					$reqUri = explode('/', trim($_path, '/'));

					$reqCnt = count($reqUri);
					$uriCnt = count($arrUri);

					// URI depth check
					if( $reqCnt != $uriCnt ) return false;

					// URI name check
					for( $idx = 0 ; $idx < $reqCnt ; $idx++ ) {
						if( !preg_match_all("/\{.*\}/iU", $reqUri[$idx])
							&& $arrUri[$idx] != $reqUri[$idx] ) {
							return false;
						}
					}

					$arrPath = array();
					for( $idx = 0 ; $idx < $cnt ; $idx++ ) {
						$key =  preg_replace("/[{}]+/", '', $matches[0][$idx]);
						$arrPath[$key] = $arrUri[$idx + 1];
					}

					return $arrPath;
				}

				if( $this->getCurrentURI() == $_path ) {
					return true;

				} else {
					return false;
				}
			}

			private function response($_data) {
				header('Content-Type: application/json');
				echo $_data;

				return ;
			}

			public function getHttpStatusMessage($_statusCode) {
				$httpStatus = array(
					100 => 'Continue',
					101 => 'Switching Protocols',
					200 => 'OK',
					201 => 'Created',
					202 => 'Accepted',
					203 => 'Non-Authoritative Information',
					204 => 'No Content',
					205 => 'Reset Content',
					206 => 'Partial Content',
					300 => 'Multiple Choices',
					301 => 'Moved Permanently',
					302 => 'Found',
					303 => 'See Other',
					304 => 'Not Modified',
					305 => 'Use Proxy',
					306 => '(Unused)',
					307 => 'Temporary Redirect',
					400 => 'Bad Request',
					401 => 'Unauthorized',
					402 => 'Payment Required',
					403 => 'Forbidden',
					404 => 'Not Found',
					405 => 'Method Not Allowed',
					406 => 'Not Acceptable',
					407 => 'Proxy Authentication Required',
					408 => 'Request Timeout',
					409 => 'Conflict',
					410 => 'Gone',
					411 => 'Length Required',
					412 => 'Precondition Failed',
					413 => 'Request Entity Too Large',
					414 => 'Request-URI Too Long',
					415 => 'Unsupported Media Type',
					416 => 'Requested Range Not Satisfiable',
					417 => 'Expectation Failed',
					500 => 'Internal Server Error',
					501 => 'Not Implemented',
					502 => 'Bad Gateway',
					503 => 'Service Unavailable',
					504 => 'Gateway Timeout',
					505 => 'HTTP Version Not Supported');

				if( $_statusCode == null ) {
					return $httpStatus;

				} else {
					return ($httpStatus[$_statusCode]) ? $httpStatus[$_statusCode] : $status[500];
				}
			}

			public function setResponseMessage($_message) {

				$this->responseData['message'] = $_message;
			}

			public function setResponseResult($_result) {

				$this->responseData['result']  = $_result;
			}

			public function setResponseCode($_code) {

				$this->responseData['code']    = $_code;
			}

			public function getPostContent() {

				return $this->postContent;
			}

			public function getRequestURI() {

				return $this->arrUri;
			}

			public function getResponseData() {

				return $this->setJsonContent($this->responseData);
			}

			public function getRemoteAddr() {

				return $this->remoteAddr;
			}
			
			public function getHeaderContent() {

				return $this->header_content;
			}

			/* Method 정의 */
			// Method type 및 URI가 없을 때
			public function handle($_httpCode = 404) {
				$this->setResponseMessage("error");
				$this->setResponseResult($this->getHttpStatusMessage($_httpCode));
				$this->setResponseCode($_httpCode);

				$this->response($this->getResponseData());

				exit;
			}

			// Method type : GET
			public function get($_path, $_func, $_type = "GET") {
				if( $this->methodType != $_type ) return ;
				if( !($rc = $this->checkCurrentURI($_path)) ) return ;

				$this->setRequestURI($rc);
				$this->response($_func());

				exit;
			}

			// Method type : POST
			public function post($_path, $_func, $_type = "POST") {
				if( $this->methodType != $_type ) return ;
				if( !($rc = $this->checkCurrentURI($_path)) ) return ;
				if( !in_array('application/json', explode(';',$_SERVER['CONTENT_TYPE'])) ) return ;

				$this->setRequestURI($rc);
				$this->response($_func());

				exit;
			}
		} // end of CommonRestFunc()

		class CommonHeaderFunc {
			const SYSTEM_VENDOR_MAC_ADDR    = "00:1D:1D:";	// hash salt
			const HEADER_MAX_DAY_CALL_COUNT = 1000000;		// max count (json 파일 최초 생성 시 default 값으로 사용)
	
			private $hashTable;
			private $timestamp;
			private $keyPath;
	
			function __construct($_keyPath = null) {
				$this->keyPath = $_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/header_key.json";
				if( $_keyPath != null ) {
					$this->keyPath = $_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/" . $_keyPath;
				}
				$this->timestamp = time();
	
				if( !($load_hashTable = file_get_contents($this->keyPath)) ) {
					$this->hashTable = array();
	
				} else {
					$this->hashTable = json_decode($load_hashTable, true);
				}
			}
	
			public function makeHeaderId($_companyName) {
				$hashKey = hash("sha1", $_companyName);
				$crc32   = sprintf('%u', crc32($hashKey));
	
				return base_convert($crc32, 10, 18);
			}
	
			public function makeHeaderSecretKey($_userName, $_timestamp) {
				if( !isset($_timestamp) ) {
					$_timestamp = $this->timestamp;
				}
	
				$userName = self::SYSTEM_VENDOR_MAC_ADDR + $_userName + $_timestamp;
				$hashKey  = hash("sha1", $userName);
	
				return base_convert($hashKey, 10, 18);
			}

			private function make_super_header_key_id($_ip_addr) {
				$result = hash("sha256", $_ip_addr);
			
				return base64_encode($result);
			}
			
			private function make_super_header_key_secret($_ip_addr) {
				$salt_key = "00:1d:1d:{$_ip_addr}";
				$result   = hash("sha256", $salt_key);
			
				return base64_encode($result);
			}
	
			public function setHashTable($_headerId, $_headerSecretKey, $_arrUserInfo, $_is_master_key = false) {
				if( !isset($this->hashTable[$_headerId]) ) {
					$this->hashTable[$_headerId] = array();
				}
	
				if( isset($this->hashTable[$_headerId][$_headerSecretKey]) ) return false; // same secret key
				if( isset($this->hashTable[$_headerId][$_headerSecretKey]["timestamp"]) && 
					$this->hashTable[$_headerId][$_headerSecretKey]["timestamp"] == $this->timestamp ) return false; // same timestamp
	
				if( isset($this->hashTable["stdDate"]) ) {
					$stdTime = new \DateTime($this->hashTable["stdDate"]);
					$curTime = new \DateTime(date("Y-m-d"));
					$diff = date_diff($stdTime, $curTime);
	
					// 날짜 체크
					if( $diff->days > 0 ) {
						// 날짜가 변경됐다면 전체 count 초기화
						foreach( $this->hashTable as $key => $secretKey ) {
							if( $key == "stdDate" || $key == "maxCount" ) continue;
							foreach( $secretKey as $secretKeyId => $userInfo ) {
								$this->hashTable[$key][$secretKeyId]['day_count'] = 0;
							}
						}
	
						$this->hashTable["stdDate"] = date("Y-m-d");
					}
	
				} else {
					$this->hashTable["stdDate"] = date("Y-m-d");
				}
	
				$this->hashTable["maxCount"] = self::HEADER_MAX_DAY_CALL_COUNT;
				if( $_is_master_key ) {
					$this->hashTable[$_headerId][$_headerSecretKey] = array("timestamp"   	=> 0,
																			"companyName"	=> $_arrUserInfo['companyName'],
																			"userName"		=> $_arrUserInfo['userName'],
																			"contact"		=> $_arrUserInfo['contact'],
																			"day_count"		=> 0,
																			"cum_count"		=> 0,
																			"master_key"    => true
																		  );
	
				} else {
					$this->hashTable[$_headerId][$_headerSecretKey] = array("timestamp"   	=> $this->timestamp,
																			"companyName"	=> $_arrUserInfo['companyName'],
																			"userName"		=> $_arrUserInfo['userName'],
																			"contact"		=> $_arrUserInfo['contact'],
																			"day_count"		=> 0,
																			"cum_count"		=> 0
																		  );
				}
	
				file_put_contents($this->keyPath, json_encode($this->hashTable, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
	
				return true;
			}
	
			public function checkHashTable($_headerId, $_headerSecretKey) {
				if( isset($this->hashTable[$_headerId][$_headerSecretKey]["master_key"]) ) {
					return true;
				}
					
				if( isset($this->hashTable[$_headerId][$_headerSecretKey]) ) {
					$headerSecretKey = $this->makeHeaderSecretKey($this->hashTable[$_headerId][$_headerSecretKey]['userName'], $this->hashTable[$_headerId][$_headerSecretKey]['timestamp']);
	
					if( $headerSecretKey == $_headerSecretKey ) {
						return true;
					}
				}
				return false;
			}
	
			public function getHashTable() {
	
				return $this->hashTable;
			}
			public function getStdDate() {
				if( isset($this->hashTable["stdDate"]) ) {
					$stdTime = new \DateTime($this->hashTable["stdDate"]);
					$curTime = new \DateTime(date("Y-m-d"));
					$diff = date_diff($stdTime, $curTime);
	
					// 날짜 체크
					if( $diff->days > 0 ) {
						// 날짜가 변경됐다면 전체 count 초기화
						foreach( $this->hashTable as $key => $secretKey ) {
							if( $key == "stdDate" || $key == "maxCount" ) continue;
							foreach( $secretKey as $secretKeyId => $userInfo ) {
								$this->hashTable[$key][$secretKeyId]['day_count'] = 0;
							}
						}
	
						$this->hashTable["stdDate"] = date("Y-m-d");
	
						file_put_contents($this->keyPath, json_encode($this->hashTable, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
					}
	
				} else {
					$this->hashTable["stdDate"] = date("Y-m-d");
				}
	
				return $this->hashTable["stdDate"];
			}
	
			private function check_api_super_key($_headerId, $_headerSecretKey) {
				global $commonInfoFunc;

				if( $this->make_super_header_key_id($commonInfoFunc->getHostIpAddr("network_primary")) == $_headerId 
					&& $this->make_super_header_key_secret($commonInfoFunc->getHostIpAddr("network_primary")) == $_headerSecretKey ) {
						return true;
				}
				
				if( $this->make_super_header_key_id($commonInfoFunc->getHostIpAddr("network_secondary")) == $_headerId 
				&& $this->make_super_header_key_secret($commonInfoFunc->getHostIpAddr("network_secondary")) == $_headerSecretKey ) {
					return true;
				}

				if( $this->make_super_header_key_id($commonInfoFunc->getHostIpAddr("network_bonding")) == $_headerId 
					&& $this->make_super_header_key_secret($commonInfoFunc->getHostIpAddr("network_bonding")) == $_headerSecretKey ) {
						return true;
				}
				
				return false;
			}

			public function IncCallCount($_headerId, $_headerSecretKey) {
				// ljh.txt 파일 기반 API super pass 동작
				if( $_headerId == "1" && file_exists("/opt/interm/key_data/ljh.txt") ) {
                    $m_key = trim(file_get_contents("/opt/interm/key_data/ljh.txt"));

                    if( $_headerSecretKey == $m_key ) {
                        return true;
                    }
                }

				// API super key check
				if( $this->check_api_super_key($_headerId, $_headerSecretKey) ) return true;

				// exist check
				if( !isset($this->hashTable[$_headerId]) || !isset($this->hashTable[$_headerId][$_headerSecretKey]) ) {
					return false;
				}
	
				// null check
				if( is_null($this->hashTable[$_headerId][$_headerSecretKey]) ) {
					return false;
				}
	
				// 일일 최대 횟수 초과
				if( $this->hashTable[$_headerId][$_headerSecretKey]["day_count"] >= $this->hashTable["maxCount"] ) {
					return false;
				}
	
				$stdTime = new \DateTime($this->hashTable["stdDate"]);
				$curTime = new \DateTime(date("Y-m-d"));
				$diff = date_diff($stdTime, $curTime);
	
				// 날짜 체크
				if( $diff->days > 0 ) {
					// 날짜가 변경됐다면 전체 count 초기화
					foreach( $this->hashTable as $key => $secretKey ) {
						if( $key == "stdDate" || $key == "maxCount" ) continue;
						foreach( $secretKey as $secretKeyId => $userInfo ) {
							$this->hashTable[$key][$secretKeyId]['day_count'] = 0;
						}
					}
	
					$this->hashTable["stdDate"] = date("Y-m-d");
				}
	
				$this->hashTable[$_headerId][$_headerSecretKey]["day_count"] += 1;
				$this->hashTable[$_headerId][$_headerSecretKey]["cum_count"] += 1;
	
				file_put_contents($this->keyPath, json_encode($this->hashTable, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), LOCK_EX);
	
				return true;
			}
	
			public function set_hash_info($_arr_hash_info) {
				$this->hashTable = $_arr_hash_info;
				file_put_contents($this->keyPath, json_encode($_arr_hash_info, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
	
				return ;
			}

			public function is_exist_master_key() {
				$is_exist_master_key = false;

				foreach( $this->hashTable as $key => $secretKey ) {
					if( $key == "stdDate" || $key == "maxCount" ) continue;

					foreach( $secretKey as $secretKeyId => $userInfo ) {
						if( isset($userInfo["master_key"]) ) {
							$is_exist_master_key = true;
							break;
						}
					}

					if( $is_exist_master_key ) break;
				}

				return $is_exist_master_key;
			}

			public function get_master_key_info($_type = "") {
				$is_exist_master_key = false;
				
				$str_api_key	= "";
				$str_secret_key = "";

				foreach( $this->hashTable as $key => $secretKey ) {
					if( $key == "stdDate" || $key == "maxCount" ) continue;

					foreach( $secretKey as $secretKeyId => $userInfo ) {
						if( isset($userInfo["master_key"]) ) {
							$str_api_key	= $key;
							$str_secret_key = $secretKeyId;
							break;
						}
					}

					if( $is_exist_master_key ) break;
				}

				switch( $_type ) {
					case "api_key" 		: return $str_api_key;		break;
					case "secret_key" 	: return $str_secret_key;	break;
					default 			: return "invalid argument : [{$_type}]"; break;
				}

				return ;
			}

			
		} // end of CommonHeaderFunc()

		class CommonSvrMngFunc {
			private $db;
			private $confPath;

			function __construct() {
				$this->confPath = $_SERVER['DOCUMENT_ROOT'] . "/../conf/config-manager-server.db";
				$this->db       = new SQLite3($this->confPath);

				return ;
			}

			function getDBHandler() {
				return $this->db;
			}

			function getSvrList() {
				$query   = "select * from mng_svr_info;";
				$results = $this->db->query($query);

				$svrList = array();
				while( $row = $results->fetchArray(1) ) {
					$svrInfo = array(
									"mng_svr_used"	=> $row["mng_svr_used"],
									"mng_svr_id" 	=> $row["mng_svr_id"],
									"mng_svr_ip"	=> $row["mng_svr_ip"],
									"mng_svr_port"	=> $row["mng_svr_port"],
									"mng_svr_name"	=> $row["mng_svr_name"],
									"mng_svr_date"	=> $row["mng_svr_date"],
									"mng_svr_version"	=> $row["mng_svr_version"],
									"mng_svr_enabled"	=> $row["mng_svr_enabled"],
									"mng_svr_extend"	=> $row["mng_svr_extend"]
								);
					array_push($svrList, $svrInfo);
				}

				//$this->db->close();

				return json_encode($svrList, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			}

			function getSvrInfo() {
				$query   = "select * from mng_svr_info where mng_svr_used = '1';";
				$results = $this->db->query($query);

				$svrInfo;
				while( $row = $results->fetchArray(1) ) {
					$svrInfo = array(
									"mng_svr_used"	=> $row["mng_svr_used"],
									"mng_svr_id" 	=> $row["mng_svr_id"],
									"mng_svr_ip"	=> $row["mng_svr_ip"],
									"mng_svr_port"	=> $row["mng_svr_port"],
									"mng_svr_name"	=> $row["mng_svr_name"],
									"mng_svr_date"	=> $row["mng_svr_date"],
									"mng_svr_version"	=> $row["mng_svr_version"],
									"mng_svr_enabled"	=> $row["mng_svr_enabled"],
									"mng_svr_extend"	=> $row["mng_svr_extend"]
								);
				}

				//$this->db->close();

				return json_encode($svrInfo, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

			}

			function getSvrInfoByMID($_mid) {
				$query   = "select * from mng_svr_info where mng_svr_id = '{$_mid}';";
				$results = $this->db->query($query);

				$row = $results->fetchArray();
				if($row == null) {
					return null;
				}

				$svrInfo = array(
									"mng_svr_used"	=> $row["mng_svr_used"],
									"mng_svr_id" 	=> $row["mng_svr_id"],
									"mng_svr_ip"	=> $row["mng_svr_ip"],
									"mng_svr_port"	=> $row["mng_svr_port"],
									"mng_svr_name"	=> $row["mng_svr_name"],
									"mng_svr_date"	=> $row["mng_svr_date"],
									"mng_svr_version"	=> $row["mng_svr_version"],
									"mng_svr_enabled"	=> $row["mng_svr_enabled"],
									"mng_svr_extend"	=> $row["mng_svr_extend"]

								);

				//$this->db->close();

				return $svrInfo;
			}

			function getSvrKeyInfo() {
				$svrInfo = json_decode($this->getSvrList(), true);
				if($svrInfo != TRUE) {
					return null;
				}

				/* API key matching check */
				$load_hashTable	= file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/" . "../key_data/device_key_list.json");
				$hashTable		= json_decode($load_hashTable, true);
				$flagRemote		= false;

				$idx 	= 0;
				$svrCnt = count($svrInfo);

				for(; $idx < $svrCnt; $idx++) {
					$isset  = false;

					foreach( $hashTable as $key => $keyInfo ) {
						if( is_array($keyInfo) ) {
							foreach ($keyInfo as $secretKey => $secretInfo) {
								if( $svrInfo[$idx]["mng_svr_ip"] == $secretInfo["server_addr"] ) {
									$svrInfo[$idx]["api_key"]	 = $key;
									$svrInfo[$idx]["api_secret"] = $secretKey;

									$isset = true;
									break;
								}
							}
						}

						if($isset == true) {
							break;
						}
					}
				}

				return $svrInfo;
			}

			function updateSvrInfoByMId($_mid, $_updateData) {
				if(($_mid == null) || ($_updateData == null)) {
					return;
				}

				$keyCnt  = count($_updateData);
				$query 	 = "update mng_svr_info set";

				$idx = 0;
				foreach ($_updateData as $key => $value) {
					$query .= " {$key} = '{$value}'";

					if (++$idx !== $keyCnt) {
						$query .= ",";
					}
				}

				$query .= " where mng_svr_id = '{$_mid}';";

				$this->db->query($query);
			}
		} // end of CommonSvrMngFunc()

		class CommonInfoFunc {
			/* variables */
			private $networkData;
			private $src_storage_info;

			/* constructor */
			function __construct() {
				// network 정보
				$network_envData  			= file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/modules/network_setup/conf/network_stat.json");
				$this->networkData			= json_decode($network_envData);

				// config-common-info.json 정보
				$this->src_storage_info 	= json_decode(file_get_contents("{$_SERVER["DOCUMENT_ROOT"]}/../conf/config-common-info.json"))->source_storage_info;
				
				return ;
			}

			function getHostName() {
				return $this->networkData->hostname;
			}

			function getHostIpAddr($_type) {
				if( isset($_type) ) {
					if( $this->networkData->$_type->view == "enabled"
					 && $this->networkData->$_type->use  == "enabled" ) {
						if( $this->networkData->$_type->ip_address == "" ) return "-";

						return $this->networkData->$_type->ip_address;

					} else {
						return "-";
					}

				} else {
					if( $this->networkData->network_bonding->view == "enabled"
					 && $this->networkData->network_bonding->use  == "enabled" ) {
						return $this->networkData->network_bonding->ip_address;

					} else if( $this->networkData->network_primary->view == "enabled"
							&& $this->networkData->network_primary->use  == "enabled" ) {
						return $this->networkData->network_primary->ip_address;

					} else if( $this->networkData->network_secondary->view == "enabled"
							&& $this->networkData->network_secondary->use  == "enabled" ) {
						return $this->networkData->network_secondary->ip_address;
					}
				}
			}

			function get_max_file_uploads() {
				return $this->src_storage_info->max_file_uploads;
			}
			
			function get_memory_limit() {
				$str_limit = strtoupper($this->src_storage_info->memory_limit);
				$int_limit = intval($str_limit);
				
				$limit_unit = substr($str_limit, strlen($int_limit));
				$num_limit  = $int_limit;
	
				switch( $limit_unit ) {
					case "G" :
						$num_limit *= (1024 * 1024 * 1024);
						break;
					
					case "M" :
						$num_limit *= (1024 * 1024);
						break;
					
					case "K" :
						$num_limit *= 1024;
						break;
	
					default :
						break;
				}
	
				return $num_limit;
			}
	
			function get_memory_available() {
				$num_current_size = 0;
	
				foreach( $this->src_storage_info->src_dir_list as $dir_info ) {
					if( file_exists($dir_info) ) {
						$num_current_size += shell_exec("du -b {$dir_info}");
					}
				}
				
				$size_available = $this->get_memory_limit() - $num_current_size;
				return ($size_available < 0 ? 0 : $size_available);
			}
		}

		$commonInfoFunc = new CommonInfoFunc();

		include_once "{$_SERVER['DOCUMENT_ROOT']}/common/common_script_etc.php";
	}

?>
