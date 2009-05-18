#!/bin/bash
# simple YSlow automation script

for LINK in $@;
do
	echo "Fetching $LINK"
	firefox -no-remote $LINK &
	sleep 30
	killall -9 firefox
	sleep 10
done

