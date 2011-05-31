#!/bin/bash

SHOWSLOWBASE='http://www.showslow.com'

URLS=`wget $SHOWSLOWBASE/monitor.php?new -O - -q`

for URL in $URLS
do
	echo "$URL" | curl "$SHOWSLOWBASE/beacon/pagespeed/?api" -G --data-urlencode u@-
done

