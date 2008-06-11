<?php

// Zend_Crypt_Rsa
// Depends: ext/openssl

class Zend_Crypt_Rsa
{

    protected $_privateKey = null;

    protected $_publicKey = null;

    protected $_pemString = null;

    protected $_pemPath = null;

    protected $_pemUrl = null;

    protected $_hashAlgorithm = 'sha1';

    public function __construct(array $options = null)
    {
        if (isset($options)) {
            $this->setOptions($options);
        }
    }

    public function setOptions(array $options)
    {
        foreach ($options as $option=>$value) {
            switch ($option) {
                case 'pemString':
                    $this->setPemString($value);
                    break;
                case 'pemPath':
                    $this->setPemPath($value);
                    break;
                case 'pemUrl':
                    $this->setPemUrl($value);
                    break;
                case 'hashAlgorithm':
                    $this->setHashAlgorithm($value);
                    break;
            }
        }
    }

    public function createSignature($data)
    {
        return $this->encrypt(
            $this->_hash($data)
        );
    }

    public function encrypt($data)
    {
        $key = $this->getPublicKey();

    }

    public function setPemString($value)
    {
        $this->_pemString = $value;
        $this->_privateKey = openssl_get_privatekey($this->_pemString);
        $details = openssl_pkey_get_details($this->_privateKey);
        $this->_publicKey = $details['key'];
    }

    public function setPemPath($value)
    {
        $this->_pemPath = $value;
        $this->setPemString(file_get_contents($this->_pemPath));
    }

    public function setPemUrl($value)
    {
        $this->_pemUrl = $value;
    }

    public function setHashAlgorithm($name)
    {
        $this->_hashAlgorithm = $name;
    }

    public function getPemString()
    {
        return $this->_pemString;
    }

    public function getPemPath()
    {
        return $this->_pemPath;
    }

    public function getPemUrl()
    {
        return $this->_pemUrl;
    }

    public function getHashAlgorithm()
    {
        return $this->_hashAlgorithm;
    }

    public function getPrivateKey()
    {
        return $this->_privateKey;
    }

    public function getPublicKey()
    {
        return $this->_publicKey;
    }

}