<?php

require_once 'Zend/Oauth/Signature/Abstract.php';

require_once 'Zend/Crypt/Hmac.php';

class Zend_Oauth_Signature_Hmac extends Zend_Oauth_Signature_Abstract
{

    public function sign(array $params) 
    {
        $binaryHash = Zend_Crypt_Hmac::compute(
            $this->_key,
            $this->_hashAlgorithm,
            $this->_getBaseSignatureString($params),
            Zend_Crypt_Hmac::BINARY
        );
        return base64_encode($binaryHash);
    }

}