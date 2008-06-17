<?php

require_once 'Zend/Oauth/Signature/Abstract.php';

class Zend_Oauth_Signature_Plaintext extends Zend_Oauth_Signature_Abstract
{

    public function sign(array $params) 
    {
        if (is_null($this->_accessTokenSecret)) {
            return $this->_consumerSecret;
        }
        $return = implode('&', array($this->_consumerSecret,$this->_accessTokenSecret));
        return $return;
    }

}