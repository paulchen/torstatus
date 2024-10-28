#!/bin/bash

DIRECTORY=`dirname "$0"`
SCRIPT="$DIRECTORY/tns_update.pl"

cd "$DIRECTORY"

while true; do
	ERROR=0
	perl "$SCRIPT" || ERROR=1

	if [ "$ERROR" -eq 1 ]; then
		echo "Script failed, waiting 15 seconds..."
		sleep 15
	else
		echo "Script successful, waiting 900 seconds..."
		sleep 900
	fi
done

