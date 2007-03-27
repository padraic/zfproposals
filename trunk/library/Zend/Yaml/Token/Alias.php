<?php

class Zend_Yaml_Token_Alias extends Token
{
    protected $_id = '<alias>';
    protected $_isAlias = true;
    protected $_value = null;

    public function setValue($value)
    {
        $this->_value = $value;
    }

    public function getValue()
    {
        return $this->_value;
    }
}