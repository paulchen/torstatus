#!/bin/bash

DIRECTORY=`dirname "$0"`
cd "$DIRECTORY"

./check_torstatus flags torstatus
FLAGS_EXIT=$?

./check_torstatus countries torstatus
COUNTRIES_EXIT=$?

/usr/local/bin/healthcheck.sh --connect --innodb_initialized
HEALTHCHECK_EXIT=$?

EXIT_CODE=$HEALTHCHECK_EXIT
if [ "$FLAGS_EXIT" -gt "$EXIT_CODE" ]; then
	EXIT_CODE=$FLAGS_EXIT
fi
if [ "$COUNTRIES_EXIT" -gt "$EXIT_CODE" ]; then
	EXIT_CODE=$COUNTRIES_EXIT
fi

exit $EXIT_CODE

