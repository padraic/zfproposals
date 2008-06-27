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

    protected $_requestMethod = 'POST';

    protected $_version = '1.0';

    protected $_localUrl = null;

    protected $_requestTokenUrl = null;

    protected $_accessTokenUrl = null;

    protected $_userAuthorisationUrl = null;

    protected $_consumerKey = null;

    protected $_consumerSecret = null;

    protected $_rsaPrivateKey = null;

    public function __construct(array $oauthOptions, $uri = null, $config = null)
    {
        parent::__construct($uri, $config);
        $this->setOptions($oauthOptions);
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

    // There are no unit tests for what follows - it's plain hackish to verify an approach

    public function request($method = null)
    {
        if (! $this->uri instanceof Zend_Uri_Http) {
            /** @see Zend_Http_Client_Exception */
            require_once 'Zend/Http/Client/Exception.php';
            throw new Zend_Http_Client_Exception('No valid URI has been passed to the client');
        }

        if ($method) $this->setMethod($method);
        $this->redirectCounter = 0;
        $response = null;

        // Make sure the adapter is loaded
        if ($this->adapter == null) $this->setAdapter($this->config['adapter']);

        do {
            $uri = clone $this->uri;
            if (!empty($this->paramsGet)) {
                $query = $uri->getQuery();

                if ($this->getRequestScheme() == Zend_Oauth::REQUEST_SCHEME_QUERYSTRING) {
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
                    $uri->setQuery('');
                    $query = $this->getToken()->toQueryString(
                        $this->getUrl(), $this, $params
                    );
                } else {
                    if (!empty($query)) $query .= '&';
                    $query .= http_build_query($this->paramsGet, null, '&');
                }

                $uri->setQuery($query);
            }

            // OAUTH PROGRESSION STARTS HERE

            $body = $this->prepareBody();

            // OAUTH PROGRESSION ENDS HERE

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

    protected function prepareBody()
    {
        // According to RFC2616, a TRACE request should not have a body.
        if ($this->method == self::TRACE) {
            return '';
        }

        // If we have raw_post_data set, just use it as the body.
        // If it's preset, we might have to sign the body, but presumably until
        // any xoauth extensions are deployed, all we can do is assume someone
        // pre-signed this and return it.
        if (isset($this->raw_post_data)) {
            $this->setHeaders('Content-length', strlen($this->raw_post_data));
            return $this->raw_post_data;
        }

        $body = '';

        // If we have files to upload, force enctype to multipart/form-data
        if (count ($this->files) > 0) $this->setEncType(self::ENC_FORMDATA);

        // If we have POST parameters or files, encode and add them to the body
        if (count($this->paramsPost) > 0 || count($this->files) > 0) {
            switch($this->enctype) {
                case self::ENC_FORMDATA:
                    // Encode body as multipart/form-data
          // OAuth signing does not apply to multipart form data
      // or rather it's not addressed in the Core spec...
                    $boundary = '---ZENDHTTPCLIENT-' . md5(microtime());
                    $this->setHeaders('Content-type', self::ENC_FORMDATA . "; boundary={$boundary}");

                    // Get POST parameters and encode them
                    $params = $this->_getParametersRecursive($this->paramsPost);
                    foreach ($params as $pp) {
                        $body .= self::encodeFormData($boundary, $pp[0], $pp[1]);
                    }

                    // Encode files
                    foreach ($this->files as $name => $file) {
                        $fhead = array('Content-type' => $file[1]);
                        $body .= self::encodeFormData($boundary, $name, $file[2], $file[0], $fhead);
                    }

                    $body .= "--{$boundary}--\r\n";
                    break;

                case self::ENC_URLENCODED:
                    // Encode body as application/x-www-form-urlencoded
                    $this->setHeaders('Content-type', self::ENC_URLENCODED);
                    if ($this->getRequestScheme() == Zend_Oauth::REQUEST_SCHEME_POSTBODY) {
                        $body = $this->getToken()->toQueryString(
                            $this->getUri(true), $this, $this->paramsPost
                        );
                    } else {
                        $body = http_build_query($this->paramsPost, '', '&');
                    }

                    break;

                default:
                    /** @see Zend_Http_Client_Exception */
                    require_once 'Zend/Http/Client/Exception.php';
                    throw new Zend_Http_Client_Exception("Cannot handle content type '{$this->enctype}' automatically." .
                        " Please use Zend_Http_Client::setRawData to send this kind of content.");
                    break;
            }
        }

        // Set the content-length if we have a body or if request is POST/PUT
        if ($body || $this->method == self::POST || $this->method == self::PUT) {
            $this->setHeaders('Content-length', strlen($body));
        }

        return $body;
    }

    protected function prepareHeaders()
    {
        $headers = array();

        // Set the host header
        if (! isset($this->headers['host'])) {
            $host = $this->uri->getHost();

            // If the port is not default, add it
            if (! (($this->uri->getScheme() == 'http' && $this->uri->getPort() == 80) ||
                  ($this->uri->getScheme() == 'https' && $this->uri->getPort() == 443))) {
                $host .= ':' . $this->uri->getPort();
            }

            $headers[] = "Host: {$host}";
        }

        // Set the connection header
        if (! isset($this->headers['connection'])) {
            if (! $this->config['keepalive']) $headers[] = "Connection: close";
        }

        // Set the Accept-encoding header if not set - depending on whether
        // zlib is available or not.
        if (! isset($this->headers['accept-encoding'])) {
        	if (function_exists('gzinflate')) {
        		$headers[] = 'Accept-encoding: gzip, deflate';
        	} else {
        		$headers[] = 'Accept-encoding: identity';
        	}
        }
        
        // Set the content-type header
        if ($this->method == self::POST &&
           (! isset($this->headers['content-type']) && isset($this->enctype))) {

            $headers[] = "Content-type: {$this->enctype}";
        }
        
        // Set the user agent header
        if (! isset($this->headers['user-agent']) && isset($this->config['useragent'])) {
            $headers[] = "User-agent: {$this->config['useragent']}";
        }

        // Set HTTP authentication if needed
        if (is_array($this->auth)) {
            $auth = self::encodeAuthHeader($this->auth['user'], $this->auth['password'], $this->auth['type']);
            $headers[] = "Authorization: {$auth}";
        }
        if ($this->getRequestScheme() == Zend_Oauth::REQUEST_SCHEME_HEADER) {
            $oauth = $this->getToken()->toHeader(
                $this->getUri(true), $this
            );
            $headers[] = "Authorization: {$oauth}";
        }

        // Load cookies from cookie jar
        if (isset($this->cookiejar)) {
            $cookstr = $this->cookiejar->getMatchingCookies($this->uri,
                true, Zend_Http_CookieJar::COOKIE_STRING_CONCAT);

            if ($cookstr) $headers[] = "Cookie: {$cookstr}";
        }

        // Add all other user defined headers
        foreach ($this->headers as $header) {
        	list($name, $value) = $header;
            if (is_array($value))
                $value = implode(', ', $value);

            $headers[] = "$name: $value";
        }

        return $headers;
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