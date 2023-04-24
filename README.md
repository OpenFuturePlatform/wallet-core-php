## Requirements

PHP 5.6.0 and later.

## Dependencies

### Install OPEN Trust Wallet Library

### STEP 1 - Clone repository
```bash
git clone https://github.com/OpenFuturePlatform/wallet-core.git
```

### STEP 2 - Build Open Library
```bash
cd wallet-core/open-platform/core-cpp
cmake . -DWALLET_CORE=../../
make
```

### STEP 3 - Copy Open Library
Copying shared library to "/usr/lib" allows use it from everywhere
```bash
sudo cp libOpenWallet.so /usr/lib
sudo chmod 0755 /usr/lib/libOpenWallet.so
```

### STEP 4 - Install PHP-CPP Library
The PHP-CPP library is a C++ library for developing PHP extensions.
https://github.com/CopernicaMarketingSoftware/PHP-CPP
```bash
cd wallet-core/open-platform/php/php-cpp
make
sudo make install
```

### STEP 5 - Build OPEN PHP Extension

```bash
cd wallet-core/open-platform/php
make
sudo make install	
```

## Getting Started

```php
require_once('lib/OPEN.php');

$open = new OPEN();
$open->setApiKey("op_api_key");
$open->setSecretKey("op_api_secret");
```
## Coin Types
```
0   -> BTC
60  -> ETH
144 -> XRP
195 -> TRX
354 -> DOT
501 -> SOL
714 -> BNB
```

### Generate new blockchain wallet

```php
$result = $open->generateWallet($coinType, $saveAddress);
```

### Import blockchain wallet

```php
$address = $open->importWallet($privateKey, $coinType);
```

### Get Nonce value of address

```php
$nonce = $open->getNonce($address, $network);
```

### Sign transaction

```php
$signedTransaction = $open->sign($privateKey, $address, $chainId, $gasPrice, $gasLimit, $amount, $nonce);
```

### Broadcast transaction

```php
$trxHash = $open->broadcast($signedTransaction, $coinType);
```

### Encrypt data with password

```php
$encryptedData = $open->encrypt($privateKey, $password);
```

### Decrypt data with password

```php
$decryptedData = $open->decrypt($data, $password);
```

### Generate Wallet for User

```php
$wallets = $open->generateUserWallet($userId, $webHookAddress, $coinTypes = array(), $isTest);
```

### Import user Wallet

```php
$wallets = $open->importUserWallet($userId, $webHookAddress, $isTest, $encryptedData, $address, $coinType, $metadata)
```

### Generate Wallet for Order

```php
$wallets = $open->generateOrderWallet($webHookAddress, $coinTypes = array(), $isTest, $orderId, $amount, $orderCurrency, $metadata);
```

### Import Wallet for Order

```php
$wallets = $open->importOrderWallet($webHookAddress, $orderId, $amount, $isTest, $orderCurrency, $encryptedData, $address, $coinType, $metadata);
```