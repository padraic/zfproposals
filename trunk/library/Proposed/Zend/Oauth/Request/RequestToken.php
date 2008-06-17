<?php

class Zend_Oauth_Request_RequestToken extends Zend_Oauth
{

    protected $_consumer = null;

    protected $_parameters = array();

    public function __construct(Zend_Oauth_Consumer $consumer, array $parameters = array()) 
    {
        $this->_consumer = $consumer;
        if (!empty($parameters)) {
            $this->_parameters = $parameters;
        }
    }

    public function execute() 
    {
        $params = $this->_assembleParams();
        switch ($this->_consumer->getRequestScheme()) {
            case Zend_Oauth::REQUEST_SCHEME_HEADER:
                $response = $this->_requestSchemeHeader();
                break;
        }
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

    protected function _requestSchemeHeader() 
    {
        // make request and return tokenised response
    }

}