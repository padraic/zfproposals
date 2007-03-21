<?php

class Zend_Yaml_SimpleKey
{
    public $tokenNumber = null;
    public $required = null;
    public $index = null;
    public $line = null;
    public $column = null;
    public $mark = null;

    public function __construct($tokenNumber, $required, $index, $line, $column, $mark)
    {
        $this->$tokenNumber = $tokenNumber;
        $this->$required = $required;
        $this->$index = $index;
        $this->$line = $line;
        $this->$column = $column;
        $this->$mark = $mark;
    }
}