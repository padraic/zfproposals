<?php

require_once 'Zend/Oauth/Token/Request.php';

class Zend_Oauth_Http_RequestToken extends Zend_Oauth
{

    protected $_consumer = null;

    protected $_parameters = array();

    protected $_httpClient = null;

    protected $_preferredRequestScheme = null;

    public function __construct(Zend_Oauth_Consumer $consumer, array $parameters = null)
    {
        $this->_consumer = $consumer;
        $this->_preferredRequestScheme = $this->_consumer->getRequestScheme();
        if (!is_null($parameters)) {
            $this->setParameters($parameters);
        }
    }

    public function execute(array $params = null)
    {
        $params = $this->assembleParams($params);
        $response = $this->startRequestCycle($params);
        return $response;
    }

    public function setParameters(array $customServiceParameters)
    {
        $this->_parameters = $customServiceParameters;
    }

    public function getParameters()
    {
        return $this->_parameters;
    }

    public function assembleParams()
    {
        $params = array();
        $params['oauth_consumer_key'] = $this->_consumer->getConsumerKey();
        $params['oauth_nonce'] = $this->_consumer->generateNonce();
        $params['oauth_signature_method'] = $this->_consumer->getSignatureMethod();
        $params['oauth_timestamp'] = $this->_consumer->generateTimestamp();
        $params['oauth_version'] = $this->_consumer->getVersion();
        if (!empty($this->_parameters)) {
            $params = array_merge($params, $this->_parameters);
        }
        $params['oauth_signature'] = $this->_consumer->sign(
            $params,
            $this->_consumer->getSignatureMethod(),
            $this->_consumer->getConsumerSecret(),
            null,
            $this->_consumer->getRequestMethod(),
            $this->_consumer->getRequestTokenUrl()
        );
        return $params;
    }

    // TEST
    public function getRequestSchemeHeaderClient(array $params)
    {
        // seems to get no valid reponse from tests...:( OAuth SP bug?
        $headerValue = $this->_toAuthorizationHeader($params);
        $client = Zend_Oauth::getHttpClient();
        $client->setUri($this->_consumer->getRequestTokenUrl());
        $client->setHeaders('Authorization', $headerValue);
        return $client;
    }

    public function getRequestSchemePostBodyClient(array $params)
    {
        $client = Zend_Oauth::getHttpClient();
        $client->setUri($this->_consumer->getRequestTokenUrl());
        $encodedParams = array();
        foreach ($params as $key => $value) {
            $encodedParams[] =
                Zend_Oauth::urlEncode($key) . '=' . Zend_Oauth::urlEncode($value);
        }
        $client->setMethod(Zend_Http_Client::POST);
        $client->setRawData(implode('&', $encodedParams));
        return $client;
    }

    public function getRequestSchemeQueryStringClient(array $params)
    {
        $client = Zend_Oauth::getHttpClient();
        $client->setUri($this->_consumer->getRequestTokenUrl());
        $encodedParams = array();
        foreach ($params as $key => $value) {
            $encodedParams[] =
                Zend_Oauth::urlEncode($key) . '=' . Zend_Oauth::urlEncode($value);
        }
        $client->setMethod(Zend_Http_Client::GET);
        $client->getUri()->setQuery(implode('&', $encodedParams));
        return $client;
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

    public function getConsumer()
    {
        return $this->_consumer;
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
                    'Could not retrieve a valid Request Token response from Request Token URL'
                );
        }
    }

    protected function _attemptRequest(array $params)
    {
        switch ($this->_preferredRequestScheme) {
            case Zend_Oauth::REQUEST_SCHEME_HEADER:
                $httpClient = $this->getRequestSchemeHeaderClient($params);
                break;
            case Zend_Oauth::REQUEST_SCHEME_POSTBODY:
                $httpClient = $this->getRequestSchemePostBodyClient($params);
                break;
            case Zend_Oauth::REQUEST_SCHEME_QUERYSTRING:
                $httpClient = $this->getRequestSchemeQueryStringClient($params);
                break;
        }
        return $httpClient->request();
    }

    protected function _toAuthorizationHeader(array $params, $realm = null)
    {
        $headerValue = array();
        if (is_null($realm)) {
            $headerValue[] = 'OAuth';
        } else {
            $headerValue[] = 'OAuth realm="' . $realm . '"';
        }
        foreach ($params as $key => $value) {
            $headerValue[] =
                Zend_Oauth::urlEncode($key)
                . '="'
                . Zend_Oauth::urlEncode($value)
                . '"';
        }
        return implode(",", $headerValue);
    }

}