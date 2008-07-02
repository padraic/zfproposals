<?php

require_once 'Zend/Oauth.php';

require_once 'Zend/Uri.php';

require_once 'Zend/Oauth/Http/RequestToken.php';

require_once 'Zend/Oauth/Http/UserAuthorisation.php';

require_once 'Zend/Oauth/Http/AccessToken.php';

require_once 'Zend/Oauth/Token/AuthorisedRequest.php';

require_once 'Zend/Oauth/Config/Interface.php';

class Zend_Oauth_Consumer extends Zend_Oauth implements Zend_Oauth_Config_Interface
{

    protected $_signatureMethod = 'HMAC-SHA1';

    protected $_requestScheme = Zend_Oauth::REQUEST_SCHEME_HEADER;

    protected $_version = '1.0';

    protected $_localUrl = null;

    protected $_requestTokenUrl = null;

    protected $_accessTokenUrl = null;

    protected $_userAuthorisationUrl = null;

    protected $_consumerKey = null;

    protected $_consumerSecret = null;

    protected $_rsaPrivateKey = null;

    protected $_requestToken = null;

    protected $_accessToken = null;

    public function __construct(array $options = null)
    {
        if (!is_null($options)) {
            $this->setOptions($options);
        }
        // add Zend_Config support later
    }

    public function setOptions(array $options)
    {
        foreach ($options as $key=>$value) {
            switch ($key) {
                case 'consumerKey':
                    $this->setConsumerKey($value);
                    break;
                case 'consumerSecret':
                    $this->setConsumerSecret($value);
                    break;
                case 'signatureMethod':
                    $this->setSignatureMethod($value);
                    break;
                case 'version':
                    $this->setVersion($value);
                    break;
                case 'localUrl':
                    $this->setLocalUrl($value);
                    break;
                case 'requestTokenUrl':
                    $this->setRequestTokenUrl($value);
                    break;
                case 'accessTokenUrl':
                    $this->setAccessTokenUrl($value);
                    break;
                case 'userAuthorisationUrl':
                    $this->setUserAuthorisationUrl($value);
                    break;
                case 'rsaPrivateKey':
                    $this->setRsaPrivateKey($value);
                    break;
            }
        }
        if (isset($options['requestScheme'])) {
            $this->setRequestScheme($options['requestScheme']);
        }
    }

    public function getRequestToken(array $customServiceParameters = null, $httpMethod = null,
        Zend_Oauth_Http_RequestToken $request = null)
    {
        if (is_null($request)) {
            $request = new Zend_Oauth_Http_RequestToken($this, $customServiceParameters);
        } elseif(!is_null($customServiceParameters)) {
            $request->setParameters($customServiceParameters);
        }
        if (!is_null($httpMethod)) {
            $request->setMethod($httpMethod);
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

    public function getAccessToken($queryData, Zend_Oauth_Token_Request $token = null,
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
        }
        if (isset($token)) {
            if ($authorisedToken->getToken() !== $token->getToken()) {
                require_once 'Zend/Oauth/Exception.php';
                throw new Zend_Oauth_Exception(
                    'Authorised token from Service Provider does not match supplied Request Token details');
            }
        } else {
            // retrieve token from storage solution !!TBI!!
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

    public function setConsumerKey($key)
    {
        $this->_consumerKey = $key;
    }

    public function getConsumerKey()
    {
        return $this->_consumerKey;
    }

    public function setConsumerSecret($secret)
    {
        $this->_consumerSecret = $secret;
    }

    public function getConsumerSecret()
    {
        if (!is_null($this->_rsaPrivateKey)) {
            return $this->_rsaPrivateKey;
        }
        return $this->_consumerSecret;
    }

    public function setSignatureMethod($method)
    {
        $method = strtoupper($method);
        // this is a temporary restriction
        if (!in_array($method, array('HMAC-SHA1', 'RSA-SHA1', 'PLAINTEXT'))) {
            require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception(
                $method . ' is an unsupported signature method'
            );
        }
        $this->_signatureMethod = $method;
    }

    public function getSignatureMethod()
    {
        return $this->_signatureMethod;
    }

    public function setRequestScheme($scheme)
    {
        $scheme = strtolower($scheme);
        if (!in_array($scheme, array(
                self::REQUEST_SCHEME_HEADER,
                self::REQUEST_SCHEME_POSTBODY,
                self::REQUEST_SCHEME_QUERYSTRING
            ))) {
            require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception(
                '\'' . $scheme . '\' is an unsupported request scheme'
            );
        }
        $this->_requestScheme = $scheme;
    }

    public function getRequestScheme()
    {
        return $this->_requestScheme;
    }

    public function setVersion($version)
    {
        $this->_version = $version;
    }

    public function getVersion()
    {
        return $this->_version;
    }

    public function setLocalUrl($url)
    {
        if (!Zend_Uri::check($url)) {
            require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception(
                '\'' . $url . '\' is not a valid URI'
            );
        }
        $this->_localUrl = $url;
    }

    public function getLocalUrl()
    {
        return $this->_localUrl;
    }

    public function setRequestTokenUrl($url)
    {
        if (!Zend_Uri::check($url)) {
            require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception(
                '\'' . $url . '\' is not a valid URI'
            );
        }
        $this->_requestTokenUrl = $url;
    }

    public function getRequestTokenUrl()
    {
        return $this->_requestTokenUrl;
    }

    public function setAccessTokenUrl($url)
    {
        if (!Zend_Uri::check($url)) {
            require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception(
                '\'' . $url . '\' is not a valid URI'
            );
        }
        $this->_accessTokenUrl = $url;
    }

    public function getAccessTokenUrl()
    {
        return $this->_accessTokenUrl;
    }

    public function setUserAuthorisationUrl($url)
    {
        if (!Zend_Uri::check($url)) {
            require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception(
                '\'' . $url . '\' is not a valid URI'
            );
        }
        $this->_userAuthorisationUrl = $url;
    }

    public function getUserAuthorisationUrl()
    {
        return $this->_userAuthorisationUrl;
    }

    public function setRsaPrivateKey(Zend_Crypt_Rsa_Key_Private $key)
    {
        $this->_rsaPrivateKey = $key;
    }

    public function getRsaPrivateKey()
    {
        return $this->_rsaPrivateKey;
    }

}