<?php

array_shift($argv);
foreach($argv as $filename) {
  $data = file_get_contents($filename);
  printf("%08x\t$filename\n", crc32($data));
}

