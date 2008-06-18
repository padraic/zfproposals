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
        $this->_key = $this->_assembleKey();
        if (isset($hashAlgo)) {
            $this->_hashAlgorithm = $hashAlgo;
        }
    }

    public abstract function sign(array $params);

    protected function _assembleKey() 
    {
        $parts = array($this->_consumerSecret);
        if (!is_null($this->_accessTokenSecret)) {
            $parts[] = $this->_accessTokenSecret;
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
            $baseStrings[] = $this->_urlEncode(strtoupper($method));
        }
        if (isset($url)) {
            $baseStrings[] = $this->_urlEncode($url);
        }
        $encodedParams = array();
        foreach ($params as $key=>$value) {
            $encodedParams[$this->_urlEncode($key)] = $this->_urlEncode($value);
        }
        if (isset($encodedParams['oauth_signature'])) {
            unset($encodedParams['oauth_signature']);
        }
        $keyValuePairs = $this->_toByteValueOrderedKeyValuePairs($encodedParams);
        $baseStrings = $baseStrings + $keyValuePairs;
        return implode('&', $baseStrings);
    }

    protected function _toByteValueOrderedKeyValuePairs(array $params) 
    {
        $return = array();
        uksort($params, 'strnatcmp');
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                natsort($value);
                foreach ($value as $keyduplicate) {
                    $return[] = $key . '=' . $keyduplicate;
                }
            } else {
                $return[] = $key . '=' . $value;
            }
        }
        return $return;
    }

}