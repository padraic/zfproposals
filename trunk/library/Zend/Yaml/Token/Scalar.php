<?php

class Zend_Yaml_Token_Scalar extends Token
{
    protected $_id = '<scalar>';
    protected $_isScalar = true;
    protected $_value = null;
    protected $_plain = null;
    protected $_style = null;

    public function setValue($value)
    {
        $this->_value = $value;
    }

    public function setPlain($plain)
    {
        $this->_plain = $plain;
    }

    public function setStyle($style)
    {
        $this->_style = $style;
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