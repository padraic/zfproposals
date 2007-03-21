<?php

class Zend_Yaml_Reader
{

    private $_stream = null;
    private $_streamPointer = 0;
    private $_eof = true;
    private $_buffer = null;
    private $_pointer = 0;
    private $_index = 0;
    private $_line = 0;
    private $_column = 0;
    private $_name = '';
    private $_rawBuffer = '';

    public function __construct($stream)
    {
        if (is_string($stream)) {
            $this->_name = '<string>';
            $this->_rawBuffer = $stream;
        } else {
            /** Should verify we get a valid streamed file */
            $this->_stream = $stream;
            $this->_name = '<file>';
            $this->_eof = false;
        }
        /** Add support of path parameters to be fread or file_get_contents */
    }

    public function peek($index = 0)
    {
        $temp = $this->_pointer + $index + 1;
        if($temp >= strlen($this->_buffer)) {
            $this->update($index + 1)
        }
        return $this->_buffer[$this->_pointer + $index];
    }

    public function prefix($length = 1)
    {
        $temp = $this->_pointer + $length;
        if($temp >= strlen($this->_buffer)) {
            $this->_update($length);
        }
        return substr($this->_buffer, $this->_pointer, $temp);
    }

    public function forward($length = 1)
    {
        $temp = $this->_pointer + $length + 1;
        if($temp >= strlen($this->_buffer))
        {
            $this->update($length + 1);
        }
        while($length)
        {
            $chr = $this->_buffer[$this->_pointer];
            $this->_pointer += 1;
            $this->_index += 1;
            if($chr == "\n" || $chr == "\x85") || ($chr == "\r" && $this->_buffer[$this->_pointer+1] !== "\n")) {
                $this->_line += 1;
                $this->_column = 0;
            } else {
                $this->_column += 1;
            }
        }
    }

    public function getMark()
    {
        if(is_null($this->_stream)) {
            $mark = new Zend_Yaml_Mark($this->_name, $this->_index, $this->_line, $this->_column, $this->_buffer, $this->_pointer); 
        } else {
            $mark = new Zend_Yaml_Mark($this->_name, $this->_index, $this->_line, $this->_column, null, null);
        }
        return $mark;
    }

    public function checkPrintable($string)
    {
        if(preg_match('%[^\x09\x0A\x0D\x20-\x7E\x85\xA0-\xFF]%', $string)) {
            require_once 'Zend/Yaml/Exception.php';
            throw new Zend_Yaml_Exception('Special characters are not allowed.');
        }
    }

    public function update($length)
    {
        if(is_null($this->_buffer)) {
            return;
        }
        $this->_buffer = substr($this->_buffer, $this->_pointer);
        while(strlen($this->_buffer) < $length)
        {
            if(!$this->_eof)
            {
                $this->updateRaw();
            }
            $data = $this->_rawBuffer;
            $converted = strlen($data);
            $this->checkPrintable($data);
            $this->_buffer .= $data;
            $this->_rawBuffer = substr($this->_rawBuffer, $converted);
            if($this->_eof)
            {
                $this->_buffer .= "\0"; // null byte to add in place of empty
                $this->_rawBuffer = null;
                break;
            }
        }
    }

    public function updateRaw($size = 1024)
    {
        $data = fread($this->_stream, $size);
        if($data && !empty($data)) {
            $this->_rawBuffer .= $data;
            $this->_streamPointer += strlen($data);
        } else {
            $this->_eof = true;
        }
    }

}