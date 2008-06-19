<?php

abstract class Zend_Oauth_Signature_Abstract
{

    protected $_hashAlgorithm = null;

    protected $_key = null;

    protected $_consumerSecret = null;

    protected $_accessTokenSecret = '';

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

    public abstract function sign(array $params, $method = null, $url = null);

    protected function _assembleKey() 
    {
        $parts = array($this->_consumerSecret);
        if (!is_null($this->_accessTokenSecret)) {
            $parts[] = $this->_accessTokenSecret;
        }
        foreach ($parts as $key=>$secret) {
            $parts[$key] = Zend_Oauth::urlEncode($secret);
        }
        return implode('&', $parts);
    }

    protected function _urlEncode($string) 
    {
        return Zend_Oauth::urlEncode($string);
    }

    protected function _getBaseSignatureString(array $params, $method = null, $url = null) 
    {
        $baseStrings = array();
        if (isset($method)) {
            $baseStrings[] = strtoupper($method);
        }
        if (isset($url)) {
            // should normalise later
            $baseStrings[] = Zend_Oauth::urlEncode($url);
        }
        if (isset($params['oauth_signature'])) {
            unset($params['oauth_signature']);
        }
        $baseStrings[] = Zend_Oauth::urlEncode(
            $this->_toByteValueOrderedQueryString($params)
        );
        return implode('&', $baseStrings);
    }

    protected function _toByteValueOrderedQueryString(array $params) 
    {
        $return = array();
        uksort($params, 'strnatcmp');
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                // I think this could be wrong reading "5. Parameters"
                natsort($value);
                foreach ($value as $keyduplicate) {
                    $return[] = $key . '=' . $keyduplicate;
                }
            } else {
                $return[] = $key . '=' . $value;
            }
        }
        return implode('&', $return);
    }

}