<?php

require_once 'Zend/Loader.php';

require_once 'Zend/Registry.php';

class Zend_Factory
{

    protected static $_registry = null;

    public static function create($className, array $constructionParams = null) 
    {
        if (!class_exists($className, true)) {
            Zend_Loader::loadClass($className);
        }
        if (isset(self::$_registry->$className)) {
            return self::$_registry->$className;
        }
        if ($constructionParams !== null) {
            $refClass = new ReflectionClass($className);
            $createdObject = $refClass->newInstanceArgs($constructionParams);
        } else {
            $createdObject = new $className;
        }
        return $createdObject;
    }

    public static function replaceClass($className, $withObject) 
    {
        if (self::$_registry == null) {
            self::$_registry = new Zend_Registry(array(), ArrayObject::ARRAY_AS_PROPS);
        }
        self::$_registry->$className = $withObject;
    }
}