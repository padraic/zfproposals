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
    protected $_registry = null;

    /**
     * Constructor; instantiate the object with a Zend_Registry object property
     *
     * @return void
     */
    public function __construct()
    {
        $this->_registry = new Zend_Registry;
    }

    /**
     * Return the current instance of Zend_Registry
     *
     * @return Zend_Registry
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
            if (isset($this->_registry->$key)) {
                $value = $this->get($key, $index);
                return !empty($value);
            }
        }
        return isset($this->_registry->$key);
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
            $this->_registry->$key[] = $value;
            return;
        }
        $this->_registry->$key = array($value);
    }

    /**
     * Set the value for a Placeholder key. Overwrites existing value.
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
                $this->_registry->$key[$index] = $value;
            } else {
                $this->_registry->$key[] = $value;
            }
            return;
        }
        $this->_registry->$key = array();
        if (!is_null($index)) {
            $this->_registry->$key[$index] = $value;
        } else {
            $this->_registry->$key[] = $value;
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
                return $this->_toString($this->_registry->$key[$index]);
            }
            return $this->_toString($this->_registry->$key);
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
            unset($this->_registry->$key[$index]);
            return;
        } elseif (!is_null($value)) {
            foreach($this->_registry->$key as $k => $v)
                if ($v == $value) {
                    unset($this->_registry->$key[$k]);
                }
            }
            return;
        }
        unset($this->_registry->$key);
    }

    /**
     * Flatten the array of indexed values for output
     *
     * @param array $array
     * @return string
     */
    protected function _toString($array) {
        if (!is_array($array)) {
            return $array;
        }
        $count = count($array);
        if ($count == 0 || $count == 1) {
            return (string) $array;
        }
        $unsortedArray = $array;
        ksort($unsortedArray);
        return implode("\n", $unsortedArray);
    }

}