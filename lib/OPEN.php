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
    public static $apiBase = 'https://api.openfuture.io/public/api/v1/wallet';

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
     * @param  integer Blockchain type
     * @return string  Returns hash of broadcast
     */
    public function broadcast($signedTransaction, $coinType = 60)
    {
        return self::prepare_request("wallet/broadcast", $signedTransaction, $coinType, "");
    }

    /**
     * @param  string  Private Key
     * @param  string  Password for encryption
     * @param  integer Blockchain type
     * @return string  Returns encrypted data
     */
    public function encrypt($coinType, $privateKey, $password, $saveAddress = false)
    {
        $encrypted = wallet_encrypt($privateKey, $password);

        if ($saveAddress) {
            $address = wallet_import($privateKey, $coinType);
            self::prepare_request("wallet/save", $address, $coinType, $encrypted);
        }

        return $encrypted;
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
     * @param  boolean Test - if true then will track transactions from test networks
     * @return array   Returns array of generated wallets
     */
    public function generateUserWallet($userId, $webHookAddress, $coinTypes = array(), $password, $isTest = false)
    {
        $timeStamp = self::get_timestamp();
        $blockchains = array();
        foreach ($coinTypes as $coinType) {
            array_push($blockchains, self::get_coin($coinType));
        }
        $args = array(
            'blockchains' => $blockchains,
            'master_password' => $password,
            'test' => $isTest,
            'timestamp' => strval($timeStamp),
            'user_id' => $userId,
            'webhook'   => $webHookAddress,
        );
        $hash = self::get_signature($args);

        return self::send_request("user/generate", $hash, $args);
    }

    /**
     * @param  string  User id
     * @param  string  Webhook address - if not empty will be invoked this address on open state
     * @param  string  Encrypted data
     * @param  string  Password
     * @return string  Returns imported wallet
     */
    public function importUserWallet($userId, $webHookAddress, $encryptedData, $password)
    {
        $timeStamp = self::get_timestamp();

        $args = array(
            'encrypted_data' => $encryptedData,
            'master_password' => $password,
            'timestamp' => strval($timeStamp),
            'user_id' => $userId,
            'webhook'   => $webHookAddress,
        );
        $hash = self::get_signature($args);

        return self::send_request("user/import", $hash, $args);
    }

    /**
     * @param  string  Webhook address - if not empty will be invoked this address on open state
     * @param  array   CoinTypes (0, 60)
     * @param  string  Password - if not empty, private keys will be saved in encrypted format
     * @param  boolean Test - if true then will track transactions from test networks
     * @param  string  Order Id
     * @param  string  Amount
     * @param  string  Order Fiat Currency (USD, EUR)
     * @return array   Returns array of generated wallets
     */
    public function generateOrderWallet($webHookAddress, $coinTypes = array(), $password, $isTest = false, $orderId, $amount, $orderCurrency)
    {
        $timeStamp = self::get_timestamp();
        $blockchains = array();
        foreach ($coinTypes as $coinType) {
            array_push($blockchains, self::get_coin($coinType));
        }
        $args = array(
            'amount' => $amount,
            'blockchains' => $blockchains,
            'master_password' => $password,
            'order_currency' => $orderCurrency,
            'order_id' => $orderId,
            'test' => $isTest,
            'timestamp' => strval($timeStamp),
            'webhook'   => $webHookAddress,
        );
        $hash = self::get_signature($args);

        return self::send_request("order/generate", $hash, $args);
    }

    public static function get_timestamp(): int
    {
        $currentDate = new \DateTime();
        return $currentDate->getTimestamp();
    }

    public static function prepare_request($url, $address, $coinType, $encryption)
    {
        $timeStamp = self::get_timestamp();
        $args = array(
            'address'   => $address,
            "blockchain" => self::get_coin($coinType),
            'encrypted' => $encryption,
            'timestamp' => strval($timeStamp),
        );
        $hash = self::get_signature($args);

        return self::send_request($url, $hash, $args);
    }

    public static function get_signature($args): string
    {
        $jsonString = json_encode($args);
        return hash_hmac('sha256', $jsonString, self::$secretKey);
    }

    public static function send_request(string $endpoint, string $hash, array $params = array())
    {

        $headers = array(
            'X-API-KEY: ' . self::$apiKey,
            'X-API-SIGNATURE: ' . $hash,
            'Content-Type: application/json'
        );

        $url = self::$apiBase . $endpoint;

        $payload = json_encode($params);

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
