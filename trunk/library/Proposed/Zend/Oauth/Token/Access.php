<?php

require_once 'Zend/Oauth/Token.php';

class Zend_Oauth_Token_Access extends Zend_Oauth_Token
{

    protected $_data = array();

    public function __construct(array $data = null)
    {
        if (!is_null($data)) {
            $this->_data = $data;
            $this->setParams($this->_parseParameters());
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

    protected function _parseParameters()
    {
        if (empty($this->_data)) {
            return;
        }
        foreach ($this->_data as $key=>$value) {
            $params[urldecode($key)] = urldecode($value);
        }
        return $params;
    }

}