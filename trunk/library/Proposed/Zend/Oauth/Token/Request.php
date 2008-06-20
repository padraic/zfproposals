<?php

require_once 'Zend/Oauth/Token.php';

class Zend_Oauth_Token_Request extends Zend_Oauth_Token
{

    const TOKEN_SECRET_PARAM_KEY = 'oauth_token_secret';

    protected $_response = null;

    public function __construct(Zend_Http_Response $response = null)
    {
        if (!is_null($response)) {
            $this->_response = $response;
            $this->setParams($this->_parseParameters());
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

    public function isValid()
    {
        if (isset($this->_params[self::TOKEN_PARAM_KEY])
            && !empty($this->_params[self::TOKEN_PARAM_KEY])
            && isset($this->_params[self::TOKEN_SECRET_PARAM_KEY])
            && !empty($this->_params[self::TOKEN_SECRET_PARAM_KEY])) {
            return true;
        }
        return false;
    }

    protected function _parseParameters()
    {
        $params = array();
        $body = $this->_response->getBody();
        if (empty($body)) {
            return;
        }
        $parts = explode('&', $body);
        foreach ($parts as $kvpair) {
            $pair = explode('=', $kvpair);
            $params[urldecode($pair[0])] = urldecode($pair[1]);
        }
        return $params;
    }

}