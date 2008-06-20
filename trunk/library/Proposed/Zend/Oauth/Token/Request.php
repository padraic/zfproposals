<?php

require_once 'Zend/Oauth/Token.php';

class Zend_Oauth_Token_Request extends Zend_Oauth_Token
{

    protected $_response = null;

    protected $_params = array();

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

    public function setParam($key, $value)
    {
        $this->_params[$key] = $value;
    }

    public function setParams(array $params)
    {
        foreach ($params as $key=>$value) {
            $this->setParam($key, $value);
        }
    }

    public function getParam($key)
    {
        if (isset($this->_params[$key])) {
            return $this->_params[$key];
        }
        return null;
    }

    public function setToken($token)
    {
        $this->setParam('oauth_token', $token);
    }

    public function getToken()
    {
        return $this->getParam('oauth_token');
    }

    public function setTokenSecret($secret)
    {
        $this->setParam('oauth_token_secret', $secret);
    }

    public function getTokenSecret()
    {
        return $this->getParam('oauth_token_secret');
    }

    public function isValid()
    {
        if (isset($this->_params['oauth_token'])
            && !empty($this->_params['oauth_token'])
            && isset($this->_params['oauth_token_secret'])
            && !empty($this->_params['oauth_token_secret'])) {
            return true;
        }
        return false;
    }

    public function __get($key)
    {
        return $this->getParam($key);
    }

    public function __set($key, $value)
    {
        $this->setParam($key, $value);
    }

    public function toString()
    {
        $baseStrings = array();
        foreach ($this->_params as $key=>$value) {
            $baseStrings[] = Zend_Oauth::urlEncode($key)
                . '=' . Zend_Oauth::urlEncode($value);
        }
        return implode('&', $baseStrings);
    }

    public function __toString()
    {
        return $this->toString();
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