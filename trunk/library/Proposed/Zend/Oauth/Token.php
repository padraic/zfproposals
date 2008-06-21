<?php

abstract class Zend_Oauth_Token
{

    const TOKEN_PARAM_KEY = 'oauth_token';

    const TOKEN_SECRET_PARAM_KEY = 'oauth_token_secret';

    protected $_params = array();

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
        $this->setParam(self::TOKEN_PARAM_KEY, $token);
    }

    public function getToken()
    {
        return $this->getParam(self::TOKEN_PARAM_KEY);
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

    protected function _parseParameters(Zend_Http_Response $response)
    {
        $params = array();
        $body = $response->getBody();
        if (empty($body)) {
            return;
        }
        $parts = explode('&', $body);
        foreach ($parts as $kvpair) {
            $pair = explode('=', $kvpair);
            $params[rawurldecode($pair[0])] = rawurldecode($pair[1]);
        }
        return $params;
    }

}