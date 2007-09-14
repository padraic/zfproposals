<?php

class Zend_Yaml_Decoder
{

    protected $_config = null;
    protected $_input = null;
    protected $_parser = null;

    public function __construct($input, Zend_Yaml_Config $config = null)
    {
        $this->_input = $input;
        if (!is_null($config)) {
            $this->_config = $config;
        }
    }

    public function decode()
    {
        try {
            $event = new Zend_Yaml_Parser_Event;
            if (is_null($this->_parser)) {
                $this->_parser = new Zend_Yaml_Parser($this->_input, $event);
                $this->firstDocument($parser, $event);
            } else {
                $this->_parser->setEvent($event);
                if (!$this->nextDocument($parser, $event)) {
                    throw new Exception('EOF');
                }
            }
            $return = $event->get();
            if (is_null($return)) {
                throw new Exception('The YAML document is empty');
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    // check for event error handling at some later point

    public function firstDocument(Zend_Yaml_Parser $parser, Zend_Yaml_Parser_Event_Interface $event)
    {
        while ($parser->catchComment(-1, false)) {
        }
        if (!$parser->catchHeader()) {
            $parser->catchDocumentFirst();
        } else {
            $parser->catchValueNa(-1);
        }
    }

    public function nextDocument(Zend_Yaml_Parser $parser, Zend_Yaml_Parser_Event_Interface $event)
    {
        return $parser->catchDocumentNext();
    }

}