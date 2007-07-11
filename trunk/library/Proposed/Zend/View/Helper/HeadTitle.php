<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2007 Pádraic Brady
 * @version    $Id$
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Helper to add a <title> tag value to a head->title Placeholder
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2007 Pádraic Brady
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_HeadTitle
{

    /**
     * Instance of parent Zend_View object
     *
     * @var Zend_View_Abstract
     */
    public $view = null;

    /**
     * The default Zend_View_Helper_Placeholder instance
     *
     * @var Zend_View_Helper_Placeholder
     */
    protected $_placeholder = null;

    /**
     * A common prefix prefixed to all <title> text
     *
     * @var string
     */
    protected $_prefix = null;

    /**
     * Constants
     */
    const HEADTITLE_NAMESPACE = 'ZEND_HEAD_TITLE';

    /**
     * Set the Head <title> value if a parameter and
     * return self for further in-object call
     *
     * @return Zend_View_Helper_HeadTitle
     */
    public function headTitle($value = null)
    {
        if (is_null($this->_placeholder)) {
            $this->_placeholder = $this->view->placeholder();
        }
        if (!is_null($value)) {
            $this->set($value);
        }
        return $this;
    }

    /**
     * Set view object
     *
     * @param  Zend_View_Interface $view
     * @return Zend_View_Helper_HeadTitle
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Check for the existence of the self::HEADTITLE_NAMESPACE Placeholder key
     *
     * @return bool
     */
    public function has()
    {
        return $this->_placeholder->has(self::HEADTITLE_NAMESPACE);
    }

    /**
     * Set the value for a Placeholder self::HEADTITLE_NAMESPACE key.
     * Overwrites existing value.
     *
     * @param mixed $value
     * @return Zend_View_Helper_HeadTitle
     */
    public function set($value)
    {
        $this->_placeholder->set(self::HEADTITLE_NAMESPACE, $value);
        return $this;
    }

    /**
     * Return the value of a Placeholder self::HEADTITLE_NAMESPACE key
     *
     * @return mixed
     */
    public function get()
    {
        if ($this->_placeholder->has(self::HEADTITLE_NAMESPACE)) {
            return $this->_placeholder->get(self::HEADTITLE_NAMESPACE);
        }
        return null;
    }

    /**
     * Unset the value of a Placeholder self::HEADTITLE_NAMESPACE key
     *
     * @param string $index
     * @return Zend_View_Helper_HeadTitle
     */
    public function remove()
    {
        $this->_placeholder->remove(self::HEADTITLE_NAMESPACE);
        return $this;
    }

    /**
     * Add a common prefix to the <title> text
     *
     * @param string $prefix
     * @return Zend_View_Helper_HeadTitle
     */
    public function setPrefix($prefix)
    {
        $this->_prefix = $prefix;
        return $this;
    }

    /**
     * Get any common prefix to the <title> text
     *
     * @return Zend_View_Helper_HeadTitle
     */
    public function getPrefix($prefix)
    {
        return $this->_prefix;
    }

    /**
     * Alias to toString for public API
     * 
     * @return string
     */
    public function toString()
    {
        return $this->__toString();
    }

    /**
     * __toString function for this class
     *
     * @return string
     */
    public function __toString()
    {
        return '<title>' . $this->getPrefix() . $this->get() . '</title>';
    }

}
