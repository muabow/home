#!/bin/bash

export LD_LIBRARY_PATH=/opt/interm/usr/lib:./lib

sleep 10
/opt/interm/public_html/modules/system_management/bin/log_system_check
