<?php

require_once 'Zend/Oauth/Token.php';

require_once 'Zend/Oauth/Http.php';

require_once 'Zend/Uri/Http.php';

require_once 'Zend/Oauth/Http/Utility.php';

class Zend_Oauth_Token_Access extends Zend_Oauth_Token
{

    public function toHeader($url, Zend_Oauth_Consumer $consumer, $realm = null)
    {
        if (!Zend_Uri::check($url)) {
            require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception(
                '\'' . $url . '\' is not a valid URI'
            );
        }
        $params = $this->_httpUtility->assembleParams($url, $consumer);
        return $this->_httpUtility->toAuthorizationHeader($params, $realm);
    }

    public function toQueryString($url, Zend_Oauth_Consumer $consumer, array $params = null)
    {
        if (!Zend_Uri::check($url)) {
            require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception(
                '\'' . $url . '\' is not a valid URI'
            );
        }
        $params = $this->_httpUtility->assembleParams($url, $consumer, $params);
        return $this->_httpUtility->toEncodedQueryString($params);
    }

}