<script type="text/javascript">
	$(document).ready(function() {
		// Log form resize
		$("#div_monitor_list_resize").resizable({
			minWidth: 938,
			maxWidth: 938,
			minHeight: 350

		}).resize(function() {
			var reSize   = (parseInt($("#div_monitor_list_resize").css("height")) - 44);
			$("#div_monitor_list_device").css("height", reSize + "px");

			return ;
		});

		$("#div_monitor_add_fold").click(function() {
			$("#div_monitor_add").slideToggle('2000', "swing");
			$("#div_monitor_arrow").toggleClass("div_arrow_down");

			return ;
		});

		$("#div_monitor_device_clear").click(function() {
			$("[id^=input_monitor_device_").val("");

			return
		});

		$("#div_monitor_device_add").click(function() {
			var ipAddr   = $("#input_monitor_device_ipaddr").val().trim();
			var hostname = $("#input_monitor_device_name").val().trim();
			var location = $("#input_monitor_device_location").val().trim();

			if( monitorFunc.checkIpAddr(ipAddr) == false ) {
				$("#input_monitor_device_ipaddr").focus();

				alert("<?=Monitor\Lang\STR_ADD_IPADDR_CHECK ?>");

				return ;
			}

			if( !hostname ) hostname = "-";
			if( !location ) location = "-";

			if( !confirm("[" + ipAddr + "(" + hostname + ")] <?=Monitor\Lang\STR_ADD_IPADDR_CONFIRM ?>") ) {
				return ;
			}

			if( !monitorFunc.setDeviceList(ipAddr, hostname, location) ) {
				alert("<?=Monitor\Lang\STR_ADD_IPADDR_SAME ?>");

				return ;
			}
			alert("<?=Monitor\Lang\STR_ADD_IPADDR_REGIST ?>");

			$("#div_monitor_table_body").html(monitorFunc.getDeviceList());

			monitorFunc.clear();
			monitorFunc.checkDeviceStatus();

			var rowCnt = $("#div_monitor_table_body").children(".divTableRow").length;
			for( var idx = 0 ; idx < rowCnt ; idx++ ) {
				initEqulizer("outputVolume_" + (idx + 1));
			}

			$("#div_monitor_device_clear").trigger("click");

			return ;
		});

		$("#div_monitor_device_remove").click(function() {
			$("[id^=input_monitor_check_device]:checked").each(function() {
				var arrId  = $(this).attr("id").split("_");
				var idx    = arrId[arrId.length - 1];
				var ipAddr = $("#span_monitor_table_ipaddr_" + idx).html().trim();
				var rc;

				if( !(rc = monitorFunc.removeDeviceList(ipAddr)) ) {
					alert("<?=Monitor\Lang\STR_REMOVE_IPADDR_NONE ?>");
				}

				$("#div_monitor_table_body").html(rc);

				monitorFunc.clear();
				monitorFunc.checkDeviceStatus();

				var rowCnt = $("#div_monitor_table_body").children(".divTableRow").length;
				for( var idx = 0 ; idx < rowCnt ; idx++ ) {
					initEqulizer("outputVolume_" + (idx + 1));
				}

				return ;
			});
		});

		$("#input_monitor_device_ipaddr").keyup(function(_ex) {
			if( _ex.keyCode == 13 ) { // enter key
				if( monitorFunc.checkIpAddr($(this).val()) == false ) {
				$("#input_monitor_device_ipaddr").focus();
				alert("<?=Monitor\Lang\STR_ADD_IPADDR_CHECK ?>");

				return ;
			}

				$("#input_monitor_device_name").focus();

				return ;
			}
		});

		$("#input_monitor_device_name").keyup(function(_ex) {
			if( _ex.keyCode == 13 ) { // enter key
				$("#input_monitor_device_location").focus();
			}

			return ;
		});

		$("#input_monitor_device_location").keyup(function(_ex) {
			if( _ex.keyCode == 13 ) { // enter key
				$("#div_monitor_device_add").trigger("click");
			}

			return ;
		});

		monitorFunc.clear();
		monitorFunc.checkDeviceStatus();

		var rowCnt = $("#div_monitor_table_body").children(".divTableRow").length;
		for( var idx = 0 ; idx < rowCnt ; idx++ ) {
			initEqulizer("outputVolume_" + (idx + 1));
		}

		setInterval(function() {
			// monitorFunc.clear();
			monitorFunc.checkDeviceAsync();
		}, 5000);

	});

	// TODO : onclose, onerror handle, try-catch handle
	class WebSockInfo {
		constructor(_idx, _ipAddr) {
			this.idx    = _idx;
			this.ipAddr = _ipAddr;
			this.sockFd = this.init(this.ipAddr);

			this.open();

			this.recv(this.idx);

			this.sockFd.onclose = function(_msg) {
				return ;
			};

			this.sockFd.onerror = function(_msg) {
				return ;
			};

		}

		init() {
			var hostInfo = "ws://" + this.ipAddr + "/audio_client";
			var webSocketFd = new WebSocket(hostInfo);

			return webSocketFd;
		}

		open() {
			this.sockFd.onopen = function( _msg) {
				return ;
			};

		}
		close() {
			this.sockFd.close();

			return ;
		}

		recv(_idx) {
			this.sockFd.onmessage = function(_msg) {
				var recvMsg = JSON.parse(_msg.data);

				switch( recvMsg.type ) {
					case "-11" :
						break;

					case "1" :
						if( recvMsg.data.stat == 1 ) {
							$("#div_monitor_table_audio_" + _idx).attr("class", "circle_deact");
							$(".level_outputVolume_" + _idx).html(0);

						} else {
							$("#div_monitor_table_audio_" + _idx).attr("class", "circle_act");
						}

						return ;
						break;

					case "12" :
						$("#div_monitor_table_device_" + _idx).attr("class", "circle_act");
						$("#div_monitor_table_audio_" + _idx).attr("class", "circle_act");

						$(".level_outputVolume_" + _idx).html(recvMsg.data.level);
						break;
				}
			}
		}
	} // end of WebSockInfo()

	class MonitorFunc {
		constructor() {
			this.monitorPath = "<?=Monitor\Def\PATH_AJAX_MONITOR_PROCESS ?>";
			this.wsList   = new Array();
			this.statList = new Array();
		}

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

		postArgsAsync(_target, _args, _func) {
			new ajax.xhr.Request(_target, _args, _func, "POST");

			return ;
		}

		clear() {
			for( var idx = 0 ; idx < this.wsList.length ; idx++ ) {
				this.wsList[idx].close();
			}

			this.wsList   = new Array();
			this.statList = new Array();

			return ;
		}

		setDeviceList(_ipaddr, _hostname, _location) {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("type",     "monitor");
			submitArgs	+= this.makeArgs("act",      "set_device");
			submitArgs	+= this.makeArgs("ipaddr",   _ipaddr);
			submitArgs	+= this.makeArgs("hostname", _hostname);
			submitArgs	+= this.makeArgs("location", _location);

			return this.postArgs(this.monitorPath, submitArgs);
		}

		getDeviceList() {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("type",     "monitor");
			submitArgs	+= this.makeArgs("act",      "get_device");

			return this.postArgs(this.monitorPath, submitArgs);
		}

		getDeviceStatusList(_type) {
			var rowCnt = $("#div_monitor_table_body").children(".divTableRow").length;
			var arrIpList = new Array();
			var type = "";

			for( var idx = 0 ; idx < rowCnt ; idx++ ) {

				if( _type == "ipaddr" ) {
					type = "#span_monitor_table_ipaddr_";
					var ipAddr = $(type + (idx + 1)).html().trim();

				} else {
					type = "#div_monitor_table_"+ _type + "_";
					var ipAddr = $(type + (idx + 1)).attr("class");
				}

				arrIpList.push(ipAddr);
			}

			return arrIpList;
		}

		getDeviceIndex(_ipAddr) {
			var rowCnt = $("#div_monitor_table_body").children(".divTableRow").length;
			var arrIpList = new Array();

			for( var idx = 0 ; idx < rowCnt ; idx++ ) {
				var ipAddr = $("#span_monitor_table_ipaddr_" + (idx + 1)).html().trim();

				if( ipAddr == _ipAddr ) break;
			}

			return (idx + 1);
		}

		getDeviceStatus(_ipAddr) {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("type",     "monitor");
			submitArgs	+= this.makeArgs("act",      "get_status");
			submitArgs	+= this.makeArgs("ipaddr",   _ipAddr);

			return this.postArgs(this.monitorPath, submitArgs);
		}

		removeDeviceList(_ipAddr) {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("type",     "monitor");
			submitArgs	+= this.makeArgs("act",      "remove_device");
			submitArgs	+= this.makeArgs("ipaddr",   _ipAddr);

			return this.postArgs(this.monitorPath, submitArgs);
		}

		checkDeviceAlive(_ipList, _syncFlag) {
			var submitArgs = "";

			submitArgs	+= this.makeArgs("type",     "monitor");
			submitArgs	+= this.makeArgs("act",      "check_status");
			submitArgs	+= this.makeArgs("ip_list",  _ipList.join(','));

			if( _syncFlag == true ) {
				return this.postArgs(this.monitorPath, submitArgs);

			} else {
				this.postArgsAsync(this.monitorPath, submitArgs, this.submitedCheck);
			}
		}

		submitedCheck(_req) {
			if( _req.readyState == 4 ) {
				if( _req.status == 200 ) {
					var aliveList     = JSON.parse(_req.responseText);
					var arrIpList     = monitorFunc.getDeviceStatusList("ipaddr");
					var deviceList    = monitorFunc.getDeviceStatusList("device");
					var audioliveList = monitorFunc.getDeviceStatusList("audio");

					for( var idx = 0 ; idx < arrIpList.length ; idx++ ) {
						for( var inIdx = 0 ; inIdx < aliveList.count ; inIdx++ ) {
							if( arrIpList[idx] == aliveList.ipList[inIdx] ) { // act
								var stat = JSON.parse(monitorFunc.getDeviceStatus(arrIpList[idx]));

								if( stat.status == "on" ) {
									$("#div_monitor_table_device_" + (idx + 1)).attr("class", "circle_act");

								} else {
									$("#div_monitor_table_device_" + (idx + 1)).attr("class", "circle_deact");
									$(".level_outputVolume_" + (idx + 1)).html(0);
								}

								if( stat.audio == "on" ) {
									if( $("#div_monitor_table_audio_" + (idx + 1)).attr("class") == "circle_deact" ) {
										$("#div_monitor_table_audio_" + (idx + 1)).attr("class", "circle_act");

										var wsInfo = new WebSockInfo((idx + 1), arrIpList[idx]);
									}

								} else {
									$("#div_monitor_table_audio_" + (idx + 1)).attr("class", "circle_deact");
									$(".level_outputVolume_" + (idx + 1)).html(0);
								}

								break;
							}
						}

						if( inIdx == aliveList.count ) { // deact
							$("#div_monitor_table_device_" + (idx + 1)).attr("class", "circle_deact");
							$("#div_monitor_table_audio_" + (idx + 1)).attr("class", "circle_deact");
							$(".level_outputVolume_" + (idx + 1)).html(0);

						}
					}

				} else {
					//
				}
			}
		}

		checkDeviceAsync() {
			var arrIpList  = monitorFunc.getDeviceStatusList("ipaddr");
			var aliveList  = monitorFunc.checkDeviceAlive(arrIpList, false);

		}

		checkDeviceStatus() {
			var arrIpList  = monitorFunc.getDeviceStatusList("ipaddr");
			var aliveList  = JSON.parse(monitorFunc.checkDeviceAlive(arrIpList, true));

			for( var idx = 0 ; idx < this.wsList.length ; idx++ ) {
				this.wsList[idx].close();
			}
			this.wsList = new Array();

			for( var idx = 0 ; idx < aliveList.count ; idx++ ) {
				var ipAddr   = aliveList.ipList[idx];
				var stat     = JSON.parse(monitorFunc.getDeviceStatus(ipAddr));
				var colIdx   = monitorFunc.getDeviceIndex(ipAddr);
				var loopFlag = false;
				var statFlag = false;

				for( var idx = 0 ; idx < this.statList.length ; idx++ ) {
					if( this.statList[idx].ipaddr == ipAddr ) {
						loopFlag = true;

						if( this.statList[idx].status == stat.status
							&& this.statList[idx].audio == stat.audio ) {

							statFlag = true;

						} else {
							 this.statList[idx].status = stat.status;
							 this.statList[idx].audio  = stat.audio;
						}

						break;
					}
				}
				if( idx == this.statList.length ) {
					this.statList.push(stat);
				}

				if( statFlag == true ) {

					continue;
				}

				if( stat.status == "on" ) {
					$("#div_monitor_table_device_" + colIdx).attr("class", "circle_act");

				} else {
					$("#div_monitor_table_device_" + colIdx).attr("class", "circle_deact");
				}

				if( stat.audio == "on" ) {
					if( $("#div_monitor_table_audio_" + colIdx).attr("class") == "circle_deact" ) {
						$("#div_monitor_table_audio_" + colIdx).attr("class", "circle_act");

						var wsInfo = new WebSockInfo(colIdx, ipAddr);
						this.wsList.push(wsInfo);
					}

				} else {
					$("#div_monitor_table_audio_" + colIdx).attr("class", "circle_deact");
				}
			}

			return ;
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

	} // end of MonitorFunc()

	var monitorFunc = new MonitorFunc();



</script>

<?php
	include_once 'common_js_etc.php';
?>