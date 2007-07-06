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
        $this->_registry->ZEND_HEAD = array();
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
     * @param string $index
     * @return bool
     */
    public function has($index)
    {
        return isset($this->_registry->$index);
    }

    /**
     * Append a value string to an existing Placeholder key
     *
     * @param string $index
     * @param mixed $value
     * @return void
     */
    public function append($index, $value)
    {
        if ($this->has($index)) {
            $this->_registry->$index = $this->_registry->$index . $value;
            return;
        }
        $this->_registry->$index = $value;
    }

    /**
     * Set the value for a Placeholder key. Overwrites existing value.
     *
     * @param string $index
     * @param mixed $value
     * @return void
     */
    public function set($index, $value)
    {
        $this->_registry->$index = $value;
    }

    /**
     * Return the value of a Placeholder key
     *
     * @param string $index
     * @return mixed
     */
    public function get($index)
    {
        if ($this->has($index)) {
            return $this->_registry->$index;
        }
        return null;
    }

    /**
     * Unset the value of a Placeholder key
     *
     * @param string $index
     * @return void
     */
    public function remove($index)
    {
        unset($this->_registry->$index);
    }

}