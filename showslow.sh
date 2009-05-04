#!/bin/baSh

# simple YSlow automation script
# put all your URLs into urls.txt

URLS=`cat urls.txt |xargs echo`

for LINK in $URLS;
do
        echo "Fetching $LINK"
        firefox -no-remote $LINK &
        sleep 30
        killall -9 firefox
        sleep 10
done

