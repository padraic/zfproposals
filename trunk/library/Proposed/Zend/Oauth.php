<?php

require_once 'Zend/Http/Client.php';

require_once 'Zend/Oauth/Signature/Plaintext.php';

require_once 'Zend/Oauth/Signature/Hmac.php';

require_once 'Zend/Oauth/Signature/Rsa.php';

class Zend_Oauth
{

    const REQUEST_SCHEME_HEADER = 'header';

    const REQUEST_SCHEME_POSTBODY = 'postbody';

    const REQUEST_SCHEME_QUERYSTRING = 'querystring';

    protected static $httpClient = null;

    public static function setHttpClient(Zend_Http_Client $httpClient)
    {
        self::$httpClient = $httpClient;
    }

    public static function getHttpClient()
    {
        if (!isset(self::$httpClient)) {
            self::$httpClient = new Zend_Http_Client;
        } else {
            self::$httpClient->setHeaders('Authorization', null);
            self::$httpClient->resetParameters();
        }
        return self::$httpClient;
    }

    public static function clearHttpClient()
    {
        self::$httpClient = null;
    }

    public static function urlEncode($string) 
    {
        $return = urlencode($string);
        /* 5.1. Parameter Encoding
           'Characters in the unreserved character set MUST NOT be encoded.' */
        $return = str_replace('%7E', '~', $string);
        return $return;
    }

    public function sign(array $params, $method, $consumerSecret, $accessTokenSecret = null)
    {
        $className = '';
        $hashAlgo = null;
        $parts = explode('-', $method);
        if (count($parts) > 1) {
            $className = 'Zend_Oauth_Signature_' . ucfirst(strtolower($parts[0]));
            $hashAlgo = $parts[1];
        } else {
            $className = 'Zend_Oauth_Signature_' . ucfirst(strtolower($method));
        }
        $signatureObject = new $className($consumerSecret, $accessTokenSecret, $hashAlgo);
        return $signatureObject->sign($params);
    }

    public function generateNonce()
    {
        return sha1(uniqid(rand(), true));
    }

    public function generateTimestamp()
    {
        return time();
    }

}