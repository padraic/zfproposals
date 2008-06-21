<?php

require_once 'Zend/Oauth/Token.php';

require_once 'Zend/Uri/Http.php';

class Zend_Oauth_Token_Access extends Zend_Oauth_Token
{

    protected $_response = null;

    public function __construct(Zend_Http_Response $response = null)
    {
        if (!is_null($response)) {
            $this->_response = $response;
            $this->setParams($this->_parseParameters($response));
        }
    }

    public function getResponse()
    {
        return $this->_response;
    }

    public function setTokenSecret($secret)
    {
        $this->setParam(self::TOKEN_SECRET_PARAM_KEY, $secret);
    }

    public function getTokenSecret()
    {
        return $this->getParam(self::TOKEN_SECRET_PARAM_KEY);
    }

    public function toHeader($url, Zend_Oauth_Consumer $consumer, $realm = null)
    {
        if (!Zend_Uri::check($url)) {
            require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception(
                '\'' . $url . '\' is not a valid URI'
            );
        }
        $params = $this->assembleParams($url, $consumer);
        return $this->_toAuthorizationHeader($params, $realm);
    }

    public function toQueryString($url, Zend_Oauth_Consumer $consumer, array $params = null)
    {
        if (!Zend_Uri::check($url)) {
            require_once 'Zend/Oauth/Exception.php';
            throw new Zend_Oauth_Exception(
                '\'' . $url . '\' is not a valid URI'
            );
        }
        $params = $this->assembleParams($url, $consumer, $params);
        $encodedParams = array();
        foreach ($params as $key => $value) {
            $encodedParams[] =
                Zend_Oauth::urlEncode($key) . '=' . Zend_Oauth::urlEncode($value);
        }
        return implode('&', $encodedParams);
    }

    public function assembleParams($url, Zend_Oauth_Consumer $consumer, array $serviceProviderParams = null)
    {
        $params = array();
        $params['oauth_consumer_key'] = $consumer->getConsumerKey();
        $params['oauth_nonce'] = $consumer->generateNonce();
        $params['oauth_signature_method'] = $consumer->getSignatureMethod();
        $params['oauth_timestamp'] = $consumer->generateTimestamp();
        $params['oauth_token'] = $this->getToken();
        $params['oauth_version'] = $consumer->getVersion();
        if (!is_null($serviceProviderParams)) {
            $params = array_merge($params, $serviceProviderParams);
        }
        $params['oauth_signature'] = $consumer->sign(
            $params,
            $consumer->getSignatureMethod(),
            $consumer->getConsumerSecret(),
            $this->getTokenSecret(),
            $consumer->getRequestMethod(),
            $url
        );
        return $params;
    }

    protected function _toAuthorizationHeader(array $params, $realm = null)
    {
        $headerValue = array();
        $headerValue[] = 'OAuth realm="' . $realm . '"';
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