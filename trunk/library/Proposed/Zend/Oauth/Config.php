<?php

require_once 'Zend/Oauth.php';

require_once 'Zend/Uri.php';

require_once 'Zend/Oauth/Config/Interface.php';

class Zend_Oauth_Config implements Zend_Oauth_Config_Interface
{

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

    protected $_rsaPublicKey = null;

    protected $_requestMethod = Zend_Oauth::POST;

    protected $_token = null;

    public function __construct($options = null)
    {
        if (!is_null($options)) {
            if ($options instanceof Zend_Config) {
                $options = $options->toArray();
            }
            $this->setOptions($options);
        }
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
                    $this->setCallbackUrl($value);
                    break;
                case 'callbackUrl':
                    $this->setCallbackUrl($value);
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
                case 'requestMethod':
                    $this->setRequestMethod($value);
                    break;
                case 'rsaPrivateKey':
                    $this->setRsaPrivateKey($value);
                    break;
                case 'rsaPublicKey':
                    $this->setRsaPublicKey($value);
                    break;
            }
        }
        if (isset($options['requestScheme'])) {
            $this->setRequestScheme($options['requestScheme']);
        }
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
        $this->_signatureMethod = strtoupper($method);
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
        if ($scheme == Zend_Oauth::REQUEST_SCHEME_POSTBODY
            && $this->getRequestMethod() == Zend_Oauth::GET) {
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

    public function setRsaPublicKey(Zend_Crypt_Rsa_Key_Public $key)
    {
        $this->_rsaPublicKey = $key;
    }

    public function getRsaPublicKey()
    {
        return $this->_rsaPublicKey;
    }

    public function setRsaPrivateKey(Zend_Crypt_Rsa_Key_Private $key)
    {
        $this->_rsaPrivateKey = $key;
    }

    public function getRsaPrivateKey()
    {
        return $this->_rsaPrivateKey;
    }

    public function setToken(Zend_Oauth_Token $token) 
    {
        $this->_token = $token;
    }

    public function getToken() 
    {
        return $this->_token;
    }

}