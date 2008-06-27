<?php

require_once 'Zend/Oauth/Http/Utility.php';

require_once 'Zend/Uri/Http.php';

class Zend_Oauth_Http
{

    protected $_parameters = array();

    protected $_excludeParamsFromHeader = true;

    protected $_consumer = null;

    protected $_preferredRequestScheme = null;

    protected $_preferredRequestMethod = Zend_Oauth::POST;

    protected $_httpUtility = null;

    public function __construct(Zend_Oauth_Consumer $consumer, array $parameters = null, $excludeCustomParamsFromHeader = true, Zend_Oauth_Http_Utility $utility = null)
    {
        $this->_consumer = $consumer;
        $this->_preferredRequestScheme = $this->_consumer->getRequestScheme();
        if (!is_null($parameters)) {
            $this->setParameters($parameters);
        }
        $this->_excludeParamsFromHeaders = $excludeCustomParamsFromHeader;
        if (!is_null($utility)) {
            $this->_httpUtility = $utility;
        } else {
            $this->_httpUtility = new Zend_Oauth_Http_Utility;
        }
    }

    public function setMethod($method)
    {
        if (!in_array($method, array(Zend_Oauth::POST, Zend_Oauth::GET))) {
            require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception('invalid HTTP method: '.$method);
        }
        $this->_preferredRequestMethod = $method;
    }

    public function setParameters(array $customServiceParameters,
        $excludeCustomParamsFromHeader = true)
    {
        $this->_parameters = $customServiceParameters;
        $this->_excludeParamsFromHeader = $excludeCustomParamsFromHeader;
    }

    public function getParameters()
    {
        return $this->_parameters;
    }

    public function getConsumer()
    {
        return $this->_consumer;
    }

    public function startRequestCycle(array $params)
    {
        $response = null;
        $body = null;
        $status = null;
        try {
            $response = $this->_attemptRequest($params);
        } catch (Zend_Http_Client_Exception $e) {
        }
        if (!is_null($response)) {
            $body = $response->getBody();
            $status = $response->getStatus();
        }
        if (is_null($response)// Request failure/exception
            || $status == 500 // Internal Server Error
            || $status == 400 // Bad Request
            || $status == 401 // Unauthorized
            || empty($body)   // Missing request token
            ) {
            $this->_assessRequestAttempt($response);
            $response = $this->startRequestCycle($params);
        }
        return $response;
    }

    public function getRequestSchemeQueryStringClient(array $params, $url)
    {
        $client = Zend_Oauth::getHttpClient();
        $client->setUri($url);
        $params = array_merge(
            $this->_httpUtility->parseQueryString($client->getUri()->getQuery()), $params
        );
        $client->getUri()->setQuery(
            $this->_httpUtility->toEncodedQueryString($params)
        );
        $client->setMethod($this->_preferredRequestMethod);
        return $client;
    }

    protected function _assessRequestAttempt(Zend_Http_Response $response = null)
    {
        switch ($this->_preferredRequestScheme) {
            case Zend_Oauth::REQUEST_SCHEME_HEADER:
                $this->_preferredRequestScheme = Zend_Oauth::REQUEST_SCHEME_POSTBODY;
                break;
            case Zend_Oauth::REQUEST_SCHEME_POSTBODY:
                $this->_preferredRequestScheme = Zend_Oauth::REQUEST_SCHEME_QUERYSTRING;
                break;
            default:
                require_once 'Zend/Oauth/Exception.php';
                throw new Zend_Oauth_Exception(
                    'Could not retrieve a valid Token response from Token URL:'
                    . (!is_null($response) ? PHP_EOL . $response->getBody() : '')
                );
        }
    }

    protected function _toAuthorizationHeader(array $params, $realm = null)
    {
        $headerValue = array();
        $headerValue[] = 'OAuth realm="' . $realm . '"';
        foreach ($params as $key => $value) {
            if ($this->_excludeParamsFromHeader) {
                if (!preg_match("/^oauth_/", $key)) {
                    continue; // e.g. Google; cannot include scope in Header
                }
            }
            $headerValue[] =
                Zend_Oauth_Http_Utility::urlEncode($key)
                . '="'
                . Zend_Oauth_Http_Utility::urlEncode($value)
                . '"';
        }
        return implode(",", $headerValue);
    }

}