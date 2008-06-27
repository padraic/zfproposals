<?php

require_once 'Zend/Oauth/Http/Utility.php';

abstract class Zend_Oauth_Token
{

    const TOKEN_PARAM_KEY = 'oauth_token';

    const TOKEN_SECRET_PARAM_KEY = 'oauth_token_secret';

    protected $_params = array();

    protected $_response = null;

    protected $_httpUtility = null;

    public function __construct(Zend_Http_Response $response = null,
        Zend_Oauth_Http_Utility $utility = null)
    {
        if (!is_null($response)) {
            $this->_response = $response;
            $params = $this->_parseParameters($response);
            if (count($params) > 0) {
                $this->setParams($params);
            }
        }
        if (!is_null($utility)) {
            $this->_httpUtility = $utility;
        } else {
            $this->_httpUtility = new Zend_Oauth_Http_Utility;
        }
    }

    public function isValid()
    {
        if (isset($this->_params[self::TOKEN_PARAM_KEY])
            && !empty($this->_params[self::TOKEN_PARAM_KEY])
            && isset($this->_params[self::TOKEN_SECRET_PARAM_KEY])) {
            return true;
        }
        return false;
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

    public function setParam($key, $value)
    {
        $this->_params[$key] = trim($value, "\n");
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
        return $this->_httpUtility->toEncodedQueryString($this->_params);
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