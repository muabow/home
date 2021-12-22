#!/bin/bash

## Ethernet Manager : static 설정된 interface 의 Link Up/Down 에 따라 ip 설정/해제
# static 설정을 dhcpcd5 에 의해 관리시, Link Up/Down 에 따른 Gateway 정보 갱신이 되지 않아 별도로 처리
/opt/interm/bin/ethernetManager.sh &

