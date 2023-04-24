<?php

namespace OPEN;

/**
 * Class OPEN.
 */

class OPEN
{

    /**
     *  @var string The OPEN api key to be used for requests. 
     */
    public static $apiKey;

    /** 
     * @var string The OPEN secret key to be used for requests. 
     */
    public static $secretKey;

    /** 
     * @var string The base URL for the OPEN API. 
     */
    public static $apiBase = 'http://172.23.160.1:8080/public/api/v2/'; //'https://api.openfuture.io';

    public static function getApiKey()
    {
        return self::$apiKey;
    }

    public static function getSecretKey()
    {
        return self::$secretKey;
    }

    public static function setApiKey($apiKey)
    {
        self::$apiKey = $apiKey;
    }

    public static function setSecretKey($secretKey)
    {
        self::$secretKey = $secretKey;
    }

    /**
     * @param  integer Blockchain type (60 -> Ethereum, 0 -> Bitcoin)
     * @param  boolean Save Address on Open State
     * @return array   Returns array of generated address and it's private key
     * 
     * After wallet address generation, address will be sent to Open State for further actions 
     * like transaction tracking and invoke webHook if happened
     */
    public function generateWallet($coinType = 60, $saveAddress = false)
    {
        $generatedWallet = wallet_generate($coinType);
        $address = $generatedWallet[0];
        if ($saveAddress)
            self::prepare_request("wallet/save", $address, $coinType, "");
        return $generatedWallet;
    }

    /**
     * @param  string  Private key
     * @param  integer Blockchain type (60 -> Ethereum, 0 -> Bitcoin)
     * @return string  Returns address
     */
    public function importWallet($privateKey, $coinType)
    {
        return wallet_import($privateKey, $coinType);
    }

    /**
     * @param  string  Address
     * @param  integer Blockchain Network 
     * @return string  Returns nonce of address on current network
     */
    public function getNonce($address, $network)
    {
        return self::prepare_request("wallet/fetch", $address, $network, "");
    }

    /**
     * @param  string  Private key
     * @param  string  Address
     * @param  string  Chain id (ETH Mainnet - 1, BSC Mainnet - 56, Goerli Testnet - 5)
     * @param  string  Gas price
     * @param  string  Gas limit
     * @param  string  Amount
     * @param  string  Nonce
     * @return string  Returns signature of transaction
     */
    public function sign($privateKey, $address, $chainId, $gasPrice, $gasLimit, $amount, $nonce)
    {
        return wallet_sign($privateKey, $address, $chainId, $gasPrice, $gasLimit, $amount, $nonce);
    }

    /**
     * @param  string  Signed Transaction
     * @param  integer Coin type
     * @return string  Returns hash of succesfull broadcast
     */
    public function broadcast($signedTransaction, $coinType = 60)
    {
        return self::prepare_request("wallet/broadcast", $signedTransaction, $coinType, "", "");
    }

    /**
     * @param  string  Private Key
     * @param  string  Password for encryption
     * @return string  Returns encrypted data
     */
    public function encrypt($privateKey, $password)
    {
        return wallet_encrypt($privateKey, $password);
    }

    /**
     * @param  string  Encrypted data
     * @param  string  Password for decryption
     * @return string  Returns decrypted data
     */
    public function decrypt($data, $password)
    {
        return wallet_decrypt($data, $password);
    }

    /**
     * @param  string  User id
     * @param  string  Webhook address - if not empty will be invoked this address on open state
     * @param  array   CoinTypes (0, 60)
     * @param  string  Password - if not empty, private keys will be saved in encrypted format
     * @param  array   Metadata - Optional, any metadata information
     * @param  boolean Test - if true then will track transactions from test networks
     * @return array   Returns array of generated wallets
     */
    public function generateUserWallet($userId, $webHookAddress, $coinTypes = array(), $isTest = false)
    {
        $blockchains = array();
        foreach ($coinTypes as $coinType) {
            array_push($blockchains, self::get_coin($coinType));
        }
        $metadata = array(
            'bsf' => "test",
            'alt' => "coin"
        );
        $args = array(
            'blockchains' => $blockchains,
            'metadata' => $metadata,
            'test' => $isTest,
            'user_id' => $userId,
            'webhook'   => $webHookAddress
        );
        $hash = self::get_signature($args);

        return self::send_request("wallet/user/generate", $hash, $args);
    }

    /**
     * @param  string  User id
     * @param  string  Webhook address - if not empty will be invoked this address on open state
     * @param  string  Encrypted data
     * @param  string  Address
     * @param  array   Coin (0, 60)
     * @param  array   Any Metadata
     * @return string  Returns imported wallet
     */
    public function importUserWallet($userId, $webHookAddress, $isTest = false, $encryptedData, $address, $coin, $metadata = array())
    {

        $args = array(
            'metadata' => $metadata,
            'test' => $isTest,
            'user_id' => $userId,
            'wallets' => [
                array(
                'address' => $address,
                'blockchain_type' => self::get_coin($coin),
                'encrypted_data' => $encryptedData
                )
            ],
            'webhook'   => $webHookAddress,
        );
        $hash = self::get_signature($args);

        return self::send_request("wallet/user/import", $hash, $args);
    }

