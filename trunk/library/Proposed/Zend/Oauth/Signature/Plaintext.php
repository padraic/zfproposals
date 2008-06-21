<?php

require_once 'Zend/Oauth/Signature/Abstract.php';

class Zend_Oauth_Signature_Plaintext extends Zend_Oauth_Signature_Abstract
{

    public function sign(array $params, $method = null, $url = null)
    {
        if (is_null($this->_tokenSecret)) {
            return $this->_consumerSecret;
        }
        $return = implode('&', array($this->_consumerSecret, $this->_tokenSecret));
        return $return;
    }

}