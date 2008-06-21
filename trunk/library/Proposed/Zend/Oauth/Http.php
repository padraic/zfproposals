<?php

require_once 'Zend/Oauth.php';

abstract class Zend_Oauth_Http
{

    protected $_parameters = array();

    protected $_consumer = null;

    protected $_preferredRequestScheme = null;

    protected $_preferredRequestMethod = null;

    public function __construct(Zend_Oauth_Consumer $consumer, array $parameters = null)
    {
        $this->_consumer = $consumer;
        $this->_preferredRequestScheme = $this->_consumer->getRequestScheme();
        $this->_preferredRequestMethod = $this->_consumer->getRequestMethod();
        if (!is_null($parameters)) {
            $this->setParameters($parameters);
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

    public abstract function assembleParams();

    public function getRequestSchemeQueryStringClient(array $params, $url)
    {
        if ($this->_preferredRequestMethod == 'POST') {
            $params['oauth_signature'] = null;
            $params['oauth_signature'] = $this->_consumer->sign(
            $params,
            $this->_consumer->getSignatureMethod(),
            $this->_consumer->getConsumerSecret(),
            null, 'GET', $url
        );
        }
        $client = Zend_Oauth::getHttpClient();
        $client->setUri($url);
        $encodedParams = array();
        foreach ($params as $key => $value) {
            $encodedParams[] =
                Zend_Oauth::urlEncode($key) . '=' . Zend_Oauth::urlEncode($value);
        }
        $client->setMethod(Zend_Http_Client::GET);
        $client->getUri()->setQuery(implode('&', $encodedParams));
        return $client;
    }

}