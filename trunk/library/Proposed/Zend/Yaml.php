<?php

require_once 'Zend/Yaml/Decoder.php';

class Zend_Yaml
{

    //public static $config = null;

    public static function load($input)
    {
        //if (is_null(self::$config)) {
        //    self::$config = Zend_Yaml_Config::getDefaultConfig();
        //}
        $decoder = new Zend_Yaml_Decoder($input);
        return $decoder->decode();
    }
}