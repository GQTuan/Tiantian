<?php

/**
 * Class DESCrypt
 *
 * DES 加解密类
 */
class DES
{
    /**
     * @param $text
     * @param $blocksize
     *
     * @return string
     */
    private static function pkcs5_pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    /**
     * @param $text
     *
     * @return bool|string
     */
    private static function pkcs5_unpad($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }
        return substr($text, 0, -1 * $pad);
    }

    /**
     * @param $key
     * @param $data
     *
     * @return string
     */
    public static function encrypt($key, $data)
    {
        $size = mcrypt_get_block_size('des', 'ecb');
        $data = self::pkcs5_pad($data, $size);
        $td = mcrypt_module_open('des', '', 'ecb', '');
        $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        @mcrypt_generic_init($td, $key, $iv);
        $data = mcrypt_generic($td, $data);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return strtoupper(bin2hex($data));
        //return base64_encode($data);
        // return $data;
    }

    /**
     * @param $key
     * @param $data
     *
     * @return bool|string
     */
    public static function decrypt($key, $data)
    {
        $data = hex2bin(strtolower($data));

        $td = mcrypt_module_open('des', '', 'ecb', '');
        $iv = @mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        $ks = mcrypt_enc_get_key_size($td);
        @mcrypt_generic_init($td, $key, $iv);
        $decrypted = mdecrypt_generic($td, $data);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $result = self::pkcs5_unpad($decrypted);
        return $result;
    }
}