#!/bin/sh

Xvfb_PIDFILE="/home/tmnz/__xvfb.pid"

if [ -e "/home/tmnz/__xvfb.pid" ]; then
    Xvfb_PID=`cat $Xvfb_PIDFILE`
    if [ "`ps -eo pid | grep -c $Xvfb_PID`" != 1 ]; then
        Xvfb :1 -screen 0 1152x856x24 > /dev/null 2>&1 &
        Xvfb_PID=$!
        echo $Xvfb_PID > $Xvfb_PIDFILE
        echo "Xvfb Started: $Xvfb_PID => $Xvfb_PIDFILE"
    fi
else
    Xvfb :1 -screen 0 1152x856x24 > /dev/null 2>&1 &
    Xvfb_PID=$!
    echo $Xvfb_PID > $Xvfb_PIDFILE
    echo "Xvfb Started: $Xvfb_PID => $Xvfb_PIDFILE"
fi

/home/tmnz/test_harness.pl --source http://example.com/testsuite.txt \
  --profile /home/tmnz/TMNZA \
  --profile /home/tmnz/TMNZB \
  --profile /home/tmnz/TMNZC \
  --profile /home/tmnz/TMNZD \
  --profile /home/tmnz/TMNZE \
  --profile /home/tmnz/TMNZF \
  --wait 45 \
  --display :1

### This is rm is for clean-up of the Google Page Speed optimization output (about:config)
###    user_pref("extensions.PageSpeed.optimized_file_base_dir", "/tmp/tmp.tmnz.pagespeed");
# rm -rf /tmp/tmp.tmnz.pagespeed/*
