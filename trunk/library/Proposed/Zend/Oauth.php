<?php

require_once 'Zend/Http/Client.php';

class Zend_Oauth
{

    const REQUEST_SCHEME_HEADER = 'header';

    const REQUEST_SCHEME_POSTBODY = 'postbody';

    const REQUEST_SCHEME_QUERYSTRING = 'querystring';

    const GET = 'GET';

    const POST = 'POST';

    protected static $httpClient = null;

    public static function setHttpClient(Zend_Http_Client $httpClient)
    {
        self::$httpClient = $httpClient;
    }

    public static function getHttpClient()
    {
        if (!isset(self::$httpClient)):
            self::$httpClient = new Zend_Http_Client;
        else:
            self::$httpClient->setHeaders('Authorization', null);
            self::$httpClient->resetParameters();
        endif;
        return self::$httpClient;
    }

    public static function clearHttpClient()
    {
        self::$httpClient = null;
    }

}