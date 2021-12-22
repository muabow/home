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

	var bufferSize = 512;
	var audioStack = [];
	var flagInit   = 0;
	var audioContext;

	var audioTemp  = "";


	async function setData(_data) {
		var aData = new Int16Array(_data);

		audioStack.push(aData);

		return ;
	}

	function getAudioStack() {
		if (audioStack.length < 10 && flagInit == 0 ) return null;

		flagInit = 1;
		return audioStack.shift();
	}

	try {
		window.AudioContext = window.AudioContext || window.webkitAudioContext;
		audioContext = new AudioContext();

	} catch(e) {
		alert('Web Audio API is not supported in this browser');
	}

	var myPCMProcessingNode = audioContext.createScriptProcessor(bufferSize, 1, 1);

	myPCMProcessingNode.onaudioprocess = function(e) {
		// console.log(audioContext.sampleRate);
		var output = e.outputBuffer.getChannelData(0);
		var buffer = getAudioStack();

		if( buffer != null && undefined !== buffer && buffer.length ) {

			var convertData = int16ToFloat32(buffer, 0, buffer.length);
			for (var i = 0; i < bufferSize; i++) {
				// Generate and copy over PCM samples.
				output[i] = convertData[i];
			}
		} else {
			console.log("not enough buffer : " + audioStack.length);
			for (var i = 0; i < bufferSize; i++) {
				// Generate and copy over PCM samples.
				output[i] = 0;
			}
		}
	}
	myPCMProcessingNode.connect(audioContext.destination);
	// myPCMProcessingNode.start(0);

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
			this.url	= _url;
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
					setData(_msg.data);
					return ;
				}
			}
		}
	} // end of WebSockInfo()

	var wsRawData = new WebSockInfo("192.168.47.153", "audio_rt_data");

</script>