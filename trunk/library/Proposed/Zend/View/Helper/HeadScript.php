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
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Helper to insert or append <style> tags to the ZEND_HEAD Placeholder
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2007 Pádraic Brady
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_HeadScript
{

    /**
     * Instance of parent Zend_View object
     *
     * @var Zend_View_Abstract
     */
    public $view = null

    /**
     * The default Zend_View_Helper_Placeholder instance
     *
     * @var Zend_View_Helper_Placeholder
     */
    protected $_placeholder = null;

    /**
     * Constructor; assigns a Zend_View_Helper_Placeholder object.
     *
     */
    public function __construct() {
        $this->_placeholder = $this->view->placeholder();
    }

    /**
     * Return self for further in-object calls
     *
     * @return Zend_View_Helper_HeadScript
     */
    public function headScript($file = null, $type = null, $index = null)
    {
        if (isset($file)) {

        }
        return $this;
    }

    /**
     * Check for the existence of the self::HEADSCRIPT_NAMESPACE Placeholder key
     *
     * @return bool
     */
    public function has($index = null, $file = null, $type = 'javascript')
    {
        $value = null;
        if (!is_null($file)) {
            $value = array($file, $type);
        }
        return $this->_placeholder->has(self::HEADSCRIPT_NAMESPACE, $index, $value);
    }

    /**
     * Append a value for a Placeholder self::HEADSCRIPT_NAMESPACE key.
     *
     * @param mixed $value
     * @return void
     */
    public function append($file, $type = 'javascript')
    {
        $this->_placeholder->append(self::HEADSCRIPT_NAMESPACE, array($file, $type));
    }

    /**
     * Return the value of a Placeholder self::HEADSCRIPT_NAMESPACE key
     *
     * @return mixed
     */
    public function get($index = null)
    {
        if ($this->_placeholder->has(self::HEADSCRIPT_NAMESPACE, $index)) {
            return $this->_placeholder->get(self::HEADSCRIPT_NAMESPACE, $index);
        }
        return null;
    }

    /**
     * Unset the value of a Placeholder self::HEADSCRIPT_NAMESPACE key
     *
     * @param string $index
     * @return void
     */
    public function remove($index = null, $file = null, $type = 'javascript')
    {
        $value = null;
        if (!is_null($file)) {
            $value = array($file, $type);
        }
        $this->_placeholder->remove(self::HEADSCRIPT_NAMESPACE, $index, $value);
    }

    /**
     * toString function for this class
     *
     * @return string
     */
    public function __toString() {
        $scripts = $this->get();
        if (is_null($scripts) || !is_array($scripts) || count($scripts) <= 0) {
            return '';
        }
        $output = array();
        foreach ($scripts as $script) {
            switch ($script[1]) {
                case 'javascript':
                default:
                    $output[] = '<script type="text/javascript" src="' . $script[0] . '"></script>';
                    break;
            }
        }
        return implode(PHP_EOL, $output);
    }

    /**
     * Set view object
     *
     * @param  Zend_View_Interface $view
     * @return Zend_View_Helper_HeadScript
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
        return $this;
    }

}