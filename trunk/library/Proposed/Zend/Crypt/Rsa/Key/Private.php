<?php

require_once 'Zend/Crypt/Rsa/Key.php';

class Zend_Crypt_Rsa_Key_Private extends Zend_Crypt_Rsa_Key
{

    protected $_publicKey = null;

    public function __construct($pemString) 
    {
        $this->_pemString = $pemString;
        $this->_opensslKeyResource = openssl_get_privatekey($pemString);
        $this->_details = openssl_pkey_get_details($this->_opensslKeyResource);
    }

    public function getPublicKey() 
    {
        if (is_null($this->_publicKey)) {
            $this->_publicKey = new Zend_Crypt_Rsa_Key_Public($this->_details['key']);
        }
        return $this->_publicKey;
    }

}