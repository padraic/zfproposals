<?php
/**
 * Zend Framework
 *
 *
 * @package    Zend_View
 * @subpackage Helpers
 * @copyright  Copyright (c) 2007 Pádraic Brady (http://blog.astrumfutura.com)
 * @version    $Id$
 * @license    New BSD
 */

/** Zend_Registry */
require_once 'Zend/Registry.php';

/**
 * Helper for passing data between otherwise segregated Views. In essence this
 * is just a proxy to a specific centralised Registry. It's called Placeholder to
 * make its typical usage obvious, but can be used just as easily for non-Placeholder
 * things. That said, the support for this is only guaranteed to effect Layouts.
 *
 * @package    Zend_View
 * @subpackage Helpers
 * @copyright  Copyright (c) 2007 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    New BSD
 */
class Zend_View_Helper_Placeholder {

    /**
     * Registry to store Placeholder values for later queries from layouts or
     * templates.
     * @var Zend_Registry
     */
    protected static $_registry = null;

    protected $_callback = null;

    /**
     * Constructor; instantiate the object with a Zend_Registry object property
     *
     * @return void
     */
    public function __construct()
    {
        if (self::$_registry == null) {
            self::$_registry = new Zend_Registry;
        }
    }

    /**
     * Return the current object
     *
     * @return Zend_View_Helper_Placeholder
     */
    public function placeholder()
    {
        return $this;
    }

    /**
     * Check for the existence of the named Placeholder key
     *
     * @param string $key
     * @param mixed $index
     * @return bool
     */
    public function has($key, $index = null)
    {
        if (!is_null($index)) {
            if (isset(self::$_registry->$key)) {
                $value = $this->get($key, $index);
                return !empty($value);
            }
        }
        return isset(self::$_registry->$key);
    }

    /**
     * Append a value string to an existing Placeholder key
     * without any overwriting or index value
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function append($key, $value)
    {
        if ($this->has($key)) {
            self::$_registry->$key[] = $value;
            return;
        }
        self::$_registry->$key = array($value);
    }

    /**
     * Sets the value for a Placeholder key, or sets the value for a specific index
     * on this key (e.g. to set a rendering order). If an index is not defined, it
     * is assumed a single value per key is to be set and any pre-existing array
     * will be replaced with this single value.
     *
     * If you want to set multiple values where ordering isn't necessary, use the
     * Zend_View_Helper_Placeholder::append() method instead. This will append
     * values using the default array indexation.
     *
     * @param string $key
     * @param mixed $value
     * @param mixed $index
     * @return void
     */
    public function set($key, $value, $index = null)
    {
        if ($this->has($key)) {
            if (!is_null($index)) {
                self::$_registry->$key[$index] = $value;
            } else {
                self::$_registry->$key = array($value);
            }
            return;
        }
        self::$_registry->$key = array();
        if (!is_null($index)) {
            self::$_registry->$key[$index] = $value;
        } else {
            self::$_registry->$key = array($value);
        }
    }

    /**
     * Return the value of a Placeholder key
     *
     * @param string $key
     * @param mixed $index
     * @return mixed
     */
    public function get($key, $index = null)
    {
        if ($this->has($key, $index)) {
            if (!is_null($index)) {
                return $this->_toString(self::$_registry->$key[$index]);
            }
            return $this->_toString(self::$_registry->$key);
        }
        return null;
    }

    /**
     * Unset the value of a Placeholder key
     *
     * @param string $key
     * @param mixed $index
     * @param string $value
     * @return void
     */
    public function remove($key, $index = null, $value = null)
    {
        if (!is_null($index)) {
            unset(self::$_registry->$key[$index]);
            return;
        } elseif (!is_null($value)) {
            foreach(self::$_registry->$key as $k => $v)
                if ($v == $value) {
                    unset(self::$_registry->$key[$k]);
                }
            }
            return;
        }
        unset(self::$_registry->$key);
    }

    /**
     * Register a callback function which is passed the Placeholder array
     * for a given key, along with the key name. This callback function may
     * then proceed to order the array in such a way as to determine the
     * sorting order by which element values are rendered into the Placeholder
     * injection point in any calling template.
     *
     * @param string $function
     * @param object $object
     * @return void
     */
    public function registerCallback($function, $object = null) {
        if (isset($object) && is_object($object)) {
            if (!method_exists($function, $object)) {
                throw new Zend_View_Exception();
            }
            $this->_callback = array($object, $function);
            return;
        }
        if (function_exists($function)) {
            $this->_callback = $function
        } else {
            throw new Zend_View_Exception();
        }
    }

    /**
     * Flatten the array of indexed values for output and return as
     * a string after the value array has been sorted.
     *
     * @todo determine a reasonable sorting strategy for implementation
     * @param array $array
     * @return string
     */
    protected function _toString($array, $key = null) {
        if (!is_array($array)) {
            return $array;
        }
        $count = count($array);
        if ($count == 0 || $count == 1) {
            return (string) $array;
        }
        $unsortedArray = $array;
        if (isset($this->_callback)) {
            $unsortedArray = call_user_func_array($this->_callback, array($unsortedArray, $key));
        } else {
            ksort($unsortedArray);
        }
        return implode("\n", $unsortedArray);
    }

}