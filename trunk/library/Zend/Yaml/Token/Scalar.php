<?php

class Zend_Yaml_Token_Scalar extends Token
{
    protected $_id = '<scalar>';
    protected $_isScalar = true;
    protected $_value = null;
    protected $_plain = null;
    protected $_style = null;

    public function __construct($value, $plain, $startMark, $endMark, $style = null) {
        $this->_value = $value;
        $this->_plain = $plain;
        $this->_style = $style;
        parent::__construct($startMark, $endMark);
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function getPlain()
    {
        return $this->_plain;
    }

    public function getStyle()
    {
        return $this->_style;
    }
}