<?php

require_once 'Zend/Oauth/Http.php';
require_once 'Zend/Oauth/Token/Access.php';

class Zend_Oauth_Http_AccessToken extends Zend_Oauth_Http
{

    protected $_httpClient = null;

    public function execute()
    {
        $params = $this->assembleParams();
        $response = $this->startRequestCycle($params);
        $return = new Zend_Oauth_Token_Access($response);
        return $return;
    }

    public function assembleParams()
    {
        $params = array();
        $params['oauth_consumer_key'] = $this->_consumer->getConsumerKey();
        $params['oauth_nonce'] = $this->_httpUtility->generateNonce();
        $params['oauth_signature_method'] = $this->_consumer->getSignatureMethod();
        $params['oauth_timestamp'] = $this->_httpUtility->generateTimestamp();
        $params['oauth_token'] = $this->_consumer->getLastRequestToken()->getToken();
        $params['oauth_version'] = $this->_consumer->getVersion();
        $params['oauth_signature'] = $this->_httpUtility->sign(
            $params,
            $this->_consumer->getSignatureMethod(),
            $this->_consumer->getConsumerSecret(),
            $this->_consumer->getLastRequestToken()->getTokenSecret(),
            $this->_preferredRequestMethod,
            $this->_consumer->getAccessTokenUrl()
        );
        return $params;
    }

    public function getRequestSchemeHeaderClient(array $params)
    {
        $params = $this->_cleanParamsOfIllegalCustomParameters($params);
        $headerValue = $this->_toAuthorizationHeader($params);
        $client = Zend_Oauth::getHttpClient();
        $client->setUri($this->_consumer->getAccessTokenUrl());
        $client->setHeaders('Authorization', $headerValue);
        $client->setMethod($this->_preferredRequestMethod);
        return $client;
    }

    public function getRequestSchemePostBodyClient(array $params)
    {
        $params = $this->_cleanParamsOfIllegalCustomParameters($params);
        $client = Zend_Oauth::getHttpClient();
        $client->setUri($this->_consumer->getAccessTokenUrl());
        $client->setMethod($this->_preferredRequestMethod);
        $client->setRawData(
            $this->_httpUtility->toEncodedQueryString($params)
        );
        return $client;
    }

    public function getRequestSchemeQueryStringClient(array $params, $url)
    {
        $params = $this->_cleanParamsOfIllegalCustomParameters($params);
        return parent::getRequestSchemeQueryStringClient($params, $url);
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
                    $this->_consumer->getAccessTokenUrl());
                break;
        }
        return $httpClient->request();
    }

    protected function _cleanParamsOfIllegalCustomParameters(array $params)
    {
        foreach ($params as $key=>$value) {
            if (!preg_match("/^oauth_/", $key)) {
                unset($params[$key]);
            }
        }
        return $params;
    }

}