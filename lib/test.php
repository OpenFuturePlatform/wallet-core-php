<?php

use OPEN\OPEN;

require "OPEN.php";

$open = new OPEN();
$open->setApiKey("op_f/bwk5jkFCI6mrTtTSkZ");
$open->setSecretKey("op_tcAziDFGAqGvtEQKDNh4sSkZw1/qpdP15fFLQNPm");

$metadata = array(
    'bsf' => "test",
    'alt' => "coin"
);
echo "Generate Wallet for coin \n";
$result = $open->generateWallet(60, false);
echo "Address: " . $result[0] . "\n";
echo "Private Key: " . $result[1] . "\n";

$privateKey = $result[1];
$address = $open->importWallet($privateKey, 60);
$encryptedData = $open->encrypt($result[1], "123");
echo "Encrypted Data: " . $encryptedData . "\n";
$decryptedData = $open->decrypt($encryptedData, "123");
echo "Decrypted Data: " . $decryptedData . "\n";

echo "Generate Wallet for ORDER \n";
$result = $open->generateOrderWallet("http://localhost/sample?webhook",  array("60"), true, "order2", "100", "USD", $metadata);
echo "generateOrderWallet: " . $result . "\n";

echo "Import Wallet for ORDER \n";
$result = $open->importOrderWallet("http://localhost/sample?webhook", "order2", "100", true, "USD", "6c58a07817e5d0d14271766e9c93f35d45f65d2cc3824690c34efc564b463a50b2fbb6b2712d67b156a22a029d2353a5c5376f678a3abb0e859ddce1b899f759", "0x88685E52EdaFb299B71692E317626ee51dD628B4", 60, $metadata);
print_r($result);
echo "\n";

echo "Get ORDER Wallet\n";
$result = $open->getOrderWallet("order2");
print_r($result);
echo "\n";

echo "Generate Wallet for USER \n";
$result = $open->generateUserWallet("19", "http://localhost/sample?webhook",  array(714), true);
print_r($result);
echo "\n";

echo "Get USER wallet \n";
$result = $open->getUserWallet("19");
print_r($result);
echo "\n";

echo "Import Wallet for USER \n";
$result = $open->importUserWallet("19", "http://localhost/sample?webhook", true, "6c58a07817e5d0d14271766e9c93f35d45f65d2cc3824690c34efc564b463a50b2fbb6b2712d67b156a22a029d2353a5c5376f678a3abb0e859ddce1b899f759", "0x88685E52EdaFb299B71692E317626ee51dD628B4", 60, $metadata);
print_r($result);
echo "\n";

echo "Import Wallet \n";
$result = $open->importEncryptedWallet("http://localhost/sample?webhook", true, "6c58a07817e5d0d14271766e9c93f35d45f65d2cc3824690c34efc564b463a50b2fbb6b2712d67b156a22a029d2353a5c5376f678a3abb0e859ddce1b899f759", "0x88685E52EdaFb299B71692E317626ee51dD628B4", 60, $metadata);
print_r($result);
echo "\n";
