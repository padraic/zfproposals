<?php

require_once 'Zend/Oauth.php';

require_once 'Zend/Uri.php';

require_once 'Zend/Oauth/Http/RequestToken.php';

class Zend_Oauth_Consumer extends Zend_Oauth
{

    protected $_signatureMethod = 'HMAC-SHA1';

    protected $_requestMethod = 'POST';

    protected $_requestScheme = self::REQUEST_SCHEME_HEADER;

    protected $_version = '1.0';

    protected $_localUrl = null;

    protected $_requestTokenUrl = null;

    protected $_accessTokenUrl = null;

    protected $_userAuthorisationUrl = null;

    protected $_consumerKey = null;

    protected $_consumerSecret = null;

    public function __construct($consumerKey, $consumerSecret, array $options = array())
    {
        $this->setConsumerKey($consumerKey);
        $this->setConsumerSecret($consumerSecret);
        $this->setOptions($options);
    }

    public function setOptions(array $options)
    {
        foreach ($options as $key=>$value) {
            switch ($key) {
                case 'signatureMethod':
                    $this->setSignatureMethod($value);
                    break;
                case 'requestMethod':
                    $this->setRequestMethod($value);
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

    public function getRequestToken(array $customServiceParameters = null, Zend_Oauth_Request_RequestToken $request = null)
    {
        if (is_null($request)) {
            $request = new Zend_Oauth_Http_RequestToken($this, $customServiceParameters);
        } elseif(!is_null($customServiceParameters)) {
            $request->setParameters($customServiceParameters);
        }
        return $request->execute();
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

    public function setRequestMethod($method)
    {
        $method = strtoupper($method);
        if (!in_array($method, array('POST', 'GET'))) {
            require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception(
                $method . ' is an unsupported request method'
            );
        }
        $this->_requestMethod = $method;
    }

    public function getRequestMethod()
    {
        return $this->_requestMethod;
    }

    public function setRequestScheme($scheme)
    {
        $scheme = strtolower($scheme);
        if (!in_array($scheme, array(self::REQUEST_SCHEME_HEADER, self::REQUEST_SCHEME_POSTBODY, self::REQUEST_SCHEME_QUERYSTRING))) {
            require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception(
                '\'' . $scheme . '\' is an unsupported request scheme'
            );
        }
        if ($scheme == self::REQUEST_SCHEME_QUERYSTRING) {
            $this->setRequestMethod('GET');
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

}