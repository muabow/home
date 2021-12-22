<?php
	$load_envData = file_get_contents(dirname(__FILE__) . "/../conf/network_stat.json");
	$networkData  = json_decode($load_envData);

	function execFunc($_cmd) {
		pclose(popen('echo "' . $_cmd . '" > /tmp/web_fifo', "r"));

		return ;
	}

	$machineId = trim(shell_exec('cat /etc/machine-id 2>/dev/null'));

	// env info
	$load_envData  	= file_get_contents("/opt/interm/conf/env.json");
	$envData   		= json_decode($load_envData);

	/*
	 * version, 1.0: default
	 *          2.0: add view, subnetmask, gateway, dns1, dns2, tabStat, info_version
    */
	$contents = "<?xml version='1.0' standalone='no'?>
<!DOCTYPE service-group SYSTEM 'avahi-service.dtd'>

<service-group>
	<name replace-wildcards='yes'>Inter-M : %h</name>
	<service protocol='ipv4'>
		<type>_interm._tcp</type>
		<port>80</port>
		<txt-record>'secondary':{'view':'{$networkData->network_secondary->view}','use':'{$networkData->network_secondary->use}','dhcp':'{$networkData->network_secondary->dhcp}','mac_address':'{$networkData->network_secondary->mac_address}','ip_address':'{$networkData->network_secondary->ip_address}','subnetmask':'{$networkData->network_secondary->subnetmask}','gateway':'{$networkData->network_secondary->gateway}','dns_server_1':'{$networkData->network_secondary->dns_server_1}','dns_server_2':'{$networkData->network_secondary->dns_server_2}'}}</txt-record>
		<txt-record>'primary':{'view':'{$networkData->network_primary->view}','use':'{$networkData->network_primary->use}','dhcp':'{$networkData->network_primary->dhcp}','mac_address':'{$networkData->network_primary->mac_address}','ip_address':'{$networkData->network_primary->ip_address}','subnetmask':'{$networkData->network_primary->subnetmask}','gateway':'{$networkData->network_primary->gateway}','dns_server_1':'{$networkData->network_primary->dns_server_1}','dns_server_2':'{$networkData->network_primary->dns_server_2}'},</txt-record>
		<txt-record>'bonding':{'view':'{$networkData->network_bonding->view}','use':'{$networkData->network_bonding->use}','mac_address':'{$networkData->network_bonding->mac_address}','ip_address':'{$networkData->network_bonding->ip_address}','subnetmask':'{$networkData->network_bonding->subnetmask}','gateway':'{$networkData->network_bonding->gateway}'},</txt-record>
		<txt-record>'location':'{$networkData->location}',</txt-record>
		<txt-record>'hostname':'{$networkData->hostname}',</txt-record>
		<txt-record>'tabStat':'{$networkData->tabStat}',</txt-record>
		<txt-record>'version':'{$envData->device->version}',</txt-record>
		<txt-record>'device_name':'{$envData->device->name}',</txt-record>
		<txt-record>'device_id':'{$machineId}',</txt-record>
		<txt-record>{'info_version':'2.0',</txt-record>
	</service>
</service-group>";

	$isExistFile = file_exists("/opt/interm/bin/mdnsd");
	if(false == $isExistFile) {
		file_put_contents("/etc/avahi/services/interm.service", $contents);
		shell_exec('sudo service avahi-daemon restart');
		
	} else {
		$parseXml = simplexml_load_string($contents);
		if(false == $parseXml) {
			return;
		}
		
		$searchEleName = "txt-record";
		$elements	   = $parseXml->service->$searchEleName;
		$eleCount	   = count($elements);
		$writeContents = "";
		
		for($idx = 0; $idx < $eleCount; $idx++) {
			$writeContents .= $elements[$idx]."\n";
		}
		
		$modulePath = "/opt/interm/public_html/modules/network_setup/";
		file_put_contents("{$modulePath}conf/bonjour.service", $writeContents);
		shell_exec("{$modulePath}bin/bonjour_register_monitor.sh > /dev/null 2>/dev/null &");
	}
	
?>
