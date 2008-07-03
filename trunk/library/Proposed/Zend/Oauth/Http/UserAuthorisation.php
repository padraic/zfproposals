<?php

require_once 'Zend/Oauth/Http.php';

require_once 'Zend/Uri/Http.php';

class Zend_Oauth_Http_UserAuthorisation extends Zend_Oauth_Http
{

    public function getUrl()
    {
        $params = $this->assembleParams();
        $uri = Zend_Uri_Http::fromString($this->_consumer->getUserAuthorisationUrl());
        $uri->setQuery(
            $this->_httpUtility->toEncodedQueryString($params)
        );
        return $uri->getUri();
    }

    public function assembleParams()
    {
        $params = array();
        $params['oauth_token'] = $this->_consumer->getLastRequestToken()->getToken();
        $callback = $this->_consumer->getLocalUrl();
        if (!empty($callback)) {
            $params['oauth_callback'] = $callback;
        }
        if (!empty($this->_parameters)) {
            $params = array_merge($params, $this->_parameters);
        }
        return $params;
    }

}