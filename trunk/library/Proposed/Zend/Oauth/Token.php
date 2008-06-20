<?php

abstract class Zend_Oauth_Token
{

    const TOKEN_PARAM_KEY = 'oauth_token';

    protected $_params = array();

    public abstract function isValid();

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

}