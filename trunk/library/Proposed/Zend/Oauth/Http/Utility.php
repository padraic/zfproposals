<?php

require_once 'Zend/Oauth.php';

require_once 'Zend/Oauth/Http.php';

class Zend_Oauth_Http_Utility
{

    public function assembleParams($url, Zend_Oauth_Config_Interface $config, array $serviceProviderParams = null)
    {
        $params = array();
        $params['oauth_consumer_key'] = $config->getConsumerKey();
        $params['oauth_nonce'] = Zend_Oauth::generateNonce();
        $params['oauth_signature_method'] = $config->getSignatureMethod();
        $params['oauth_timestamp'] = Zend_Oauth::generateTimestamp();
        $params['oauth_token'] = $this->getToken();
        $params['oauth_version'] = $config->getVersion();
        if (!is_null($serviceProviderParams)) {
            $params = array_merge($params, $serviceProviderParams);
        }
        $params['oauth_signature'] = Zend_Http::sign(
            $params,
            $config->getSignatureMethod(),
            $config->getConsumerSecret(),
            $this->getTokenSecret(),
            $config->getRequestMethod(),
            $url
        );
        return $params;
    }

    public function toEncodedQueryString(array $params) 
    {
        $encodedParams = array();
        foreach ($params as $key => $value) {
            $encodedParams[] =
                self::urlEncode($key) . '=' . self::urlEncode($value);
        }
        return implode('&', $encodedParams);
    }

    public function toAuthorizationHeader(array $params, $realm = null)
    {
        $headerValue = array();
        $headerValue[] = 'OAuth realm="' . $realm . '"';
        foreach ($params as $key => $value) {
            $headerValue[] =
                self::urlEncode($key) . '="'
                . self::urlEncode($value) . '"';
        }
        return implode(",", $headerValue);
    }

    public static function sign(array $params, $signatureMethod, $consumerSecret, $tokenSecret = null, $method = null, $url = null)
    {
        $className = '';
        $hashAlgo = null;
        $parts = explode('-', $signatureMethod);
        if (count($parts) > 1) {
            $className = 'Zend_Oauth_Signature_' . ucfirst(strtolower($parts[0]));
            $hashAlgo = $parts[1];
        } else {
            $className = 'Zend_Oauth_Signature_' . ucfirst(strtolower($signatureMethod));
        }
        $signatureObject = new $className($consumerSecret, $tokenSecret, $hashAlgo);
        return $signatureObject->sign($params, $method, $url);
    }

    public static function generateNonce()
    {
        return md5(uniqid(rand(), true));
    }

    public static function generateTimestamp()
    {
        return time();
    }

    public static function urlEncode($value) 
    {
        $encoded = rawurlencode($value);
        return str_replace('%7E','~',$encoded);
    }
}