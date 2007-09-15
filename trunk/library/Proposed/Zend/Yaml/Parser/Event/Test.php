<?php

class Zend_Yaml_Parser_Event_Test
{

    protected $_stack = array();
    protected $_index = -1;

    public function setEvent($type)
    {
        $this->_index = $this->_index + 1;
        $this->_stack[$this->_index]['event'] = $type;
    }

    public function setContent($key, $value)
    {
        if (!isset($this->_stack[$this->_index]['content'])) {
            $this->_stack[$this->_index]['content'] = array();
        }
        $this->_stack[$this->_index]['content'][$key] = $value;
    }

    public function setProperty($key, $value)
    {
        if (!isset($this->_stack[$this->_index]['property'])) {
            $this->_stack[$this->_index]['property'] = array();
        }
        $this->_stack[$this->_index]['property'][$key] = $value;
    }

    public function toString()
    {
        return var_export($this->_stack, true);
    }

}