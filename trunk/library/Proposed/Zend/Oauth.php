<?php

require_once 'Zend/Http/Client.php';

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
        $return = rawurlencode($string);
        /* 5.1. Parameter Encoding
           'Characters in the unreserved character set MUST NOT be encoded.' */
        $return = str_replace('%7E', '~', $return);
        return $return;
    }

    public function generateNonce()
    {
        return md5(uniqid(rand(), true));
    }

    public function generateTimestamp()
    {
        return time();
    }

}