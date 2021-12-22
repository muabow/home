<?php
	include_once "common_define.php";
	include_once "common_script.php";

	$viwerFunc = new Api_viewer\Func\ApiViewerFunc();

	if( $_POST["type"] == "open_api" && isset($_POST["act"]) ) {
		$act = $_POST["act"];

		if( $act == "set_user" ) {
			$userMail   = $_POST["userMail"];
			$userContact = $_POST["userContact"];
			$userCompany = $_POST["userCompany"];

			echo $viwerFunc->setUserList($userMail, $userContact, $userCompany);

			return ;
		}
		else if( $act == "get_user" ) {

			echo $viwerFunc->getUserList();

			return ;
		}
		else if( $act == "remove_user" ) {
			$secretKey = $_POST["secretKey"];
			$rc     = false;

			if( $viwerFunc->removeUserList($secretKey) ) {
				$rc = $viwerFunc->getUserList();
			}

			echo $rc;

			return ;
		}
	}
	
	include_once 'api_viewer_process_etc.php';
?>
