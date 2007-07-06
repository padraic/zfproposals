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
 * @version    $Id: Placeholder.php 82 2007-07-06 21:27:46Z padraic $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Helper to insert or append <meta> tags to the ZEND_HEAD Placeholder
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2007 Pádraic Brady
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_HeadMeta
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
        if (!isset($this->_placeholder->ZEND_HEAD->['ZEND_META'])) {
            $this->_placeholder->ZEND_HEAD->['ZEND_META'] = array();
        }
    }

    /**
     * Return self for further in-object calls
     *
     * @return Zend_View_Helper_HeadMeta
     */
    public function headMeta()
    {
        return $this;
    }

    /**
     * Set view object
     *
     * @param  Zend_View_Interface $view
     * @return Zend_View_Helper_HeadMeta
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
        return $this;
    }

}
