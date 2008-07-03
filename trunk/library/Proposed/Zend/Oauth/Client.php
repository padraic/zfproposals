<?php

require_once 'Zend/Oauth.php';

require_once 'Zend/Http/Client.php';

require_once 'Zend/Oauth/Http/Utility.php';

require_once 'Zend/Oauth/Config/Interface.php';

class Zend_Oauth_Client extends Zend_Http_Client implements Zend_Oauth_Config_Interface
{

    protected $_token = null;

    protected $_signatureMethod = 'HMAC-SHA1';

    protected $_requestScheme = Zend_Oauth::REQUEST_SCHEME_HEADER;

    protected $_version = '1.0';

    protected $_callbackUrl = null;

    protected $_requestTokenUrl = null;

    protected $_accessTokenUrl = null;

    protected $_userAuthorisationUrl = null;

    protected $_consumerKey = null;

    protected $_consumerSecret = null;

    protected $_rsaPrivateKey = null;

    protected $_requestMethod = self::POST;

    public function __construct(array $oauthOptions, $uri = null, $config = null, $excludeCustomParamsFromHeader = true)
    {
        parent::__construct($uri, $config);
        if (!is_null($oauthOptions)) {
            if ($oauthOptions instanceof Zend_Config) {
                $oauthOptions = $oauthOptions->toArray();
            }
            $this->setOptions($options);
        }
        $this->_excludeCustomParamsFromHeader = (bool) $excludeCustomParamsFromHeader;
    }

    public function setMethod($method = self::GET)
    {
        if ($method == self::GET) {
            $this->_requestMethod = 'GET';
        } elseif($method == self::POST) {
            $this->_requestMethod = 'POST';
        }
        return parent::setMethod($method);
    }

    public function request($method = null)
    {
        if (!is_null($method)) {
            $this->setMethod($method);
        }
        if ($this->_requestScheme == Zend_Oauth::REQUEST_SCHEME_HEADER) {
            $params = array();
            if (!empty($this->paramsGet)) {
                $params = array_merge($params, $this->paramsGet);
                $query = $this->getToken()->toQueryString(
                    $this->getUri(true), $this, $params
                );
                $this->paramsGet = array();
            }
            if (!empty($this->paramsPost)) {
                $params = array_merge($params, $this->paramsPost);
                $query = $this->getToken()->toQueryString(
                    $this->getUri(true), $this, $params
                );
                $this->paramsPost = array();
            }
            $oauthHeaderValue = $this->getToken()->toHeader(
                $this->getUri(true), $this, $params
            );
            $this->setHeaders('Authorization', $oauthHeaderValue);
            // handle non-OAuth protocol parameter passage
            if ($this->_requestMethod == self::GET) {
                $this->getUri()->setQuery($query);
            } elseif($this->_requestMethod == self::POST) {
                $this->setRawData($query);
            }
        } elseif ($this->_requestScheme == Zend_Oauth::REQUEST_SCHEME_POSTBODY) {
            if ($this->_requestMethod == self::GET) {
                require_once 'Zend/Oauth/Exception.php';
                throw new Zend_Oauth_Exception('The client is configured to pass OAuth parameters through a POST body but request method is set to GET');
            }
            $raw = $this->getToken()->toQueryString(
                $this->getUri(true), $this, $this->paramsPost
            );
            $this->setRawData($raw);
            $this->paramsPost = array();
        } elseif ($this->_requestScheme == Zend_Oauth::REQUEST_SCHEME_QUERYSTRING) {
            $this->getUri()->setQuery('');
            $query = $this->getToken()->toQueryString(
                $this->getUri(true), $this, $this->paramsGet
            );
            $this->getUri()->setQuery($query);
            $this->paramsGet = array();
        }
        return parent::request();
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
                case 'accessToken':
                    $this->setToken($value);
                    break;
                case 'signatureMethod':
                    $this->setSignatureMethod($value);
                    break;
                case 'version':
                    $this->setVersion($value);
                    break;
                case 'localUrl':
                    $this->setCallbackUrl($value);
                    break;
                case 'callbackUrl':
                    $this->setCallbackUrl($value);
                    break;
                case 'requestTokenUrl':
                    $this->setRequestTokenUrl($value);
                    break;
                case 'requestMethod':
                    $this->setRequestMethod($value);
                    break;
                case 'accessTokenUrl':
                    $this->setAccessTokenUrl($value);
                    break;
                case 'userAuthorisationUrl':
                    $this->setUserAuthorisationUrl($value);
                    break;
            }
        }
        if (isset($options['requestScheme'])) {
            $this->setRequestScheme($options['requestScheme']);
        }
    }

    public function setToken(Zend_Oauth_Token_Access $token)
    {
        $this->_token = $token;
    }

    public function getToken()
    {
        return $this->_token;
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
                Zend_Oauth::REQUEST_SCHEME_HEADER,
                Zend_Oauth::REQUEST_SCHEME_POSTBODY,
                Zend_Oauth::REQUEST_SCHEME_QUERYSTRING
            ))) {
            require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception(
                '\'' . $scheme . '\' is an unsupported request scheme'
            );
        }
        if ($scheme == self::REQUEST_SCHEME_POSTBODY
            && $this->getRequestMethod() == self::GET) {
            require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception(
                'Cannot set POSTBODY request method if HTTP method set to GET'
            );
        }
        $this->_requestScheme = $scheme;
    }

    public function getRequestScheme()
    {
        return $this->_requestScheme;
    }

    public function getRequestMethod()
    {
        return $this->_requestMethod;
    }

    public function setVersion($version)
    {
        $this->_version = $version;
    }

    public function getVersion()
    {
        return $this->_version;
    }

    public function setCallbackUrl($url)
    {
        if (!Zend_Uri::check($url)) {
            require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception(
                '\'' . $url . '\' is not a valid URI'
            );
        }
        $this->_callbackUrl = $url;
    }

    public function getCallbackUrl()
    {
        return $this->_callbackUrl;
    }

    public function setLocalUrl($url)
    {
        $this->setCallbackUrl($url);
    }

    public function getLocalUrl()
    {
        return $this->getCallbackUrl();
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

    public function setRequestMethod($method) 
    {
        if (!in_array($method, array(Zend_Oauth::GET, Zend_Oauth::POST))) {
            require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception('Invalid method: '.$method);
        }
        $this->_requestMethod = $method;
    }

    public function getRequestMethod() 
    {
        return $this->_requestMethod;
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