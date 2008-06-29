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

    protected $_localUrl = null;

    protected $_requestTokenUrl = null;

    protected $_accessTokenUrl = null;

    protected $_userAuthorisationUrl = null;

    protected $_consumerKey = null;

    protected $_consumerSecret = null;

    protected $_rsaPrivateKey = null;

    protected $_excludeCustomParamsFromHeader = true;

    public function __construct(array $oauthOptions, $uri = null, $config = null, $excludeCustomParamsFromHeader = true)
    {
        parent::__construct($uri, $config);
        $this->setOptions($oauthOptions);
        $this->_excludeCustomParamsFromHeader = (bool) $excludeCustomParamsFromHeader;
    }

    public function setMethod($method = self::GET)
    {
        $return = parent::setMethod($method);
        // this is all wrong - but GET support is not introduced yet...
        if ($method == self::GET) {
            $this->_requestMethod = 'GET';
            if ($this->_requestScheme !== Zend_Oauth::REQUEST_SCHEME_HEADER) {
                $this->setRequestScheme(Zend_Oauth::REQUEST_SCHEME_QUERYSTRING);
            }
        } elseif($method == self::POST) {
            //$this->setRequestScheme(Zend_Oauth::REQUEST_SCHEME_POSTBODY);
            $this->_requestMethod = 'POST';
        }
        return $return;
    }

    public function request($method = null)
    {
        if (!is_null($method)) {
            $this->setMethod($method);
        }
        if ($this->_requestScheme == Zend_Oauth::REQUEST_SCHEME_HEADER) {
            $params = array();
            if (!empty($this->paramsGet)) {

            }
            $oauthHeaderValue = $this->getToken()->toHeader(
                $this->getUri(true), $this
            );
            $this->setHeaders('Authorization', $oauthHeaderValue);
        } elseif ($this->_requestScheme == Zend_Oauth::REQUEST_SCHEME_POSTBODY) {
            $raw = $this->getToken()->toQueryString(
                $this->getUri(true), $this, $this->paramsPost
            );
            $this->paramsPost = array();
            $this->setRawData($raw);
        } elseif ($this->_requestScheme == Zend_Oauth::REQUEST_SCHEME_QUERYSTRING) {
            $query = $uri->getUri()->getQuery();
            if (!empty($query)) {
                $params = array();
                $parts = explode('&', $query); // yes, yes, test ;)
                foreach ($parts as $part) {
                    $pair = explode('=', $part);
                    $params[$pair[0]] = $pair[1];
                }
                $params = array_merge($params, $this->paramsGet);
            }
            // reset query to the signed OAuth parameter style
            $this->paramsGet = array();
            $this->getUri()->setQuery('');
            $query = $this->getToken()->toQueryString(
                $this->getUri(true), $this, $params
            );
            $this->getUri()->setQuery($query);
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