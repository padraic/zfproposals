<?php

require_once 'Zend/Oauth/Http/Utility.php';

class Zend_Oauth_Http
{

    protected $_parameters = array();

    protected $_consumer = null;

    protected $_preferredRequestScheme = null;

    protected $_preferredRequestMethod = null;

    protected $_httpUtility = null;

    public function __construct(Zend_Oauth_Consumer $consumer, array $parameters = null,
        Zend_Oauth_Http_Utility $utility = null)
    {
        $this->_consumer = $consumer;
        $this->_preferredRequestScheme = $this->_consumer->getRequestScheme();
        $this->_preferredRequestMethod = $this->_consumer->getRequestMethod();
        if (!is_null($parameters)) {
            $this->setParameters($parameters);
        }
        if (!is_null($utility)) {
            $this->_httpUtility = $utility;
        } else {
            $this->_httpUtility = new Zend_Oauth_Http_Utility;
        }
    }

    public function setParameters(array $customServiceParameters)
    {
        $this->_parameters = $customServiceParameters;
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
            $this->_assessRequestAttempt();
            $response = $this->startRequestCycle($params);
        }
        return $response;
    }

    public function getRequestSchemeQueryStringClient(array $params, $url)
    {
        $client = Zend_Oauth::getHttpClient();
        $client->setUri($url);
        $client->getUri()->setQuery(
            $this->_httpUtility->toEncodedQueryString($params)    
        );
        $client->setMethod(Zend_Http_Client::POST);
        return $client;
    }

    protected function _assessRequestAttempt()
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
                    'Could not retrieve a valid Token response from Token URL'
                );
        }
    }

    protected function _toAuthorizationHeader(array $params, $realm = null)
    {
        $headerValue = array();
        $headerValue[] = 'OAuth realm="' . $realm . '"';
        foreach ($params as $key => $value) {
            $headerValue[] =
                Zend_Oauth_Http_Utility::urlEncode($key)
                . '="'
                . Zend_Oauth_Http_Utility::urlEncode($value)
                . '"';
        }
        return implode(",", $headerValue);
    }

}