<?php

class Zend_Yaml_Token_Tag extends Token
{
    protected $_id = '<tag>';
    protected $_isTag = true;
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