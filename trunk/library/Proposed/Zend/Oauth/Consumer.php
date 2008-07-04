<?php

require_once 'Zend/Oauth.php';

require_once 'Zend/Uri.php';

require_once 'Zend/Oauth/Http/RequestToken.php';

require_once 'Zend/Oauth/Http/UserAuthorisation.php';

require_once 'Zend/Oauth/Http/AccessToken.php';

require_once 'Zend/Oauth/Token/AuthorisedRequest.php';

require_once 'Zend/Oauth/Config.php';

class Zend_Oauth_Consumer extends Zend_Oauth
{

    protected $_requestToken = null;

    protected $_accessToken = null;

    protected $_config = null;

    public function __construct($options = null)
    {
        $this->_config = new Zend_Oauth_Config;
        if (!is_null($options)) {
            if ($options instanceof Zend_Config) {
                $options = $options->toArray();
            }
            $this->_config->setOptions($options);
        }
    }

    public function getRequestToken(array $customServiceParameters = null,
        $httpMethod = null,
        Zend_Oauth_Http_RequestToken $request = null)
    {
        if (is_null($request)) {
            $request = new Zend_Oauth_Http_RequestToken($this, $customServiceParameters);
        } elseif(!is_null($customServiceParameters)) {
            $request->setParameters($customServiceParameters);
        }
        if (!is_null($httpMethod)) {
            $request->setMethod($httpMethod);
        } else {
            $request->setMethod($this->getRequestMethod());
        }
        $this->_requestToken = $request->execute();
        return $this->_requestToken;
    }

    public function getRedirectUrl(array $customServiceParameters = null,
        Zend_Oauth_Token_Request $token = null,
        Zend_Oauth_Http_UserAuthorisation $redirect = null)
    {
        if (is_null($redirect)) {
            $redirect = new Zend_Oauth_Http_UserAuthorisation($this, $customServiceParameters);
        } elseif(!is_null($customServiceParameters)) {
            $redirect->setParameters($customServiceParameters);
        }
        if (!is_null($token)) {
            $this->_requestToken = $token;
        }
        return $redirect->getUrl();
    }

    public function redirect(array $customServiceParameters = null,
        Zend_Oauth_Http_UserAuthorisation $request = null)
    {
        $redirectUrl = $this->getRedirectUrl($customServiceParameters, $request);
        header('Location: ' . $redirectUrl);
    }

    public function getAccessToken($queryData, Zend_Oauth_Token_Request $token,
        $httpMethod = null, Zend_Oauth_Http_AccessToken $request = null)
    {
        $authorisedToken = new Zend_Oauth_Token_AuthorisedRequest($queryData);
        if (!$authorisedToken->isValid()) {
            require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception(
                'Response from Service Provider is not a valid authorised request token');
        }
        if (is_null($request)) {
            $request = new Zend_Oauth_Http_AccessToken($this);
        }
        if (!is_null($httpMethod)) {
            $request->setMethod($httpMethod);
        } else {
            $request->setMethod($this->getRequestMethod());
        }
        if (isset($token)) {
            if ($authorisedToken->getToken() !== $token->getToken()) {
                require_once 'Zend/Oauth/Exception.php';
                throw new Zend_Oauth_Exception(
                    'Authorised token from Service Provider does not match
                    supplied Request Token details'
                );
            }
        } else {
            require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception('Request token must be passed to method');
        }
        $this->_requestToken = $token;
        $this->_accessToken = $request->execute();
        return $this->_accessToken;
    }

    public function getLastRequestToken()
    {
        return $this->_requestToken;
    }

    public function getLastAccessToken()
    {
        return $this->_accessToken;
    }

    public function getToken() 
    {
        return $this->_accessToken;
    }

    public function __call($method, array $args) 
    {
        if (method_exists($this->_config, $method)) {
            return call_user_func_array(array($this->_config,$method), $args);
        }
        require_once 'Zend/Oauth/Exception.php';
        throw new Zend_Oauth_Exception('Method does not exist: '.$method);
    }

}