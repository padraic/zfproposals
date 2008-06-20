<?php

require_once 'Zend/Oauth/Token.php';

class Zend_Oauth_Token_Access extends Zend_Oauth_Token
{

    protected $_response = null;

    public function __construct(Zend_Http_Response $response = null)
    {
        if (!is_null($response)) {
            $this->_response = $response;
            $this->setParams($this->_parseParameters($response));
        }
    }

    public function getResponse()
    {
        return $this->_response;
    }

    public function setTokenSecret($secret)
    {
        $this->setParam(self::TOKEN_SECRET_PARAM_KEY, $secret);
    }

    public function getTokenSecret()
    {
        return $this->getParam(self::TOKEN_SECRET_PARAM_KEY);
    }

}