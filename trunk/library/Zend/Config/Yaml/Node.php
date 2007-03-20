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
 * This class forms part of a proposal for the Zend Framework. The attached
 * copyright will be transferred to Zend Technologies USA Inc. upon future
 * acceptance of that proposal:
 *      http://framework.zend.com/wiki/pages/viewpage.action?pageId=20369
 *
 * @category   Zend
 * @package    Zend_Config
 * @copyright  Copyright (c) 2007 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @uses       Zend_Config
 * @category   Zend
 * @package    Zend_Config
 * @copyright  Copyright (c) 2007 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Config_Yaml_Node
{
    private $_id = null;

    private $_indent = null;

    public $data = array();

    private $_parentId = null;

    private $_children = false;

    public function __construct($id)
    {
        $this->setId($id);
    }

    public function setIndent($indent)
    {
        $this->_indent = intval($indent);
    }

    public function getIndent()
    {
        return $this->_indent;
    }

    public function setId($id)
    {
        $this->_id = intval($id);
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setParentId($id)
    {
        $this->_parentId = intval($id);
    }

    public function getParentId()
    {
        return $this->_parentId;
    }

    public function setChildren($bool = true)
    {
        if(is_bool($bool))
        {
            $this->_children = $bool;
        }
    }

    public function hasChildren()
    {
        return $this->_children;
    }

}