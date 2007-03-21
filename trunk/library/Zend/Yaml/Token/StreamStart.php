<?php

class Zend_Yaml_Token_StreamStart extends Token
{
    protected $_id = '<stream start>';
    protected $_isStreamStart = true;
    protected $_encoding = null;

    public function __construct($startMark, $endMark, $encoding)
    {
        $this->_encoding = $encoding;
        parent::__construct($startMark, $endMark);
    }

    public function getEncoding()
    {
        return $this->_encoding;
    }
}