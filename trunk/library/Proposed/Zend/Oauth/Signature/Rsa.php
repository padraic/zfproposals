<?php

require_once 'Zend/Oauth/Signature/Abstract.php';

require_once 'Zend/Crypt/Rsa.php';

class Zend_Oauth_Signature_Rsa extends Zend_Oauth_Signature_Abstract
{

    public function sign(array $params, $method = null, $url = null) 
    {
        //var_dump($this->_getBaseSignatureString($params, $method, $url)); echo '<hr>';
        $rsa = new Zend_Crypt_Rsa;
        $rsa->setHashAlgorithm($this->_hashAlgorithm);
        $sign = $rsa->sign(
            $this->_getBaseSignatureString($params, $method, $url),
            $this->_key,
            Zend_Crypt_Rsa::BASE64
        );
        return $sign;
    }

    protected function _assembleKey()
    {
        return $this->_consumerSecret;
    }

}