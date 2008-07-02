<?php

require_once 'Zend/Oauth/Http.php';
require_once 'Zend/Oauth/Token/Request.php';

class Zend_Oauth_Http_RequestToken extends Zend_Oauth_Http
{

    protected $_httpClient = null;

    public function execute()
    {
        $params = $this->assembleParams();
        $response = $this->startRequestCycle($params);
        $return = new Zend_Oauth_Token_Request($response);
        return $return;
    }

    public function assembleParams()
    {
        $params = array();
        $params['oauth_consumer_key'] = $this->_consumer->getConsumerKey();
        $params['oauth_nonce'] = $this->_httpUtility->generateNonce();
        $params['oauth_signature_method'] = $this->_consumer->getSignatureMethod();
        $params['oauth_timestamp'] = $this->_httpUtility->generateTimestamp();
        $params['oauth_version'] = $this->_consumer->getVersion();
        if (!empty($this->_parameters)) {
            $params = array_merge($params, $this->_parameters);
        }
        $params['oauth_signature'] = $this->_httpUtility->sign(
            $params,
            $this->_consumer->getSignatureMethod(),
            $this->_consumer->getConsumerSecret(),
            null,
            $this->_preferredRequestMethod,
            $this->_consumer->getRequestTokenUrl()
        );
        return $params;
    }

    public function getRequestSchemeHeaderClient(array $params)
    {
        $headerValue = $this->_httpUtility->toAuthorizationHeader(
            $params
        );
        $client = Zend_Oauth::getHttpClient();
        $client->setUri($this->_consumer->getRequestTokenUrl());
        $client->setHeaders('Authorization', $headerValue);
        $rawdata = $this->_httpUtility->toEncodedQueryString($params, true);
        if (!empty($rawdata)) $client->setRawData($rawdata);
        $client->setMethod($this->_preferredRequestMethod);
        return $client;
    }

    public function getRequestSchemePostBodyClient(array $params)
    {
        $client = Zend_Oauth::getHttpClient();
        $client->setUri($this->_consumer->getRequestTokenUrl());
        $client->setMethod($this->_preferredRequestMethod);
        $client->setRawData(
            $this->_httpUtility->toEncodedQueryString($params)
        );
        return $client;
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
                $httpClient = $this->getRequestSchemeQueryStringClient($params,
                    $this->_consumer->getRequestTokenUrl());
                break;
        }
        return $httpClient->request();
    }

}