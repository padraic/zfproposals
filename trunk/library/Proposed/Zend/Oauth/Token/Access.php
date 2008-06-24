<?php

require_once 'Zend/Oauth/Token.php';

require_once 'Zend/Oauth/Http.php';

require_once 'Zend/Uri/Http.php';

require_once 'Zend/Oauth/Client.php';

class Zend_Oauth_Token_Access extends Zend_Oauth_Token
{

    public function toHeader($url, Zend_Oauth_Config_Interface $config, $realm = null)
    {
        if (!Zend_Uri::check($url)) {
            require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception(
                '\'' . $url . '\' is not a valid URI'
            );
        }
        $params = $this->_httpUtility->assembleParams($url, $config);
        return $this->_httpUtility->toAuthorizationHeader($params, $realm);
    }

    public function toQueryString($url, Zend_Oauth_Config_Interface $config, array $params = null)
    {
        if (!Zend_Uri::check($url)) {
            require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception(
                '\'' . $url . '\' is not a valid URI'
            );
        }
        $params = $this->_httpUtility->assembleParams($url, $config, $params);
        return $this->_httpUtility->toEncodedQueryString($params);
    }

    public function getHttpClient(array $oauthOptions, $uri = null, $config = null) 
    {
        $client = new Zend_Oauth_Client($oauthOptions, $uri = null, $config = null);
        $client->setToken($this);
        return $client;
    }

}