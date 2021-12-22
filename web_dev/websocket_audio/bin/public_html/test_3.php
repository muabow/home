<script>


	var context;
var saved;

try {
    context = new (window.AudioContext || window.webkitAudioContext)();
}
catch (e) {
    console.log("Your browser doesn't support Web Audio API");
}

if (saved) {
    playSound(saved);
} else {
    loadSound();
}

//loading sound into the created audio context
function loadSound() {
    //set the audio file's URL
    var audioURL = '/t.mp3';

    //creating a new request
    var request = new XMLHttpRequest();
    request.open('GET', audioURL, true);
    request.responseType = 'arraybuffer';
    request.onload = function () {
    	console.log(request.response);
        //take the audio from http request and decode it in an audio buffer
        context.decodeAudioData(request.response, function (buffer) {

        	console.log(buffer);
            // save buffer, to not load again
            saved = buffer;
            // play sound
            playSound(buffer);
        });
    };
    request.send();
}

//playing the audio file
function playSound(buffer) {
    //creating source node
    var source = context.createBufferSource();
    //passing in data
    source.buffer = buffer;
    //giving the source which sound to play
    source.connect(context.destination);
    //start playing
    source.start(0);
}

</script>