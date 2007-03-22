<?php

class Zend_Yaml_Token_Directive extends Token
{
    protected $_id = '<directive>';
    protected $_isDirective = true;
    private $_name = null;
    private $_value = null;

    public function __construct($name, $value)
    {
        $this->_name = $name;
        $this->_value = $value;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getValue()
    {
        return $this->_value;
    }
}