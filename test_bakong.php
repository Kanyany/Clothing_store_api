<?php

$ch = curl_init('https://api-bakong.nbc.gov.kh');

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

$result = curl_exec($ch);

echo "========== RESULT ==========\n";
var_dump($result);

echo "\n========== ERROR ==========\n";
var_dump(curl_error($ch));

echo "\n========== INFO ==========\n";
var_dump(curl_getinfo($ch));

curl_close($ch);
