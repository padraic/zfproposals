<?php

require_once 'Zend/Oauth.php';

require_once 'Zend/Http/Client.php';

require_once 'Zend/Oauth/Http/Utility.php';

require_once 'Zend/Oauth/Config.php';

class Zend_Oauth_Client extends Zend_Http_Client
{

    protected $_token = null;

    protected $_config = null;

    public function __construct(array $oauthOptions, $uri = null, $config = null)
    {
        parent::__construct($uri, $config);
        $this->_config = new Zend_Oauth_Config;
        if (!is_null($oauthOptions)) {
            if ($oauthOptions instanceof Zend_Config) {
                $oauthOptions = $oauthOptions->toArray();
            }
            $this->_config->setOptions($oauthOptions);
        }
    }

    public function setMethod($method = self::GET)
    {
        if ($method == self::GET) {
            $this->setRequestMethod(self::GET);
        } elseif($method == self::POST) {
            $this->setRequestMethod(self::POST);
        }
        return parent::setMethod($method);
    }

    public function request($method = null)
    {
        if (!is_null($method)) {
            $this->setMethod($method);
        }
        $this->prepareOauth();
        return parent::request();
    }

    public function prepareOauth() 
    {
        $requestScheme = $this->getRequestScheme();
        $requestMethod = $this->getRequestMethod();
        if ($requestScheme == Zend_Oauth::REQUEST_SCHEME_HEADER) {
            $params = array();
            if (!empty($this->paramsGet)) {
                $params = array_merge($params, $this->paramsGet);
                $query = $this->getToken()->toQueryString(
                    $this->getUri(true), $this, $params
                );
                $this->paramsGet = array();
            }
            if (!empty($this->paramsPost)) {
                $params = array_merge($params, $this->paramsPost);
                $query = $this->getToken()->toQueryString(
                    $this->getUri(true), $this->_config, $params
                );
                $this->paramsPost = array();
            }
            $oauthHeaderValue = $this->getToken()->toHeader(
                $this->getUri(true), $this->_config, $params
            );
            $this->setHeaders('Authorization', $oauthHeaderValue);
            if ($requestMethod == self::GET) {
                $this->getUri()->setQuery($query);
            } elseif($requestMethod == self::POST) {
                $this->setRawData($query);
            }
        } elseif ($requestScheme == Zend_Oauth::REQUEST_SCHEME_POSTBODY) {
            if ($requestMethod == self::GET) {
                require_once 'Zend/Oauth/Exception.php';
                throw new Zend_Oauth_Exception('The client is configured to pass OAuth parameters through a POST body but request method is set to GET');
            }
            $raw = $this->getToken()->toQueryString(
                $this->getUri(true), $this->_config, $this->paramsPost
            );
            $this->setRawData($raw);
            $this->paramsPost = array();
        } elseif ($requestScheme == Zend_Oauth::REQUEST_SCHEME_QUERYSTRING) {
            $this->getUri()->setQuery('');
            $query = $this->getToken()->toQueryString(
                $this->getUri(true), $this->_config, $this->paramsGet
            );
            $this->getUri()->setQuery($query);
            $this->paramsGet = array();
        }
    }

    public function __call($method, array $args) 
    {
        if (method_exists($this->_config, $method)) {
            return call_user_func_array(array($this->_config,$method), $args);
        }
        require_once 'Zend/Oauth/Exception.php';
        throw new Zend_Oauth_Exception('Method does not exist: '.$method);
    }

}