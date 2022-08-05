<?php

require "../lib/OPEN.php";

echo "\nTesting OPEN generate wallet\n";
$open = new OPEN();
$result = $open->generateWallet();

echo "Address: ". $result[0]  . "\n";
echo "Private Key: ". $result[1]  . "\n";

echo "\nTesting OPEN import wallet\n";
$prvKey = "0x15e9df2c39a3d2b12f9e72e23cabeaccd2cd25255a816f4d0b30e39188e3ece4";
$address = $open->importWallet($result[1], 1);
echo $address . "\n";