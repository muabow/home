<?php
	const PATH_PIPE_FILE = "/tmp/web_fifo";
	const PERM_MODE = 0777;

	cli_set_process_title("web_pipe");

	// create named pipe
	if (file_exists(PATH_PIPE_FILE)) {
		unlink(PATH_PIPE_FILE);
	}
	umask(0);
	posix_mkfifo(PATH_PIPE_FILE, PERM_MODE);

	while( true ) {
		$runCode = "";

		$fp = fopen(PATH_PIPE_FILE, "r");
		$reders = array($fp);

		if( ($rc = stream_select($reders, $writers = null , $except = null , $timeout = null)) < 0 ) {
			echo "stream_select() failed\n";
			continue;

		} else if( $rc == 0 ) {
			// do nothing

		} else {
			// read data from the fifo
			while( ($commands = fgets($fp)) != null ) {
				$runCode .= $commands;
			}
		}

		fclose($fp);
		shell_exec($runCode);
	}

	return ;
?>
