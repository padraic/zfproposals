<?php

class Zend_Yaml_Token_Tag extends Token
{
    protected $_id = '<tag>';
    protected $_isTag = true;
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