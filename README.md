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
```

Simple usage looks like:

```php
$open = new OPEN();
$result = $open->generateWallet();
echo $result;
```