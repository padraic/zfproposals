<?php

interface Zend_Yaml_Parser_Event_Interface
{

    public function setEvent($type);

    public function getContent($key, $value);

    public function getProperty($key, $value);

    public function getError(Exception $exception, $line);

}