<?php

class Zend_Yaml_Token_Alias extends Token
{
    protected $_id = '<alias>';
    protected $_isAlias = true;
    protected $_value = null;

    public function __construct($value, $startMark, $endMark) {
        $this->_value = $value;
        parent::__construct($startMark, $endMark);
    }

    public function getValue()
    {
        return $this->_value;
    }
}