<?php

class Zend_Yaml_Parser
{

    private $_scanner = null;
    private $_currentEvent = null;
    private $_yamlVersion = null;
    private $_events = null;
    private $_workingEvents = null;
    private $_tagHandles = array();
    private $_parseStack = null;

    public function __construct(Zend_Yaml_Lexer $lexer)
    {
        $this->_scanner = $lexer;
    }

    public function checkEvent(array $choices)
    {
        $this->_parseStream();
        if (empty($this->_currentEvent)) {
            $this->_currentEvent = $this->_parseStreamNext();
        }
        if (!empty($this->_currentEvent)) {
            if (empty($choices)) {
                return true;
            }
            foreach ($choices as $choice) {
                if ($choice == $this->_currentEvent) {
                    return true;
                }
            }
        }
        return false;
    }

    public function peekEvent()
    {
        $this->_parseStream();
        if (empty($this->_currentEvent)) {
            $this->_currentEvent = $this->_parseStreamNext();
        }
        return $this->_currentEvent;
    }

    public function getEvent()
    {
        $this->_parseStream();
        if (empty($this->_currentEvent)) {
            $this->_currentEvent = $this->_parseStreamNext();
        }
        $return = $this->_currentEvent;
        $this->_currentEvent = null;
        return $return;
    }

}