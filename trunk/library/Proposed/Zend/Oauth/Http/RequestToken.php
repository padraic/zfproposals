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
        // add to all other schemes too!
        //$requestTokenUrl = Zend_Uri_Http::fromString($this->_consumer->getRequestTokenUrl());
        //$queryString = $requestTokenUrl->getQuery();
        //if (!empty($queryString)) {
        //    $queryPairs = $this->_httpUtility->parseQueryString($queryString);
        //    $params = array_merge($params, $queryPairs);
        //    $requestTokenUrl->setQuery('');
        //    $this->_consumer->setRequestTokenUrl($requestTokenUrl->getUri(true));
        //}
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
        $headerValue = $this->_toAuthorizationHeader($params);
        $client = Zend_Oauth::getHttpClient();
        $client->setUri($this->_consumer->getRequestTokenUrl());
        $client->setHeaders('Authorization', $headerValue);
        $client->setMethod(Zend_Http_Client::POST);
        return $client;
    }

    public function getRequestSchemePostBodyClient(array $params)
    {
        $client = Zend_Oauth::getHttpClient();
        $client->setUri($this->_consumer->getRequestTokenUrl());
        $client->setMethod(Zend_Http_Client::POST);
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
        //var_dump($httpClient);
        //var_dump($httpClient->request()); exit; //TEST//
        return $httpClient->request();
    }

}