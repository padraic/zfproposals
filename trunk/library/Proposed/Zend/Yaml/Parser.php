<?php

require_once 'Zend/Yaml/Parser/Reader.php';
require_once 'Zend/Yaml/Character.php';
require_once 'Zend/Yaml/Parser/Event/Interface.php';

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
    protected $_yamlCharacter = null;
    protected $_properties = array();
    protected $_pendingEvent = null;

    public function __construct($data, Zend_Yaml_Parser_Event_Interface $event, Zend_Yaml_Character $yamlCharacter = null)
    {
        $this->_reader = new Zend_Yaml_Parser_Reader($data);
        $this->_parserEvent = $event;
        if (is_null($yamlCharacter)) {
            $this->_yamlCharacter = new Zend_Yaml_Character;
        } else {
            $this->_yamlCharacter = $yamlCharacter;
        }
    }

    public function indent()
    {
        $this->mark();
        $i = 0;
        $char = null;
        while ($this->_yamlCharacter->isType( $char = $this->_reader->read(), Zend_Yaml_Character::INDENT )) {
            echo '::', $char, '::';
            $i++;
        }
        if ($char == "\t") {
            throw new Exception('Invalid indentation; spaces, and not tabs, should be used to indent YAML content');
        }
        $this->reset();
        return $i;
    }

    public function harray($type)
    {
    }

    public function space()
    {
    }

    public function line()
    {
    }

    public function lineSpace()
    {
    }

    public function word()
    {
    }

    public function digit()
    {
    }

    public function indent2()
    {
    }

    public function newLine()
    {
    }

    public function end()
    {
    }

    public function stringSimple()
    {
    }

    public function stringLooseSimple()
    {
    }

    public function stringSingleQuote()
    {
    }

    public function stringDoubleQuote()
    {
    }

    public function stringLoose()
    {
    }

    public function string()
    {
    }

    // fix quotes at some point

    public function alias()
    {
    }

    public function anchor()
    {
    }

    public function comment()
    {
    }

    public function header()
    {
    }

    public function directive()
    {
    }

    public function transfer()
    {
    }

    public function properties()
    {
    }

    public function key()
    {
    }

    public function value()
    {
    }

    public function valueNa()
    {
    }

    public function valueInline()
    {
    }

    public function valueLooseInline()
    {
    }

    public function valueInlineNa()
    {
    }

    public function valueNested()
    {
    }

    public function valueBlock()
    {
    }

    public function nmap()
    {
    }

    public function nmapEntry()
    {
    }

    public function nList()
    {
    }

    public function startList()
    {
    }

    public function nlistEntry()
    {
    }

    public function nmapInList()
    {
    }

    public function block()
    {
    }

    public function blockLine()
    {
    }

    public function hlist()
    {
    }

    public function listEntry()
    {
    }

    public function map()
    {
    }

    public function mapEntry()
    {
    }

    public function documentFirst()
    {
    }

    public function documentNext()
    {
    }

    public function parse()
    {
    }

    public function mark()
    {
        $this->_reader->mark();
    }

    public function reset()
    {
        $this->_reader->reset();
    }

    public function unmark()
    {
        $this->_reader->unmark();
    }

    public function setEvent()
    {
    }

    public function getEvent()
    {
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