<?php

require_once 'Zend/Oauth.php';

class Zend_Oauth_Http_Utility
{

    public function assembleParams($url, Zend_Oauth_Consumer $consumer, array $serviceProviderParams = null)
    {
        $params = array();
        $params['oauth_consumer_key'] = $consumer->getConsumerKey();
        $params['oauth_nonce'] = $consumer->generateNonce();
        $params['oauth_signature_method'] = $consumer->getSignatureMethod();
        $params['oauth_timestamp'] = $consumer->generateTimestamp();
        $params['oauth_token'] = $this->getToken();
        $params['oauth_version'] = $consumer->getVersion();
        if (!is_null($serviceProviderParams)) {
            $params = array_merge($params, $serviceProviderParams);
        }
        $params['oauth_signature'] = $consumer->sign(
            $params,
            $consumer->getSignatureMethod(),
            $consumer->getConsumerSecret(),
            $this->getTokenSecret(),
            $consumer->getRequestMethod(),
            $url
        );
        return $params;
    }

    public function toEncodedQueryString(array $params) 
    {
        $encodedParams = array();
        foreach ($params as $key => $value) {
            $encodedParams[] =
                Zend_Oauth::urlEncode($key) . '=' . Zend_Oauth::urlEncode($value);
        }
        return implode('&', $encodedParams);
    }

    public function toAuthorizationHeader(array $params, $realm = null)
    {
        $headerValue = array();
        $headerValue[] = 'OAuth realm="' . $realm . '"';
        foreach ($params as $key => $value) {
            $headerValue[] =
                Zend_Oauth::urlEncode($key)
                . '="'
                . Zend_Oauth::urlEncode($value)
                . '"';
        }
        return implode(",", $headerValue);
    }
}