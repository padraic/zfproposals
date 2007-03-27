<?php

class Zend_Yaml_Token_StreamStart extends Token
{
    protected $_id = '<stream start>';
    protected $_isStreamStart = true;
    protected $_encoding = null;

    public function setEncoding($enc)
    {
        $this->_encoding = $enc;
    }

    public function getEncoding()
    {
        return $this->_encoding;
    }
}