<?php

class Zend_Yaml_Token_Directive extends Zend_Yaml_Token
{
    protected $_id = '<directive>';
    protected $_isDirective = true;
    private $_name = null;
    private $_value = null;

    public function setName($name)
    {
        $this->_name = $name;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setValue($value)
    {
        $this->_value = $value;
    }

    public function getValue()
    {
        return $this->_value;
    }
}