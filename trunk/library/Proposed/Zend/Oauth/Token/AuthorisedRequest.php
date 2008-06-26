<?php

require_once 'Zend/Oauth/Token.php';

class Zend_Oauth_Token_AuthorisedRequest extends Zend_Oauth_Token
{

    protected $_data = array();

    public function __construct(array $data = null,
        Zend_Oauth_Http_Utility $utility = null)
    {
        if (!is_null($data)) {
            $this->_data = $data;
            $params = $this->_parseData();
            if (count($params) > 0) {
                $this->setParams($params);
            }
        }
        if (!is_null($utility)) {
            $this->_httpUtility = $utility;
        } else {
            $this->_httpUtility = new Zend_Oauth_Http_Utility;
        }
    }

    public function getData()
    {
        return $this->_data;
    }

    public function isValid()
    {
        if (isset($this->_params[self::TOKEN_PARAM_KEY])
            && !empty($this->_params[self::TOKEN_PARAM_KEY])) {
            return true;
        }
        return false;
    }

    protected function _parseData()
    {
        $params = array();
        if (empty($this->_data)) {
            return;
        }
        foreach ($this->_data as $key=>$value) {
            $params[rawurldecode($key)] = rawurldecode($value);
        }
        return $params;
    }

}