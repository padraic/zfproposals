<?php

abstract class Zend_Oauth_Signature_Abstract
{

    protected $_hashAlgorithm = null;

    protected $_key = null;

    protected $_consumerSecret = null;

    protected $_accessTokenSecret = null;

    public function __construct($consumerSecret, $accessTokenSecret = null, $hashAlgo = null) 
    {
        $this->_consumerSecret = $consumerSecret;
        if (isset($accessTokenSecret)) {
            $this->_accessTokenSecret = $accessTokenSecret;
        }
        $this->_key = $this->_assembleKey($consumerSecret, $accessTokenSecret);
        if (isset($hashAlgo)) {
            $this->_hashAlgorithm = $hashAlgo;
        }
    }

    public abstract function sign(array $params);

    protected function _assembleKey($consumerSecret, $accessTokenSecret = null) 
    {
        $parts = array($consumerSecret);
        if (!is_null($accessTokenSecret)) {
            $parts[] = $accessTokenSecret;
        }
        foreach ($parts as $key=>$secret) {
            $parts[$key] = $this->_urlEncode($secret);
        }
        return implode('&', $parts);
    }

    protected function _urlEncode($string) 
    {
        $return = urlencode($string);
        // RFC 3986 2.3 (Unreserved Characters)
        $return = str_replace('%7E', '~', $string);
        return $return;
    }

    protected function _getBaseSignatureString(array $params, $method = null, $url = null) 
    {
        $baseStrings = array();
        if (isset($method)) {
            $baseStrings[] = strtoupper($method);
        }
        if (isset($url)) {
            $baseStrings[] = $this->_urlEncode($url);
        }
        foreach ($params as $key=>$value) {
            $value = $this->_urlEncode($value);
            $baseStrings[] = $key . '=' . $value;
        }
        return implode('&', $baseStrings);
    }

}