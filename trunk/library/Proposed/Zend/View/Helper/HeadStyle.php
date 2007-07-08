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
 * Helper to add a <style> tag value to a head->style Placeholder
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2007 Pádraic Brady
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_HeadStyle
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
     * Constants
     */
    const HEADSTYLE_NAMESPACE = 'ZEND_HEAD_STYLE';

    /**
     * Append a Head <meta> value if a parameter and
     * return self for further in-object calls
     *
     * @return Zend_View_Helper_HeadStyle
     */
    public function headStyle($value = null)
    {
        if (is_null($this->_placeholder)) {
            $this->_placeholder = $this->view->placeholder();
        }
        if (!is_null($value)) {
            $this->append($value);
        }
        return $this;
    }

    /**
     * Set view object
     *
     * @param  Zend_View_Interface $view
     * @return Zend_View_Helper_HeadStyle
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Check for the existence of the self::HEADSTYLE_NAMESPACE Placeholder key
     *
     * @return bool
     */
    public function has($index = null, $value = null)
    {
        return $this->_placeholder->has(self::HEADSTYLE_NAMESPACE, $index, $value);
    }

    /**
     * Append a value for a Placeholder self::HEADSTYLE_NAMESPACE key.
     *
     * @param mixed $value
     * @return void
     */
    public function append($value)
    {
        $this->_placeholder->append(self::HEADSTYLE_NAMESPACE, $value);
    }

    /**
     * Return the value of a Placeholder self::HEADSTYLE_NAMESPACE key
     *
     * @return mixed
     */
    public function get($index = null)
    {
        return $this->_placeholder->get(self::HEADSTYLE_NAMESPACE, $index);
    }

    /**
     * Unset the value of a Placeholder self::HEADSTYLE_NAMESPACE key
     *
     * @param string $index
     * @return void
     */
    public function remove($index = null, $value = null)
    {
        $this->_placeholder->remove(self::HEADSTYLE_NAMESPACE, $index, $value);
    }

    /**
     * toString function for this class
     *
     * @return string
     */
    public function __toString() {
        return $this->get();
    }

}
