<?php

require_once 'Zend/Oauth.php';

require_once 'Zend/Http/Client.php';

require_once 'Zend/Oauth/Config/Interface.php';

class Zend_Oauth_Client extends Zend_Http_Client implements Zend_Oauth_Config_Interface
{

    protected $_token = null;

    protected $_signatureMethod = 'HMAC-SHA1';

    protected $_requestScheme = Zend_Oauth::REQUEST_SCHEME_POSTBODY;

    protected $_requestMethod = 'POST';

    protected $_version = '1.0';

    protected $_localUrl = null;

    protected $_requestTokenUrl = null;

    protected $_accessTokenUrl = null;

    protected $_userAuthorisationUrl = null;

    protected $_consumerKey = null;

    protected $_consumerSecret = null;

    public function __construct(array $oauthOptions, $uri = null, $config = null) 
    {
        $this->setOptions($oauthOptions);
        parent::__construct($uri, $config);
    }

    public function request($method = null)
    {

        if ($method) $this->setMethod($method);
        do {
            $uri = clone $this->uri;
            if (!empty($this->paramsGet)) {
                $query = $uri->getQuery(); // what about paramsGet!
                // add OAuth parameters to Query String if required
                if ($this->getRequestScheme() == Zend_Oauth::REQUEST_SCHEME_QUERYSTRING) {
                    if (!empty($query)) {
                        $params = array();
                        $parts = explode('&', $query); // yes, yes, test ;)
                        foreach ($parts as $part) {
                            $pair = explode('=', $part);
                            $params[$pair[0]] = $pair[1];
                        }
                    }
                    $query = $this->getToken()->toQueryString(
                        $this->getUrl(),
                        $this,
                        $params
                    );
                } else {
                    if (!empty($query)) $query .= '&';
                    $query .= http_build_query($this->paramsGet, null, '&');
                }

                $uri->setQuery($query);
            }

            // OAUTH PROGRESSION ENDS HERE

            $body = $this->prepareBody();
            $headers = $this->prepareHeaders();

            // Open the connection, send the request and read the response
            $this->adapter->connect($uri->getHost(), $uri->getPort(),
                ($uri->getScheme() == 'https' ? true : false));

            $this->last_request = $this->adapter->write($this->method,
                $uri, $this->config['httpversion'], $headers, $body);

            $response = $this->adapter->read();
            if (! $response) {
                /** @see Zend_Http_Client_Exception */
                require_once 'Zend/Http/Client/Exception.php';
                throw new Zend_Http_Client_Exception('Unable to read response, or response is empty');
            }

            $response = Zend_Http_Response::fromString($response);
            if ($this->config['storeresponse']) $this->last_response = $response;

            // Load cookies into cookie jar
            if (isset($this->cookiejar)) $this->cookiejar->addCookiesFromResponse($response, $uri);

            // If we got redirected, look for the Location header
            if ($response->isRedirect() && ($location = $response->getHeader('location'))) {

                // Check whether we send the exact same request again, or drop the parameters
                // and send a GET request
                if ($response->getStatus() == 303 ||
                   ((! $this->config['strictredirects']) && ($response->getStatus() == 302 ||
                       $response->getStatus() == 301))) {

                    $this->resetParameters();
                    $this->setMethod(self::GET);
                }

                // If we got a well formed absolute URI
                if (Zend_Uri_Http::check($location)) {
                    $this->setHeaders('host', null);
                    $this->setUri($location);

                } else {

                    // Split into path and query and set the query
                    if (strpos($location, '?') !== false) {
                        list($location, $query) = explode('?', $location, 2);
                    } else {
                        $query = '';
                    }
                    $this->uri->setQuery($query);

                    // Else, if we got just an absolute path, set it
                    if(strpos($location, '/') === 0) {
                        $this->uri->setPath($location);

                        // Else, assume we have a relative path
                    } else {
                        // Get the current path directory, removing any trailing slashes
                        $path = $this->uri->getPath();
                        $path = rtrim(substr($path, 0, strrpos($path, '/')), "/");
                        $this->uri->setPath($path . '/' . $location);
                    }
                }
                ++$this->redirectCounter;

            } else {
                // If we didn't get any location, stop redirecting
                break;
            }

        } while ($this->redirectCounter < $this->config['maxredirects']);

        return $response;
    }

    public function setMethod($method = self::GET)
    {
        $return = parent::setMethod($method);
        if ($method == self::GET) {
            $this->setRequestScheme(Zend_Oauth::REQUEST_SCHEME_QUERYSTRING);
            $this->_requestMethod = 'GET';
        }
        return $return;
    }

    public function setOptions(array $options)
    {
        foreach ($options as $key=>$value) {
            switch ($key) {
                case 'accessToken':
                    $this->setToken($value);
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

}