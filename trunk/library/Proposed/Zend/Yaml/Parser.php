<?php

require_once 'Zend/Yaml/Parser/Reader.php';

class Zend_Yaml_Parser
{
    
    /** Event constants */
    const LIST_OPEN = '[';
    const LIST_CLOSE = ']';
    const MAP_OPEN = '{';
    const MAP_CLOSE = '}';
    const LIST_NO_OPEN = 'n';
    const MAP_NO_OPEN = 'N';
    const DOCUMENT_HEADER = 'H';
    const MAP_SEPARATOR = ':';
    const LIST_ENTRY = '-';

    protected $_reader = null;
    protected $_line = 1;
    protected $_parserEvent = null;
    protected $_properties = array();
    protected $_pendingEvent = null;

    public function __construct($data, Zend_Yaml_Parser_Event_Interface $event)
    {
        $this->_reader = new Zend_Yaml_Parser_Reader($data);
        $this->_parserEvent = $event;
    }



    protected function getReaderString()
    {
        return $this->_reader->toString();
    }

    protected function removeEvents()
    {
        $this->_properties = array();
    }

    protected function sendEvents()
    {
        $string = '';

    }
}