<?php
    echo "\nsource file management module init script start \n";

    umask(0);
    posix_mkfifo("/tmp/pipe_audio_player_read",  0666);
    posix_mkfifo("/tmp/pipe_audio_player_write", 0666);

    echo "\nsource file management module init script end \n";
?>