    /**
     * @param  string  User id
     * @return array   Returns wallets
     */
    public function getUserWallet($userId)
    {
        $args = array(
            'userId' => $userId
        );

        return self::send_get_request("wallet/user?", $args);
    }


    /**
     * @param  string  Webhook address - if not empty will be invoked this address on open state
     * @param  array   CoinTypes (0, 60)
     * @param  string  Password - if not empty, private keys will be saved in encrypted format
     * @param  boolean Test - if true then will track transactions from test networks
     * @param  string  Order Id
     * @param  string  Amount
     * @param  string  Order Fiat Currency (USD, EUR)
     * @param  array   Any Metadata
     * @return array   Returns array of generated wallets
     */
    public function generateOrderWallet($webHookAddress, $coinTypes = array(), $isTest = false, $orderId, $amount, $orderCurrency, $metadata = array())
    {

        $blockchains = array();
        foreach ($coinTypes as $coinType) {
            array_push($blockchains, self::get_coin($coinType));
        }
        
        $args = array(
            'amount' => $amount,
            'blockchains' => $blockchains,
            'metadata' => $metadata,
            'order_currency' => $orderCurrency,
            'order_id' => $orderId,
            'test' => $isTest,
            'webhook'   => $webHookAddress,
        );
        $hash = self::get_signature($args);

        return self::send_request("wallet/order/generate", $hash, $args);
    }

    /**
     * @param  string  Webhook address - if not empty will be invoked this address on open state
     * @param  string  Encrypted data
     * @param  string  Address
     * @param  string  Order Id
     * @param  string  Amount
     * @param  string  Order Fiat Currency (USD, EUR)
     * @param  array   Any Metadata
     * @return array   Returns array of generated wallets
     */
    public function importOrderWallet($webHookAddress, $orderId, $amount, $isTest = false, $orderCurrency, $encryptedData, $address, $coin, $metadata = array())
    {
        
        $args = array(
            'amount' => $amount,
            'metadata' => $metadata,
            'order_currency' => $orderCurrency,
            'order_id' => $orderId,
            'test' => $isTest,
            'wallets' => [
                array(
                'address' => $address,
                'blockchain_type' => self::get_coin($coin),
                'encrypted_data' => $encryptedData
                )
            ],
            'webhook'   => $webHookAddress,
        );
        
        $hash = self::get_signature($args);

        return self::send_request("wallet/order/import", $hash, $args);
    }

    /**
     * @param  string  Order id
     * @return array   Returns wallets
     */
    public function getOrderWallet($orderId)
    {
        $args = array(
            'orderId' => $orderId
        );

        return self::send_get_request("wallet/order?", $args);
    }

    /**
     * @param  string  Webhook address - if not empty will be invoked this address on open state
     * @param  string  Encrypted data
     * @param  string  Address
     * @param  array   Any Metadata
     * @return array   Returns array of generated wallets
     */
    public function importEncryptedWallet($webHookAddress, $isTest = false, $encryptedData, $address, $coin, $metadata = array())
    {
        
        $args = array(
            'metadata' => $metadata,
            'test' => $isTest,
            'wallets' => [
                array(
                'address' => $address,
                'blockchain_type' => self::get_coin($coin),
                'encrypted_data' => $encryptedData
                )
            ],
            'webhook'   => $webHookAddress,
        );
        
        $hash = self::get_signature($args);

        return self::send_request("wallet/import", $hash, $args);
    }

    public static function get_timestamp(): int
    {
        $currentDate = new \DateTime();
        return $currentDate->getTimestamp();
    }

    public static function prepare_request($url, $address, $coinType, $encryption, $password)
    {
        $args = array(
            'address'   => $address,
            "blockchain" => self::get_coin($coinType),
            'encrypted_data' => $encryption,
        );
        $hash = self::get_signature($args);

        return self::send_request($url, $hash, $args);
    }

    public static function get_signature($args): string
    {
        $jsonString = json_encode($args, JSON_UNESCAPED_SLASHES);
        return hash_hmac('sha256', $jsonString, self::$secretKey);
    }

    public static function send_request(string $endpoint, string $hash, array $params = array())
    {

        $timeStamp = self::get_timestamp();
        $headers = array(
            'X-API-KEY: ' . self::$apiKey,
            'X-API-SIGNATURE: ' . $hash,
            'X-API-TIMESTAMP: ' . $timeStamp,
            'Content-Type: application/json'
        );

        $url = self::$apiBase . $endpoint;

        $payload = json_encode($params, JSON_UNESCAPED_SLASHES);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POST, true);

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }

    public static function send_get_request(string $endpoint, array $params = array())
    {

        $timeStamp = self::get_timestamp();
        $headers = array(
            'X-API-KEY: ' . self::$apiKey,
            'X-API-TIMESTAMP: ' . $timeStamp,
            'Content-Type: application/json'
        );

        $url = self::$apiBase . $endpoint;

        $url .= http_build_query($params, '', '&');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }

    public static function get_coin($coinCode)
    {
        switch ($coinCode) {
            case 0:
                return "BTC";
            case 60:
                return "ETH";
            case 714:
                return "BNB";
            default:
                return "ETH";
        }
    }
}
