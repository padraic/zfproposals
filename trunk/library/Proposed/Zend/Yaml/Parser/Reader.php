<?php

class Zend_Yaml_Parser_Reader
{
    
    protected $_reader = null;
    protected $_char = null;
    protected $_buffer = array();
    protected $_index = 0;
    protected $_fileIndex = 0;
    protected $_level = 0;
    protected $_eofIndex = -1;
    protected $_mark = array();

    protected $_rawPointer = 0;
    protected $_rawBuffer = null;
    protected $_rawLength = 0;

    public function __construct($string)
    {
        $this->_rawBuffer = $string;
        $this->_rawLength = strlen($string);
    }

    public function read()
    {
        if ($this->_index == $this->_eofIndex) {
            $this->_index++;
            return -1;
        } elseif ($this->_index != $this->_rawPointer) {
            return $this->_buffer[$this->_index];
        } else {
            if ($this->_eofIndex != -1) {
                return -1;
            }
            $this->_char = $this->_next();
            if ($this->_char == -1) {
                $this->_eofIndex = $this->_index;
            }    
            $this->_buffer[$this->_index] = $this->_char;
        }
        $this->_index++;
        return $this->_char;
    }

    public function unread()
    {
        if ($this->_index == 0) {
            return;
        }
        $this->_index--;
    }

    public function current()
    {
        $this->read();
        $this->unread();
        return $this->_char;
    }

    public function previous()
    {
        if ($this->_index <= 0) {
            return false;
        } elseif ($this->_index == 1) { // ?
            return false;
        } else {
            return $this->_buffer[$this->_index - 2];
        }
    }

    public function mark()
    {
        $this->_mark[$this->_level] = $this->_index;
        $this->_level++;
    }

    public function unmark()
    {
        $this->_level--;
        if ($this->_level < 0) {
            throw new Exception("There are no further Marks remaining which can be unmarked");
        }
    }

    public function reset()
    {
        $this->unmark();
        $this->_index = $this->_mark[$this->_level];
    }

    public function toString()
    {
        if ($this->_level == 0) {
            return '';
        }
        $begin = $this->_mark[$this->_level-1];
        
        if ($begin > $this->_index) {
            // do something suitable
        } else {
            $length = $this->_index - $begin;
            $pointer = $begin;
            $string = '';
            while ($length > 0) {
                $string .= $this->_buffer[$pointer];
                $pointer++;
                $length--;
            }
            return $string;
        }
    }

    public function __toString()
    {
        return $this->toString();
    }

    protected function _next()
    {
        if ($this->_rawPointer < $this->_rawLength) {
            $next = $this->_rawBuffer[$this->_rawPointer];
        } else {
            return -1;
        }
        $this->_rawPointer++;
        return $next;
    }

}