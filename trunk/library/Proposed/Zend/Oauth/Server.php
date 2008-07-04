<?php

require_once 'Zend/Oauth.php';

require_once 'Zend/Uri.php';

require_once 'Zend/Oauth/Config/Interface.php';

class Zend_Oauth_Server extends Zend_Oauth implements Zend_Oauth_Config_Interface
{

    protected $_config = null;

    public function __construct($options = null)
    {
        $this->_config = new Zend_Oauth_Config;
        if (!is_null($options)) {
            if ($options instanceof Zend_Config) {
                $options = $options->toArray();
            }
            $this->_config->setOptions($options);
        }
    }

    public function __call($method, $args) 
    {
        if (method_exists($this->_config, $method)) {
            return call_user_func_array(array($this->_config,$method), $args);
        }
        require_once 'Zend/Oauth/Exception.php';
        throw new Zend_Oauth_Exception('Method does not exist: '.$method);
    }

}