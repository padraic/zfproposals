<?php

require_once 'Zend/Crypt/Rsa/Key.php';

class Zend_Crypt_Rsa_Key_Public extends Zend_Crypt_Rsa_Key
{

    public function __construct($pemString) 
    {
        $this->_pemString = $pemString;
        $this->_opensslKeyResource = openssl_get_publickey($this->_pemString);
    }

}