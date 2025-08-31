<?php

namespace App\Traits;

trait CcAveuneTrait
{
    public function hextobin($hexString): string
    {
        $length = mb_strlen($hexString);
        $binString = '';
        $count = 0;
        while ($count < $length) {
            $subString = mb_substr($hexString, $count, 2);
            $packedString = pack('H*', $subString);
            if ($count === 0) {
                $binString = $packedString;
            } else {
                $binString .= $packedString;
            }

            $count += 2;
        }

        return $binString;
    }

    public function encrypt($plainText, $key): string
    {
        $key = $this->hextobin(md5($key));
        $initVector = pack('C*', 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0A, 0x0B, 0x0C, 0x0D, 0x0E, 0x0F);
        $openMode = openssl_encrypt($plainText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);

        return bin2hex($openMode);
    }

    /*
    * @param1 : Encrypted String
    * @param2 : Working key provided by CCAvenue
    * @return : Plain String
    */
    public function decrypt($encryptedText, $key)
    {
        $key = hextobin(md5($key));
        $initVector = pack('C*', 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0A, 0x0B, 0x0C, 0x0D, 0x0E, 0x0F);
        $encryptedText = hextobin($encryptedText);

        return openssl_decrypt($encryptedText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
    }
}
