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
 * Helper to add a <link> tag value to a head->link Placeholder
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2007 Pádraic Brady
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_HeadLink
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
     * Attributes common to <link> elements
     *
     * @var string
     */
    protected $_attributes = array('href', 'charset', 'hreflang', 'media', 'rel', 'rev', 'target', 'type', 'id', 'class', 'title', 'style', 'dir', 'lang', 'xml:lang');

    /**
     * Constants
     */
    const HEADLINK_NAMESPACE = 'ZEND_HEAD_LINK';

    /**
     * Set the Head <link> value if a parameter and
     * return self for further in-object call
     *
     * @return Zend_View_Helper_HeadLink
     */
    public function headLink(array $attributes = null)
    {
        if (is_null($this->_placeholder)) {
            $this->_placeholder = $this->view->placeholder();
        }
        if (!is_null($attributes)) {
            $this->append($attributes);
        }
        return $this;
    }

    /**
     * Set view object
     *
     * @param  Zend_View_Interface $view
     * @return Zend_View_Helper_HeadLink
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Check for the existence of the self::HEADLINK_NAMESPACE Placeholder key
     *
     * @return bool
     */
    public function has($index = null, array $attributes = null)
    {
        return $this->_placeholder->has(self::HEADLINK_NAMESPACE, $index, $attributes);
    }

    /**
     * Set the value for a Placeholder self::HEADLINK_NAMESPACE key.
     * Overwrites existing value.
     *
     * @param array $attributes
     * @param int $index
     * @return Zend_View_Helper_HeadLink
     */
    public function append(array $attributes, $index = null)
    {
        $keys = array_keys($attributes);
        foreach($keys as $key) {
            if (!in_array($key, $this->_attributes)) {
                require_once 'Zend/View/Exception.php';
                throw new Zend_View_Exception('Invalid <link> attribute: ' . $key);
            }
        }
        $this->_placeholder->append(self::HEADLINK_NAMESPACE, $attributes, $index);
        return $this;
    }

    /**
     * Return the value of a Placeholder self::HEADLINK_NAMESPACE key
     *
     * @return mixed
     */
    public function get($index = null)
    {
        if ($this->_placeholder->has(self::HEADLINK_NAMESPACE, $index)) {
            return $this->_placeholder->asArray(self::HEADLINK_NAMESPACE, $index);
        }
        return null;
    }

    /**
     * Unset the value of a Placeholder self::HEADLINK_NAMESPACE key
     *
     * @param int $index
     * @return Zend_View_Helper_HeadLink
     */
    public function remove($index, array $attributes = null)
    {
        $this->_placeholder->remove(self::HEADLINK_NAMESPACE, $index, $attributes);
        return $this;
    }

    /**
     * Alias to toString for public API
     * 
     * @param int $index
     * @return string
     */
    public function toString($index = null)
    {
        return $this->__toString($index);
    }

    /**
     * __toString function for this class
     *
     * @param int $index
     * @return string
     */
    public function __toString($index = null)
    {
        $attributeList = $this->get($index);
        $output = '';
        foreach ($attributeList as $attributes) {
            $link = '<link ';
            $link .= $this->_merge($attributes);
            $link .= ' />';
            $output[] = $link;
        }
        return implode("\n", $output);
    }

    /**
     * Merges key=>value pairs into a form suitable for insertion into
     * a <link> string.
     *
     * @param array $attributes
     * @return string
     */
    protected function _merge(array $attributes)
    {
        $mergeString = array();
        foreach($attributes as $key=>$value) {
            $mergeString[] = $key . '="' . $value . '"';
        }
        return implode(' ', $mergeString);
    }

}
