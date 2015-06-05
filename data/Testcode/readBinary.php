<?php
$filename = '/var/www/iso.log';
$buffer = file_get_contents($filename);
$length = filesize($filename);

if (!$buffer || !$length) {
  die("Reading error\n");
}

$_buffer = '';
for ($i = 0; $i < $length; $i++) {
  $_buffer .= sprintf("%08b", ord($buffer[$i]));
}

var_dump($_buffer);