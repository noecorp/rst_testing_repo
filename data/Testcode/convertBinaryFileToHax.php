<?php
$fp = fopen("/var/www/iso.log", "rb");     
$data = fread($fp, 4); // 4 is the byte size of a whole on a 32-bit PC.     
$number = unpack("i", $data);     
//echo $number[1]; //displays 500     
print bin2hex($number[1]);
?>