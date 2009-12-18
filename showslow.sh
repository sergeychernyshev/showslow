#!/bin/bash
# simple YSlow and Page Speed automation script

for LINK in $@;
do
	echo "Fetching $LINK"

	firefox -P yslow -no-remote $LINK &
	sleep 30
	killall -9 firefox
	sleep 10

	firefox -P pagespeed -no-remote $LINK &
	sleep 30
	killall -9 firefox
	sleep 10
done

