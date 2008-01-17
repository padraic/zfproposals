<?php

require_once 'Zend/Loader.php';

require_once 'Zend/Registry.php';

/**
 * Abstract object instantiation with minimal setup and allow for replacement
 * of created object return values by preceding unit tests or BDD specifications
 *
 */
class Zend_Factory
{

    /**
     * Registry instance for holding replacement objects
     *
     * @var Zend_Registry
     */
    protected static $_registry = null;

    /**
     * Create a new object based on the referenced class name and construction
     * parameters. If a registered replacement object exists, this will be
     * returned instead.
     *
     * @param object $className
     * @param array $constructionParams
     * @return object
     */
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

    /**
     * Replace the return value of any call for an instance of the referenced
     * class name with an alternative object.
     *
     * @param string $className
     * @param object $withObject
     */
    public static function replaceClass($className, $withObject) 
    {
        if (self::$_registry == null) {
            self::$_registry = new Zend_Registry(array(), ArrayObject::ARRAY_AS_PROPS);
        }
        self::$_registry->$className = $withObject;
    }
    
    /**
     * Clear the registry of replacement objects
     *
     */
    public static function clearRegistry()
    {
        if(isset(self::$_registry)) {
            self::$_registry = null;
        }
    }
}