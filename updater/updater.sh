#!/bin/bash

DIRECTORY=`dirname "$0"`
SCRIPT="$DIRECTORY/tns_update.pl"

cd "$DIRECTORY"

rm -f .shutdown

trap 'touch .shutdown ; killall perl' SIGTERM

while true; do
	ERROR=0
	perl "$SCRIPT" &
	wait -f $! || ERROR=1

	if [ -e .shutdown ]; then
		break
	fi

	if [ "$ERROR" -eq 1 ]; then
		echo "Script failed, waiting 15 seconds..."
		sleep 15 &
		wait $!
	else
		echo "Script successful, waiting 900 seconds..."
		sleep 900 &
		wait $!
	fi

	if [ -e .shutdown ]; then
		break
	fi
done

