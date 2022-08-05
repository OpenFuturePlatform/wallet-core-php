<?php

/**
 * Class OPEN.
 */

class OPEN
{

    /**
     * @return array Returns array of generated address and it's private key
     */
    public function generateWallet()
    {
        return wallet_generate();
    }

    /**
     * @param  string Private key
     * @param  integer Blockchain type (0 -> Ethereum, 1 -> Bitcoin)
     * @return string Returns address
     */
    public function importWallet($privateKey, $coinType)
    {
        return wallet_import($privateKey, $coinType);
    }

    public function sign($privateKey, $address, $chainId, $gasPrice, $gasLimit, $amount)
    {
        return wallet_sign($privateKey, $address, $chainId, $gasPrice, $gasLimit, $amount);
    }
}
