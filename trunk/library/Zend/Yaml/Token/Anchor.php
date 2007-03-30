<?php

class Zend_Yaml_Token_Anchor extends Zend_Yaml_Token
{
    protected $_id = '<anchor>';
    protected $_isAnchor = true;
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