<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://rawgit.com/binaryjs/binaryjs/master/dist/binary.min.js"></script>

<script>
	function int16ToFloat32(_inputArray, _startIndex, _length) {
		var output = new Float32Array(_inputArray.length - _startIndex);
		for( var i = _startIndex ; i < _length ; i++ ) {
			var int = _inputArray[i];
			// If the high bit is on, then it is a negative number, and actually counts backwards.
			var float = (int >= 0x8000) ? - (0x10000 - int) / 0x8000 : int / 0x7FFF;
			output[i] = float;
		}

		return output;
	}

	var context = new (window.AudioContext || window.webkitAudioContext)();
	var delayTime = 0;
	var init = 0;
	var audioStack = [];
	var bufferSize = 512;

	function playPcm(_data) {
		var aData = new Int16Array(_data);

		audioStack.push(aData);
	}

	var node = context.createBufferSource()
  , buffer = context.createBuffer(1, 4096, 44100)
  , data = buffer.getChannelData(0);

	for (var i = 0; i < 4096; i++) {
	 data[i] = Math.random();
	}
	console.log(context.sampleRate);
	node.buffer = buffer;
	node.connect(context.destination);
	node.start(0);

	node.onaudioprocess = function(e) {
		var output = e.outputBuffer.getChannelData(0);
		var buffer = getAudioStack();

		for (var i = 0; i < 4096; i++) {
			 output[i] = Math.random();
			}
	}



	function sendMsgToProc(_sockFd, _cmdId, _bodyString) {
		if( _bodyString == null ) _bodyString = "";
		var arrBodyString = new Uint8Array(_bodyString.length);

		for( idx = 0 ; idx < _bodyString.length ; idx++ ) {
			arrBodyString[idx] = _bodyString.charCodeAt(idx);
		}

		var arrSendMsg = new Uint8Array(5 + _bodyString.length);

		arrSendMsg.set([_cmdId]);
		arrSendMsg.set([_bodyString.length], 1, 4);
		if( _bodyString.length > 0 ) {
			arrSendMsg.set(arrBodyString, 5, _bodyString.length);
		}

		_sockFd.send(arrSendMsg);

		return ;
	}

	class WebSockInfo {
		constructor(_ipAddr, _url) {
			this.ipAddr = _ipAddr;
			this.url    = _url;
			this.sockFd = this.init();

			this.open();
			this.recv(this.url);

			this.sockFd.onclose = function(_msg) {
				return ;
			};

			this.sockFd.onerror = function(_msg) {
				return ;
			};

		}

		init() {
			var hostInfo = "ws://" + this.ipAddr + "/" + this.url;
			var webSocketFd = new WebSocket(hostInfo);
			webSocketFd.binaryType = 'arraybuffer';

			return webSocketFd;
		}

		open() {
			var sockFd = this.sockFd;

			this.sockFd.onopen = function( _msg) {
				sendMsgToProc(sockFd, 1, null);

				return ;
			};

		}
		close() {
			this.sockFd.close();

			return ;
		}

		recv(_url) {
			this.sockFd.onmessage = function(_msg) {
				if( typeof _msg.data != "string") {
					playPcm(_msg.data);
					return ;
				}
			}
		}
	} // end of WebSockInfo()

	var wsRawData = new WebSockInfo("192.168.47.153", "audio_rt_data");
</script>