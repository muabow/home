<?php
	$moduleName = "network_setup";
	$backupVersion = "0.0.0.0";
	$currentVersion = "0.0.0.0";

	# Check Version Info
	if ($argc > 1) { $backupVersion = $argv[1]; }
	if ($argc > 2) { $currentVersion = $argv[2]; }

	echo "# Backward Migration - $moduleName\n";

	### TODO : Migration Code
	echo "TODO : Migration Code\n";
	echo "$backupVersion => $currentVersion\n";

	if(true == isUpperVersion("1.2.1.0", $currentVersion)) {
		shell_exec('rm -f /opt/interm/bin/mdnsd');
		shell_exec('rm -f /opt/interm/bin/bonjour-browse');
		shell_exec('rm -f /opt/interm/bin/bonjour-register');
		shell_exec('rm -f /opt/interm/bin/bonjour-resolve');
		shell_exec('rm -f /opt/interm/usr/libdns_sd.so');

		shell_exec('rm -f /opt/interm/bin/scripts/mdnsd_monitor.sh');
		shell_exec('rm -f /opt/interm/public_html/modules/network_setup/bin/bonjour_register_monitor.sh');
	}

	echo "# Backward Migration - $moduleName : Done\n";
	
	/*
	 * Check upper version.
	 * ===============================================
	 * orgVersion		|compareVersion			|flag
	 * -----------------------------------------------
	 * 1.0.0.0			|1.0.0.0				|FALSE
	 * 1.0.0.0			|1.0.1.0				|FALSE
	 * 1.1.0.0			|1.0.1.0				|TRUE
	 * 1.0.1.0a			|1.0.1.0				|TRUE
	 * 1.0.1.0a			|1.0.1.0b				|FALSE
	 * ===============================================
	 */
	 
	function isUpperVersion($_orgVersion, $_compareVersion) {
		if (version_compare($_orgVersion, $_compareVersion, "==")) {
			echo "same version.\n";
			return false;
		} else {
			echo "is not same version.\n";
		}
		
		$tmpBackVer = preg_replace('/[a-zA-Z]*/', "", $_orgVersion);
		$tmpCurrentVer = preg_replace('/[a-zA-Z]*/', "", $_compareVersion);
		$tmpBackAlpha = preg_replace('/[0-9.]*/', "", $_orgVersion);
		$tmpCurrentAlpha = preg_replace('/[0-9.]*/', "", $_compareVersion);
		
		//echo "temp Backup Version (" . $tmpBackVer . "), temp Current Version (" . $tmpCurrentVer . ")\n";
		//echo "alphabet Backup Version (" . $tmpBackAlpha . "), alphabet Current Version (" . $tmpCurrentAlpha . ")\n";
		
		if (version_compare($tmpBackVer, $tmpCurrentVer, ">")) {
			echo "(tmp)current Version (" . $tmpCurrentVer . ") is lower version.\n";
			return true;
		}
		
		if ($tmpBackAlpha > $tmpCurrentAlpha) {
			echo "alphabet current (" . $tmpCurrentAlpha . ") is lower version.\n";
			return true;
		}
		
		return false;
	}
	
	echo "\n";
?>

