#!/bin/bash

SHOWSLOWBASE='... put your instance URL here ...' # e.g. 'http://www.showslow.com'

URLS=`wget $SHOWSLOWBASE/monitor.php -O - -q`

for URL in $URLS
do
	echo "$URL" | curl "$SHOWSLOWBASE/beacon/pagespeed/?api" -G --data-urlencode u@-
done

