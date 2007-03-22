<?php

class Zend_Yaml_SimpleKey
{
    public $tokenNumber = null;
    public $required = null;
    public $column = null;

    public function __construct($tokenNumber, $required, $column)
    {
        $this->$tokenNumber = $tokenNumber;
        $this->$required = $required;
        $this->$column = $column;
    }
}