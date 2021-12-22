#!/bin/bash

if [ $# -ne 1 ]; then
        echo '{"code":211, "message":"invalid arguments"}'
        exit 211
fi

if [ -e $1 ]; then
        check_string=`strings $1 | head -2 | tail -n 1 | grep version | wc -l`
else
        echo '{"code":212, "message":"file not exist"}'
        exit 212
fi

if [ $check_string -eq 0 ]; then
        echo '{"code":213, "message":"invalid header format"}'
        exit 213
fi

src=`strings $1 | head -2 | tail -n 1 | python -c "import json,sys;obj=json.load(sys.stdin);print obj['version'];" | cut -f 1 -d .`
dest=`cat /opt/interm/conf/env.json | python -c "import json,sys;obj=json.load(sys.stdin);print obj['device']['version'];" | cut -f 1 -d .`

if [ $src -eq $dest ]; then
        echo '{"code":0, "message":"valid firmware file"}'
        exit_code=0
else
        echo '{"code":210, "message":"invalid firmware file"}'
        exit_code=210
fi

exit $exit_code
