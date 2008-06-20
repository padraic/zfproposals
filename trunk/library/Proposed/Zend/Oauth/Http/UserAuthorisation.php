<?php

require_once 'Zend/Oauth/Http.php';
require_once 'Zend/Uri/Http.php';

class Zend_Oauth_Http_UserAuthorisation extends Zend_Oauth_Http
{

    public function getUrl()
    {
        $params = $this->assembleParams();
        foreach ($params as $key => $value) {
            $encodedParams[] =
                Zend_Oauth::urlEncode($key) . '=' . Zend_Oauth::urlEncode($value);
        }
        $uri = Zend_Uri_Http::fromString($this->_consumer->getUserAuthorisationUrl());
        $uri->setQuery(implode('&', $encodedParams));
        return $uri->getUri();
    }

    public function assembleParams()
    {
        $params = array();
        $params['oauth_token'] = $this->_consumer->getLastRequestToken()->getToken();
        $params['oauth_callback'] = $this->_consumer->getLocalUrl();
        if (!empty($this->_parameters)) {
            $params = array_merge($params, $this->_parameters);
        }
        return $params;
    }

}