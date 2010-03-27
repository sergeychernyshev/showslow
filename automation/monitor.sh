#!/bin/sh

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
