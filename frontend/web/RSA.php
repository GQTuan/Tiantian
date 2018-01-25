<?php

/**
 * RSA 加解密
 *
 * Class RsaCrypt
 */
class RSA
{
    // 公钥和私钥的一些配置
    private $options = [];

    //保存例实例在此属性中
    private static $_instance;

    /**
     * 私有构造函数，防止外界实例化对象
     * RsaCrypt constructor.
     *
     * @param $options
     *
     * @throws \Exception
     */
    private function __construct($options)
    {
        if (empty($options) || !is_array($options)) {
            throw new \Exception('配置不能为空');
        }
        $this->options = $options;

        $this->options['private_key'] = $this->getCertData($this->options['private_key']);
        $this->options['public_key'] = $this->getCertData($this->options['public_key']);
    }

    /**
     * @param $path
     *
     * @return string
     */
    private function getCertData($path)
    {
        $data = file_get_contents($path);
        return $data;
    }

    /**
     * 私有克隆函数，防止外办克隆对象
     */
    private function __clone()
    {
    }

    /**
     * 静态方法，单例统一访问入口
     *
     * @param $options
     *
     * @return RSA
     */
    public static function getInstance($options = [])
    {
        if (is_null(self::$_instance) || isset (self::$_instance)) {
            self::$_instance = new self ($options);
        }
        return self::$_instance;
    }

    /**
     * 获取私钥内容
     *
     * @return mixed
     * @throws \Exception
     */
    public function getPrivateKey()
    {
        if (empty($this->options['private_key'])) {
            throw new \Exception('私钥为空');
        }
        extension_loaded('openssl') or die('php需要openssl扩展支持');

        openssl_pkcs12_read($this->options['private_key'], $certs, $this->options['private_key_pwd']);

        $private_key = openssl_pkey_get_private($certs['pkey']);
        return $private_key;
    }

    /**
     * 获取公钥内容
     *
     * @return mixed
     * @throws \Exception
     */
    public function getPublicKey()
    {
        if (empty($this->options['public_key'])) {
            throw new \Exception('请配置公钥');
        }
        extension_loaded('openssl') or die('php需要openssl扩展支持');

        openssl_pkcs12_read($this->options['private_key'], $certs, $this->options['private_key_pwd']);

        $public_key = openssl_pkey_get_public($certs['cert']);

        return $public_key;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getCertKey()
    {
        if (empty($this->options['public_key'])) {
            throw new \Exception('请配置公钥');
        }
        extension_loaded('openssl') or die('php需要openssl扩展支持');

        openssl_pkcs12_read($this->options['private_key'], $certs, $this->options['private_key_pwd']);
        
        return str_replace(['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----', "\n"], '', $certs['cert']);
    }

    /**
     * 私钥加密
     *
     * @param string $data 要加密的数据
     * @param string $private_key
     *
     * @return string 加密后的字符串
     */
    public function privateKeyEncode($data, $private_key = '')
    {
        $encrypted = '';
        if (!$private_key) {
            $private_key = self::getPrivateKey();
        }

        try {
            openssl_private_encrypt($data, $encrypted, $private_key);
            return base64_encode($encrypted); //序列化后base64_encode
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * 公钥加密
     *
     * @param string $data 要加密的数据
     * @param string $public_key
     *
     * @return string 加密后的字符串
     */
    public function publicKeyEncode($data, $public_key = '')
    {
        $encrypted = '';
        if (!$public_key) {
            $public_key = self::getPublicKey();
        }

        try {
            openssl_public_encrypt($data, $encrypted, $public_key);
            return base64_encode($encrypted);
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * 用公钥解密私钥加密内容
     *
     * @param string $data 要解密的数据
     * @param string $public_key
     *
     * @return bool|string 解密后的字符串
     */
    public function decodePrivateEncode($data, $public_key = '')
    {
        $decrypted = '';

        if (!$public_key) {
            $public_key = self::getPublicKey();
        }
        try {

            openssl_public_decrypt(base64_decode($data), $decrypted, $public_key); //私钥加密的内容通过公钥可用解密出来
            return $decrypted;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * 用私钥解密公钥加密内容
     *
     * @param string $data 要解密的数据
     * @param string $private_key
     *
     * @return bool|string 解密后的字符串
     */
    public function decodePublicEncode($data, $private_key = '')
    {
        $decrypted = '';
        if (!$private_key) {
            $private_key = self::getPrivateKey();
        }

        try {
            openssl_private_decrypt(base64_decode($data), $decrypted, $private_key); //私钥解密
            return $decrypted;
        } catch (\Exception $exception) {
            return false;
        }
    }
}