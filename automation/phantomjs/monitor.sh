#!/bin/bash

SHOWSLOWBASE='... put your instance URL here ...' # e.g. 'http://www.showslow.com'

URLS=`wget $SHOWSLOWBASE/monitor.php -O - -q`

# Download and install PhantomJS: http://phantomjs.org/download.html
PHANTOMJS='... path to phantomjs executable ...'

# Download the latest build of phantomjs version of yslow: https://github.com/marcelduran/yslow/downloads
YSLOWJS='... path to yslow.js ...'

for URL in $URLS
do
	$PHANTOMJS $YSLOWJS -i grade -b $SHOWSLOWBASE/beacon/yslow/ $URL >/dev/null
done

