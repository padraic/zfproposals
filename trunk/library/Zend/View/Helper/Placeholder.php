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
    protected $_registry = null
    
    public function __construct()
    {
        $this->_registry = new Zend_Registry(array(), ArrayObject::ARRAY_AS_PROPS);
    }

    public function placeholder()
    {
        return $this;
    }

    public function has($index)
    {
        return $this_>_registry->isRegistered($index);
    }

    public function append($index, $value)
    {
        $this->_registry->{$index} .= $value;
    }

    public function set($index, $value)
    {
        $this->_registry->{$index} = $value;
    }

    public function get($index)
    {
        return $this->_registry->{$index};
    }

    public function remove($index)
    {
        unset($this->_registry->{$index});
    }
    
}