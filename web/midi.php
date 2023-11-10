<?php

declare(strict_types=1);


$out = stream_socket_client("udp://127.0.0.1:8123", $errno, $errstr, 1);
if (!$out) {
    die("ERREUR OUT: $errno - $errstr<br />\n");
}
stream_set_timeout($out, 1);


$in = stream_socket_server("udp://127.0.0.1:8321", $errno, $errstr,  STREAM_SERVER_BIND);
if (!$in) {
    die("ERREUR IN : $errno - $errstr<br />\n");
}
stream_set_timeout($in, 1);

$start = microtime(true);
fwrite($out, pack("C*", 0b10010000, 0b0001000,0b01111111));
$duration = microtime(true) - $start;
echo "Sent $duration\n";

$start = microtime(true);
$data = fread($in, 3);
$duration = microtime(true) - $start;
echo "Accepted $duration\n";

$bytes= unpack("C*", $data);
echo "Received: " . implode(", ", $bytes) . "\n";
