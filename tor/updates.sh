#!/bin/bash

apt-get update > /dev/null 2>&1 || exit 3

COUNT=`apt-get dist-upgrade --dry-run|grep Inst|cut -d " " -f 2|grep -c '^tor'`

echo "$COUNT updates of Tor packages available"

if [ "$COUNT" -gt 0 ]; then
	exit 2
fi

