<?php

require_once 'Zend/Oauth/Token/Request.php';

class Zend_Oauth_Request_RequestToken extends Zend_Oauth
{

    protected $_consumer = null;

    protected $_parameters = array();

    protected $_httpClient = null;

    public function __construct(Zend_Oauth_Consumer $consumer, array $parameters = null)
    {
        $this->_consumer = $consumer;
        if (!is_null($parameters)) {
            $this->_parameters = $parameters;
        }
    }

    public function execute()
    {
        $params = $this->_assembleParams();
        switch ($this->_consumer->getRequestScheme()) {
            case Zend_Oauth::REQUEST_SCHEME_HEADER:
                $httpClient = $this->_getRequestSchemeHeaderClient($params);
                break;
            case Zend_Oauth::REQUEST_SCHEME_POSTBODY:
                $httpClient = $this->_getRequestSchemePostBodyClient($params);
                break;
            case Zend_Oauth::REQUEST_SCHEME_QUERYSTRING:
                $httpClient = $this->_getRequestSchemeQueryStringClient($params);
                break;
        }
        $response = $httpClient->request();
        var_dump($response); exit;
    }

    public function setParameters(array $customServiceParameters)
    {
        $this->_parameters = $customServiceParameters;
    }

    protected function _assembleParams()
    {
        $params = array();
        $params['oauth_consumer_key'] = $this->_consumer->getConsumerKey();
        $params['oauth_signature_method'] = $this->_consumer->getSignatureMethod();
        $params['oauth_timestamp'] = $this->generateTimestamp();
        $params['oauth_nonce'] = $this->generateNonce();
        $params['oauth_version'] = $this->_consumer->getVersion();
        if (!empty($this->_parameters)) {
            $params = $params + $this->_parameters;
        }
        $params['oauth_signature'] = $this->sign(
            $params, $this->_consumer->getSignatureMethod(),
            $this->_consumer->getConsumerSecret()
        );
        return $params;
    }

    protected function _getRequestSchemeHeaderClient(array $params)
    {
        // seems to get no valid reponse from tests...:( OAuth SP bug?
        $headerValue = $this->_toAuthorizationHeader($params);
        $client = Zend_Oauth::getHttpClient();
        $client->setUri($this->_consumer->getRequestTokenUrl());
        $client->setHeaders('Authorization', $headerValue);
        return $client;
    }

    protected function _getRequestSchemePostBodyClient(array $params)
    {
        $client = Zend_Oauth::getHttpClient();
        $client->setUri($this->_consumer->getRequestTokenUrl());
        $encodedParams = array();
        foreach ($params as $key => $value) {
            $encodedParams[Zend_Oauth::urlEncode($key)] = Zend_Oauth::urlEncode($value);
        }
        $client->setMethod(Zend_Http_Client::POST);
        $client->setRawData(implode('&', $encodedParams));
        return $client;
    }

    protected function _getRequestSchemeQueryStringClient(array $params)
    {
        $client = Zend_Oauth::getHttpClient();
        $client->setUri($this->_consumer->getRequestTokenUrl());
        $encodedParams = array();
        foreach ($params as $key => $value) {
            $encodedParams[Zend_Oauth::urlEncode($key)] = Zend_Oauth::urlEncode($value);
        }
        $client->setMethod(Zend_Http_Client::GET);
        $client->getUri()->setQuery(implode('&', $encodedParams));
        return $client;
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
                . Zend_Oauth::urlEncode($key)
                . '"';
        }
        return implode(",\n", $headerValue);
    }

}