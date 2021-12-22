<?php
    include_once '/opt/interm/public_html/api/api_websocket.php';
   
    $ws_uri = "";
	$ws_ip = "127.0.0.1";
	$cmd_id = 0x00;
	$data = null;
    # Check Version Info
	if ($argc > 1) { $ws_ip  = $argv[1]; }
	if ($argc > 2) { $ws_uri = $argv[2]; }
	if ($argc > 3) { $cmd_id = $argv[3]; }
	if ($argc > 4) { $data   = $argv[4]; }

	if( $ws_uri == "" )
	{
		return;
	}

	$ws_handler = new WebsocketHandler($ws_ip, $ws_uri);

	while( true ) {
		$rc = $ws_handler->send($cmd_id, $data);
		usleep(10000);
		if( $rc == 1 ) break;
	}
?>

