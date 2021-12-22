<script type="text/javascript">
	$(document).ready(function() {
		var networkFunc = new NetworkFunc();
		var displayFunc = new CommonDisplayFunc();
		var networkLog   = new CommonLogFunc("network_setup");
		var logMsg;

		$(".tabs_list").click(function() {
			var number = $(".tabs_list").index(this) + 1;

			$(".tabs_list").removeClass("tabs_list_click");
			$(this).addClass("tabs_list_click");
			$("div[id^=tabs-]").hide();
			$("#tabs-"+(number)).show();

			if( number == 1 ) tabStat = "primary";
			if( number == 2 ) tabStat = "secondary";
			if( number == 3 ) tabStat = "bonding";

			var submitParams = "";
			submitParams += networkFunc.makeArgs("type",				"network");
			submitParams += networkFunc.makeArgs("act",					"set_tab");
			submitParams += networkFunc.makeArgs("tab_stat",			tabStat);

			networkFunc.postArgs("<?=Network_setup\Def\PATH_NETWORK_PROCESS ?>", submitParams);

			return ;
		});


		// 0. input style
		$('[id^=radio_networkSetupStatic]').click(function() {
			var arrId = this.id.split("_");
			var type  = arrId[arrId.length - 1];

			if( $("[id=radio_networkSetupDisable_" + type + "]:checked").val() == "on" ) return ;

			$(".input_textbox[id$=" + type + "]").attr("disabled", false);
			$(".input_textbox[id$=" + type + "]").css("background","#ffffff").css("color", "#000000");
		});

		$("[id^=radio_networkSetupDhcp]").click(function() {
			var arrId = this.id.split("_");
			var type  = arrId[arrId.length - 1];

			$(".input_textbox[id$=" + type + "]").attr("disabled", true);
			$(".input_textbox[id$=" + type + "]").css("background","#ebebe4").css("color", "#808080");
		});

		$('[id^=radio_networkSetupEnable]').click(function() {
			var arrId = this.id.split("_");
			var type  = arrId[arrId.length - 1];

			if( $("[id=radio_networkSetupDhcp_" + type + "]:checked").val() == "on" ) return ;

			$(".input_textbox[id$=" + type + "]").attr("disabled", false);
			$(".input_textbox[id$=" + type + "]").css("background","#ffffff").css("color", "#000000");
		});

		$("[id^=radio_networkSetupDisable]").click(function() {
			var arrId = this.id.split("_");
			var type  = arrId[arrId.length - 1];


			$(".input_textbox[id$=" + type + "]").attr("disabled", true);
			$(".input_textbox[id$=" + type + "]").css("background","#ebebe4").css("color", "#808080");
		});


		var placeholderTarget = $('.textbox input[type="text"], .textbox input[type="password"]');

		placeholderTarget.on('focus', function() {
			$(this).siblings('label').fadeOut('fast');
		});

		placeholderTarget.on('focusout', function() {
			if( $(this).val() == '' ) {
				$(this).siblings('label').fadeIn('fast');
			}
		});

		placeholderTarget.on('textchange', function() {
			$(this).siblings('label').fadeOut('fast');
		});

		// 1. Apply
		$("#button_networkApply").click(function() {
			var is_valid_network_info = true;

			// valid check : [장치 이름/장치 위치]
			$("input[type=text][id^=input_device]").each(function() {
				if( $(this).val().length == 0 || $(this).val().length > 32 ) {
					$(this).focus();
					alert("[" + $("div[name^=" + $(this).attr("id") + "]").html().trim() + "] <?=Network_setup\Lang\STR_NETWORK_SETUP_LENGTH ?>");

					is_valid_network_info = false;
					return false;
				}
			});
			if( !is_valid_network_info ) {
				return ;
			}

			// valid check : [네트워크 정보]
			$("input[type=text][id^=input_network]").each(function() {
				var arrId = $(this).attr("id").split("_");
				var type  = arrId[arrId.length - 1];

				var str_enable_radio = $("[name=radio_use_" + type + "]:checked").prop("id");
				var is_enable = (str_enable_radio.indexOf("Enable") < 0 ? false : true);

				if( !is_enable ) {
					return ;
				}

				if( !networkFunc.checkIpAddr($(this).val()) ) {
					if( $('[name=radio_dhcp_' + type + ']:checked').attr("id") == "radio_networkSetupDhcp_" + type ) {
						return ;
					}

					$(this).focus();
					var id    = $(this).attr("id").replace("_" + type, "");
					typeTitle = type.substring(0, 1).toUpperCase() + type.substring(1, type.length).toLowerCase();

					alert("[" + typeTitle + "] [" + $("div[name=" + $(this).attr("id").replace("_" + type, "") + "]").html().trim() + "] <?=Network_setup\Lang\STR_NETWORK_SETUP_INVALID ?>");

					is_valid_network_info = false;
					return false;
				}
			});
			if( !is_valid_network_info ) {
				return ;
			}

			if( !confirm("<?=Network_setup\Lang\STR_NETWORK_SETUP_APPLY_DHCP ?>") ) {
				return ;
			}

			displayFunc.showLoader();
			displayFunc.hideLoader(60);

			var submitParams = "";
			submitParams += networkFunc.makeArgs("type", "network");
			submitParams += networkFunc.makeArgs("act",  "get_stat");

			var rc = networkFunc.postArgs("<?=Network_setup\Def\PATH_NETWORK_PROCESS ?>", submitParams);
			var stat = JSON.parse(rc);

			var enableStat;
			var dhcpStat;
			var msgFlag;
			var tableFlag;
			submitParams = "";
			logMsg = "";
			msgFlag = false;
			tableFlag = false;

			submitParams += networkFunc.makeArgs("type",			"network");
			submitParams += networkFunc.makeArgs("act",				"set_stat");

			submitParams += networkFunc.makeArgs("hostname", 		encodeURIComponent($("#input_deviceHostname").val()));
			submitParams += networkFunc.makeArgs("location", 		encodeURIComponent($("#input_deviceLocation").val()));

			logMsg = "<?=Network_setup\Lang\STR_NETWORK_SETUP_APPLY ?><br \>"

			if((stat.hostname != $("#input_deviceHostname").val()) && (stat.location != $("#input_deviceLocation").val())) {
				logMsg += "<table><tr><td width='170'></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_HOST ?></td><td>: " + $("#input_deviceHostname").val() + "</td></tr> "+
				 "<tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_DEVICE ?></td><td>: " + $("#input_deviceLocation").val()+"</td></tr></table>";

			} else if(stat.hostname != $("#input_deviceHostname").val()) {
				logMsg += "<table><tr><td width='170'></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_HOST ?></td><td>: " + $("#input_deviceHostname").val() + "</td></tr></table>";

			} else if(stat.location != $("#input_deviceLocation").val()) {
				logMsg += "<table><tr><td width='170'></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_DEVICE ?></td><td>: " + $("#input_deviceLocation").val() + "</td></tr></table>";

			}

			if( $('[name=radio_use_primary]:checked').attr("id") == "radio_networkSetupEnable_primary" ) enableStat = "enabled";
			else enableStat = "disabled";
			submitParams += networkFunc.makeArgs("primary_use",	 			enableStat);

			if( $('[name=radio_dhcp_primary]:checked').attr("id") == "radio_networkSetupDhcp_primary" ) dhcpStat = "on";
			else dhcpStat = "off";

			submitParams += networkFunc.makeArgs("primary_dhcp",	 		dhcpStat);
			submitParams += networkFunc.makeArgs("primary_ip_address",		$("#input_networkIpAddr_primary").val());
			submitParams += networkFunc.makeArgs("primary_subnetmask",		$("#input_networkSubnetmask_primary").val());
			submitParams += networkFunc.makeArgs("primary_gateway",  		$("#input_networkGateway_primary").val());
			submitParams += networkFunc.makeArgs("primary_dns_server_1",	$("#input_networkDns1_primary").val());
			submitParams += networkFunc.makeArgs("primary_dns_server_2",	$("#input_networkDns2_primary").val());

			if((stat.primary.use != enableStat) && (enableStat == "enabled")) {
				logMsg += "<table><tr><td width='180' align='right'>-</td><td>Primary</td></tr><tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_USE_INFO ?></td><td>: " + "<?=Network_setup\Lang\STR_NETWORK_USE_ENABLE ?></td></tr>";
				tableFlag = true;
			} else if ((stat.primary.use != enableStat) && (enableStat == "disabled")) {
				logMsg += "<table><tr><td width='180' align='right'>-</td><td>Primary</td></tr><tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_USE_INFO ?></td><td>: " + "<?=Network_setup\Lang\STR_NETWORK_USE_DISABLE ?></td></tr>";
				tableFlag = true;
			} else {
				msgFlag = true;
			}

			if((stat.primary.dhcp != dhcpStat) && (dhcpStat == "on")) {
				if(msgFlag == true) {
					logMsg += "<table><tr><td width='180' align='right'>-</td><td>Primary</td></tr><tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_DHCP_INFO ?></td><td>: " + "<?=Network_setup\Lang\STR_NETWORK_SETUP_DHCP ?></td></tr>";
					msgFlag = false;

				} else {
					logMsg += "<tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_DHCP_INFO ?></td><td>: " + "<?=Network_setup\Lang\STR_NETWORK_SETUP_DHCP ?></td></tr>";
				}

				tableFlag = true;
			} else if((stat.primary.dhcp != dhcpStat) && dhcpStat == "off") {
				if(msgFlag == true) {
					logMsg += "<table><tr><td width='180' align='right'>-</td><td>Primary</td></tr><tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_DHCP_INFO ?></td><td>: " + "<?=Network_setup\Lang\STR_NETWORK_SETUP_USER ?></td></tr>";
					msgFlag = false;

				} else {
					logMsg += "<tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_DHCP_INFO ?></td><td>: " + "<?=Network_setup\Lang\STR_NETWORK_SETUP_USER ?></td></tr>";

				}
				tableFlag = true;

			}
			if(stat.primary.ip_address != $("#input_networkIpAddr_primary").val()) {
				if(msgFlag == true) {
					logMsg += "<table><tr><td width='180' align='right'>-</td><td>Primary</td></tr><tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_IP ?></td><td>: "+ $("#input_networkIpAddr_primary").val()+"</td></tr>";
					msgFlag = false;

				} else {
					logMsg += "<tr><td></td>td><?=Network_setup\Lang\STR_NETWORK_SETUP_IP ?></td><td>: "+ $("#input_networkIpAddr_primary").val()+"</td></tr>";

				}
				tableFlag = true;
			}
			if(stat.primary.subnetmask != $("#input_networkSubnetmask_primary").val()) {
				if(msgFlag == true) {
					logMsg += "<table><tr><td width='180' align='right'>-</td><td>Primary</td></tr><tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_SUBNET ?></td><td>: " + $("#input_networkSubnetmask_primary").val()+"</td></tr>";
					msgFlag = false;

				} else {
					logMsg += "<tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_SUBNET ?></td><td>: " + $("#input_networkSubnetmask_primary").val()+"</td></tr>";

				}
				tableFlag = true;
			}
			if(stat.primary.gateway != $("#input_networkGateway_primary").val()) {
				if(msgFlag == true) {
					logMsg += "<table><tr><td width='180' align='right'>-</td><td>Primary</td></tr><tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_GATEWAY ?></td><td>: " + $("#input_networkGateway_primary").val()+"</td></tr>";
					msgFlag = false;

				} else {
					logMsg += "<tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_GATEWAY ?></td><td>: " + $("#input_networkGateway_primary").val()+"</td></tr>";

				}
				tableFlag = true;
			}
			if(stat.primary.dns_server_1 != $("#input_networkDns1_primary").val()) {
				if(msgFlag == true) {
					logMsg += "<table><tr><td width='180' align='right'>-</td><td>Primary</td></tr><tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_DNS1 ?></td><td>: " + $("#input_networkDns1_primary").val()+"</td></tr>";
					msgFlag = false;

				} else {
					logMsg += "<tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_DNS1 ?></td><td>: " + $("#input_networkDns1_primary").val()+"</td></tr>";

				}
				tableFlag = true;
			}
			if(stat.primary.dns_server_2 != $("#input_networkDns2_primary").val()) {
				if(msgFlag == true) {
					logMsg += "<table><tr><td width='180' align='right'>-</td><td>Primary</td></tr><tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_DNS2 ?></td><td>: " +$("#input_networkDns2_primary").val()+"</td></tr>";
					msgFlag = false;

				} else {
					logMsg += "<tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_DNS2 ?></td><td>: " +$("#input_networkDns2_primary").val()+"</td></tr>";

				}
				tableFlag = true;
			}

			if(tableFlag == true) {
				logMsg += "</table>"
				tableFlag = false;
			}

			if(logMsg != "<?=Network_setup\Lang\STR_NETWORK_SETUP_APPLY ?><br \>"){
				networkLog.info(logMsg);

			}
			msgFlag = false;

			logMsg = "<?=Network_setup\Lang\STR_NETWORK_SETUP_APPLY ?><br \>"

			if( $('[name=radio_use_secondary]:checked').attr("id") == "radio_networkSetupEnable_secondary" ) enableStat = "enabled";
			else enableStat = "disabled";
			submitParams += networkFunc.makeArgs("secondary_use",	 		enableStat);

			if( $('[name=radio_dhcp_secondary]:checked').attr("id") == "radio_networkSetupDhcp_secondary" ) dhcpStat = "on";
			else dhcpStat = "off";
			submitParams += networkFunc.makeArgs("secondary_dhcp",	 		dhcpStat);
			submitParams += networkFunc.makeArgs("secondary_ip_address",	$("#input_networkIpAddr_secondary").val());
			submitParams += networkFunc.makeArgs("secondary_subnetmask",	$("#input_networkSubnetmask_secondary").val());
			submitParams += networkFunc.makeArgs("secondary_gateway",  		$("#input_networkGateway_secondary").val());
			submitParams += networkFunc.makeArgs("secondary_dns_server_1",	$("#input_networkDns1_secondary").val());
			submitParams += networkFunc.makeArgs("secondary_dns_server_2",	$("#input_networkDns2_secondary").val());

			if((stat.secondary.use != enableStat) && (enableStat == "enabled")) {
				logMsg += "<table><tr><td width='180' align='right'>-</td><td>Secondary</td></tr>"+"<tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_USE_INFO ?></td><td>: " + "<?=Network_setup\Lang\STR_NETWORK_USE_ENABLE ?></td></tr>";
				tableFlag = true;
			} else if ((stat.secondary.use != enableStat) && (enableStat == "disabled")) {
				logMsg += "<table><tr><td width='180' align='right'>-</td><td>Secondary</td></tr>"+"<tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_USE_INFO ?></td><td>: " + "<?=Network_setup\Lang\STR_NETWORK_USE_DISABLE ?></td></tr>";
				tableFlag = true;
			} else {
				msgFlag = true;

			}

			if((stat.secondary.dhcp != dhcpStat) && (dhcpStat == "on")) {
				if(msgFlag == true) {
					logMsg += "<table><tr><td width='180' align='right'>-</td><td>Secondary</td></tr><tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_DHCP_INFO ?></td><td>: " + "<?=Network_setup\Lang\STR_NETWORK_SETUP_DHCP ?></td></tr>";
					msgFlag = false;

				} else {
					logMsg += "<tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_DHCP_INFO ?></td><td>: " + "<?=Network_setup\Lang\STR_NETWORK_SETUP_DHCP ?></td></tr>";
				}
				tableFlag = true;
			} else if((stat.secondary.dhcp != dhcpStat) && (dhcpStat == "off")) {
				if(msgFlag == true) {
					logMsg += "<table><tr><td width='180' align='right'>-</td><td>Secondary</td></tr><tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_DHCP_INFO ?></td><td>: " + "<?=Network_setup\Lang\STR_NETWORK_SETUP_USER ?></td></tr>";
					msgFlag = false;

				} else {
					logMsg += "<tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_DHCP_INFO ?></td><td>: " + "<?=Network_setup\Lang\STR_NETWORK_SETUP_USER ?></td></tr>";
				}
				tableFlag = true;
			}
			if(stat.secondary.ip_address != $("#input_networkIpAddr_secondary").val()) {
				if(msgFlag == true) {
					logMsg += "<table><tr><td width='180' align='right'>-</td><td>Secondary</td></tr><tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_IP ?></td><td>: "+ $("#input_networkIpAddr_secondary").val()+"</td></tr>";
					msgFlag = false;

				} else {
					logMsg += "<tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_IP ?></td><td>: "+ $("#input_networkIpAddr_secondary").val()+"</td></tr>";

				}
				tableFlag = true;
			}
			if(stat.secondary.subnetmask != $("#input_networkSubnetmask_secondary").val()) {
				if(msgFlag == true) {
					logMsg += "<table><tr><td width='180' align='right'>-</td><td>Secondary</td></tr><tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_SUBNET ?></td><td>: " + $("#input_networkSubnetmask_secondary").val()+"</td></tr>";
					msgFlag = false;

				} else {
					logMsg += "<tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_SUBNET ?></td><td>: " + $("#input_networkSubnetmask_secondary").val()+"</td></tr>";

				}
				tableFlag = true;
			}
			if(stat.secondary.gateway != $("#input_networkGateway_secondary").val()) {
				if(msgFlag == true) {
					logMsg += "<table><tr><td width='180' align='right'>-</td><td>Secondary</td></tr><tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_GATEWAY ?></td><td>: " + $("#input_networkGateway_secondary").val()+"</td></tr>";
					msgFlag = false;

				} else {
					logMsg += "<tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_GATEWAY ?></td><td>: " + $("#input_networkGateway_secondary").val()+"</td></tr>";

				}
				tableFlag = true;
			}
			if(stat.secondary.dns_server_1 != $("#input_networkDns1_secondary").val()) {
				if(msgFlag == true) {
					logMsg += "<table><tr><td width='180' align='right'>-</td><td>Secondary</td></tr><tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_DNS1 ?></td><td>: " + $("#input_networkDns1_secondary").val()+"</td></tr>";
					msgFlag = false;

				} else {
					logMsg += "<tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_DNS1 ?></td><td>: " + $("#input_networkDns1_secondary").val()+"</td></tr>";

				}
				tableFlag = true;
			}
			if(stat.secondary.dns_server_2 != $("#input_networkDns2_secondary").val()) {
				if(msgFlag == true) {
					logMsg += "<table><tr><td width='180' align='right'>-</td><td>Secondary</td></tr><tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_DNS2 ?></td><td>: " +$("#input_networkDns2_secondary").val()+"</td></tr>";
					msgFlag = false;

				} else {
					logMsg += "<tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_DNS2 ?></td><td>: " +$("#input_networkDns2_secondary").val()+"</td></tr>";

				}
				tableFlag = true;
			}

			if(tableFlag == true) {
				logMsg += "</table>"
				tableFlag = false;

			}
			if(logMsg != "<?=Network_setup\Lang\STR_NETWORK_SETUP_APPLY ?><br \>"){
				networkLog.info(logMsg);

			}
			msgFlag = false;

			logMsg = "<?=Network_setup\Lang\STR_NETWORK_SETUP_APPLY ?><br \>"

			if( $('[name=radio_use_bonding]:checked').attr("id") == "radio_networkSetupEnable_bonding" ) enableStat = "enabled";
			else enableStat = "disabled";

			submitParams += networkFunc.makeArgs("bonding_use",	 			enableStat);
			submitParams += networkFunc.makeArgs("bonding_ip_address",		$("#input_networkIpAddr_bonding").val());
			submitParams += networkFunc.makeArgs("bonding_subnetmask",		$("#input_networkSubnetmask_bonding").val());
			submitParams += networkFunc.makeArgs("bonding_gateway",			$("#input_networkGateway_bonding").val());

			if((stat.bonding.use != enableStat) && (enableStat == "enabled")){
				logMsg += "<table><tr><td width='180' align='right'>-</td><td>Bonding</td></tr><tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_USE_INFO ?></td><td>: " + "<?=Network_setup\Lang\STR_NETWORK_USE_ENABLE ?></td></tr>";
				tableFlag = true;
			} else if((stat.bonding.use != enableStat) && (enableStat == "disabled")) {
				logMsg += "<table><tr><td width='180' align='right'>-</td><td>Bonding</td></tr><tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_USE_INFO ?></td><td>: " + "<?=Network_setup\Lang\STR_NETWORK_USE_DISABLE ?></td></tr>";
				tableFlag = true;

			} else {
				msgFlag = true;
			}

			if(stat.bonding.ip_address != $("#input_networkIpAddr_bonding").val()) {
				if(msgFlag == true) {
					logMsg += "<table><tr><td width='180' align='right'>-</td><td>Bonding</td></tr><tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_IP ?></td><td>: "+ $("#input_networkIpAddr_bonding").val()+"</td></tr>";
					msgFlag = false;

				} else {
					logMsg += "<tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_IP ?></td><td>: "+ $("#input_networkIpAddr_bonding").val()+"</td></tr>";

				}
				tableFlag = true;

			}
			if(stat.bonding.subnetmask != $("#input_networkSubnetmask_bonding").val()) {
				if(msgFlag == true) {
				logMsg += "<table><tr><td width='180' align='right'>-</td><td>Bonding</td></tr><tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_SUBNET ?></td><td>: " + $("#input_networkSubnetmask_bonding").val()+"</td></tr>";
					msgFlag = false;

				} else {
					logMsg += "<tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_SUBNET ?></td><td>: " + $("#input_networkSubnetmask_bonding").val()+"</td></tr>";

				}
				tableFlag = true;

			}
			if(stat.bonding.gateway != $("#input_networkGateway_bonding").val()) {
				if(msgFlag == true) {
				logMsg += "<table><tr><td width='180' align='right'>-</td><td>Bonding</td></tr><tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_GATEWAY ?></td><td>: " + $("#input_networkGateway_bonding").val()+"</td></tr>";
					msgFlag = false;

				} else {
					logMsg += "<tr><td></td><td><?=Network_setup\Lang\STR_NETWORK_SETUP_GATEWAY ?></td><td>: " + $("#input_networkGateway_bonding").val()+"</td></tr>";

				}
				tableFlag = true;
			}

			if(tableFlag == true) {
				logMsg += "</table>"
				tableFlag = false;

			}
			if(logMsg != "<?=Network_setup\Lang\STR_NETWORK_SETUP_APPLY ?><br \>"){
				networkLog.info(logMsg);

			}

			networkFunc.postArgs("<?=Network_setup\Def\PATH_NETWORK_PROCESS ?>", submitParams);

			$("#button_networkCancel").trigger("click");

			return ;
		});

		// 2. Cancel
		$("#button_networkCancel").click(function() {
			var submitParams = "";
			submitParams += networkFunc.makeArgs("type", "network");
			submitParams += networkFunc.makeArgs("act",  "get_stat");

			var rc = networkFunc.postArgs("<?=Network_setup\Def\PATH_NETWORK_PROCESS ?>", submitParams);
			var stat = JSON.parse(rc);

			$("#input_deviceHostname").trigger("focus").val(stat.hostname);
			$("#input_deviceLocation").trigger("focus").val(stat.location);

			if( stat.primary.dhcp == "on" ) {
				$('#radio_networkSetupDhcp_primary').trigger("click");

			} else {
				$('#radio_networkSetupStatic_primary').trigger("click");
			}

			if( stat.primary.use == "enabled" ) {
				$('#radio_networkSetupEnable_primary').trigger("click");

			} else {
				$('#radio_networkSetupDisable_primary').trigger("click");
			}

			$("#input_networkIpAddr_primary").trigger("focus").val(stat.primary.ip_address);

			$("#input_networkSubnetmask_primary").trigger("focus").val(stat.primary.subnetmask);
			$("#input_networkGateway_primary").trigger("focus").val(stat.primary.gateway);
			$("#input_networkDns1_primary").trigger("focus").val(stat.primary.dns_server_1);
			$("#input_networkDns2_primary").trigger("focus").val(stat.primary.dns_server_2);

			if( stat.secondary.dhcp == "on" ) {
				$('#radio_networkSetupDhcp_secondary').trigger("click");

			} else {
				$('#radio_networkSetupStatic_secondary').trigger("click");
			}

			if( stat.secondary.use == "enabled" ) {
				$('#radio_networkSetupEnable_secondary').trigger("click");

			} else {
				$('#radio_networkSetupDisable_secondary').trigger("click");
			}

			$("#input_networkIpAddr_secondary").trigger("focus").val(stat.secondary.ip_address);
			$("#input_networkSubnetmask_secondary").trigger("focus").val(stat.secondary.subnetmask);
			$("#input_networkGateway_secondary").trigger("focus").val(stat.secondary.gateway);
			$("#input_networkDns1_secondary").trigger("focus").val(stat.secondary.dns_server_1);
			$("#input_networkDns2_secondary").trigger("focus").val(stat.secondary.dns_server_2);

			if( stat.bonding.use == "enabled" ) {
				$('#radio_networkSetupEnable_bonding').trigger("click");

			} else {
				$('#radio_networkSetupDisable_bonding').trigger("click");
			}

			$("#input_networkIpAddr_bonding").trigger("focus").val(stat.bonding.ip_address);
			$("#input_networkSubnetmask_bonding").trigger("focus").val(stat.bonding.subnetmask);
			$("#input_networkGateway_bonding").trigger("focus").val(stat.bonding.gateway);

			return ;
		});

		$("[id^=check_useNetwork_]").click(function() {
			var stat    = $(this).is(":checked") == true ? "enabled" : "disabled";
			var arrType = $(this).attr("id").split("_");
			var type    = arrType[arrType.length - 1];

			var submitParams = "";
			submitParams += networkFunc.makeArgs("type",	"network");
			submitParams += networkFunc.makeArgs("act",		"set_use");
			submitParams += networkFunc.makeArgs("tab", 	type);
			submitParams += networkFunc.makeArgs("stat",	stat);

			var rc = networkFunc.postArgs("<?=Network_setup\Def\PATH_NETWORK_PROCESS ?>", submitParams);

			return ;
		});
	});

	class NetworkFunc {
		constructor() {		}

		makeArgs(_key, _value) {
			var args = "&" + _key + "=" + _value;

			return args;
		}

		postArgs(_target, _args) {
			var result;

			$.ajax({
				type	: "POST",
				url		: _target,
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

		checkIpAddr(_ipAddr) {
			var arrIpAddr = _ipAddr.split(":");
			var ipAddr = arrIpAddr[0];
			var port   = arrIpAddr[1];

			if( /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(ipAddr) ) {
				if( arrIpAddr.length > 1 ) {
					if( $.isNumeric(port) && port >= 1 && port <= 65535 ) {
						return true;
					} else {
						return false;
					}
				}
				return true;

			} else {
				return false;
			}
		}

	} // end of NetworkFunc()


</script>

<?php
	include_once 'common_js_etc.php';
?>