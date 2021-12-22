<?php

// 시스템의 Network 정보 설정 방법 변경에 따라,
// 현재 설정된 정보(network_stat.json)를 기반으로 시스템의 Network 정보를 재 설정 함
// 참조 : artf16129 Zynq 공통 : 랜케이블 탈착시 네트워킹 안됨


function PostIntSync($_url, $_postData) {
	$curlsession = curl_init();
	curl_setopt($curlsession, CURLOPT_URL, $_url);
	curl_setopt($curlsession, CURLOPT_POST, 1);
	curl_setopt($curlsession, CURLOPT_POSTFIELDS, $_postData);
	curl_setopt($curlsession, CURLOPT_RETURNTRANSFER, 1);

	$result = curl_exec($curlsession);

	curl_close($curlsession);

	return $result;
}

// 기본 정보
$url = "http://127.0.0.1/modules/network_setup/html/common/network_process.php";
$postData["type"] = "network";
$postData["act"] = "set_stat";
$postData["no_reboot"] = "true";



// 현재 Network 설정 정보를 그대로 적용
$network_stat = json_decode(file_get_contents("/opt/interm/public_html/modules/network_setup/conf/network_stat.json"));
$postData["hostname"] = $network_stat->hostname;
$postData["location"] = $network_stat->location;

$postData["primary_use"] = $network_stat->network_primary->use;
$postData["primary_dhcp"] = $network_stat->network_primary->dhcp;
$postData["primary_ip_address"] = $network_stat->network_primary->ip_address;
$postData["primary_subnetmask"] = $network_stat->network_primary->subnetmask;
$postData["primary_gateway"] = $network_stat->network_primary->gateway;
$postData["primary_dns_server_1"] = $network_stat->network_primary->dns_server_1;
$postData["primary_dns_server_2"] = $network_stat->network_primary->dns_server_2;

$postData["secondary_use"] = $network_stat->network_secondary->use;
$postData["secondary_dhcp"] = $network_stat->network_secondary->dhcp;
$postData["secondary_ip_address"] = $network_stat->network_secondary->ip_address;
$postData["secondary_subnetmask"] = $network_stat->network_secondary->subnetmask;
$postData["secondary_gateway"] = $network_stat->network_secondary->gateway;
$postData["secondary_dns_server_1"] = $network_stat->network_secondary->dns_server_1;
$postData["secondary_dns_server_2"] = $network_stat->network_secondary->dns_server_2;

$postData["bonding_use"] = $network_stat->network_bonding->use;
$postData["bonding_ip_address"] = $network_stat->network_bonding->ip_address;
$postData["bonding_subnetmask"] = $network_stat->network_bonding->subnetmask;
$postData["bonding_gateway"] = $network_stat->network_bonding->gateway;



// Network 정보를 재 설정 (Web -> 네트워크 설정 -> 적용)
PostIntSync($url, $postData);

?>

