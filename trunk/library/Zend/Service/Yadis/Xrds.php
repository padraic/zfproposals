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
 * @package    Zend_Service
 * @subpackage Yadis
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'Zend/Service/Yadis/Exception.php';

/**
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Yadis_Xrds implements SeekableIterator
{

    /**
     * The Yadis Services resultset
     *
     * @var array
     */ 
    protected $_services;
 
    /**
     * Current key/pointer for the Iterator
     * 
     * @var integer
     */ 
    private $_currentKey = 0;

    /**
     * Contains the valid xrd:XRD node parsed from the XRD document.
     *
     * @var SimpleXMLElement
     */
    private $_xrdNode = null;
 
    /**
     * Parse the XRD document into a set of Service objects.
     * 
     * @param SimpleXMLElement $xrdsDocument
     */ 
    public function __construct(SimpleXMLElement $xrds)
    {
        $xrdNode = $this->_getValidXrdNode($xrds);
        if(!$xrdNode)
        {
            throw new Zend_Service_Yadis_Exception('The XRD document was found to be invalid');
        }
        $this->_xrdNode = $xrdNode;
        $services = $xrdNode->xpath('xrd:Service');
        var_dump($services);
    }
 
    /**
     * Implements SeekableIterator::current()
     * 
     * Return the current element.
     *
     * @return Zend_Service_Yadis_Service
     */ 
    public function current()
    {}
 
    /**
     * Implements SeekableIterator::key()
     *
     * Return the key of the current element.
     * 
     * @return integer
     */ 
    public function key()
    {}
 
    /**
     * Implements SeekableIterator::next()
     * 
     * Increments pointer to next Service object.
     */ 
    public function next()
    {}
 
    /**
     * Implements SeekableIterator::rewind()
     * 
     * Rewinds the Iterator to the first Service object
     *
     * @return boolean
     */ 
    public function rewind()
    {}
 
    /**
     * Implements SeekableIterator::seek()
     * 
     * Seek to an absolute position.
     *
     * @param  integer $key
     * @return Zend_Service_Yadis_Service
     * @throws Zend_Service_Yadis_Exception
     */ 
    public function seek($key)
    {}
 
    /**
     * Implement SeekableIterator::valid()
     *
     * @param  integer $key
     * @return boolean
     */ 
    public function valid($key = null)
    {}

    protected function _getValidXrdNode(SimpleXMLElement $xrds)
    {
        /**
         * Verify the XRDS resource has a root element called "xrds:XRDS".
         */
        $root = $xrds->xpath('/xrds:XRDS[1]');
        if(count($root) == 0)
        {
            return null;
        }

        /**
         * Check namespace urls of standard xmlns (no suffix) or xmlns:xrd
         * (if present and of priority) for validity.
         * No loss if neither exists, but they really should be.
         */
        $nameSpaces = $xrds->getDocNamespaces();
        if(array_key_exists('xrd', $nameSpaces) && $nameSpaces['xrd'] != 'xri://$xrd*($v*2.0)')
        {
            return null;
        }
        elseif(array_key_exists('', $nameSpaces) && $nameSpaces[''] != 'xri://$xrd*($v*2.0)')
        {
            return null;
        }

        /**
         * Grab the XRD element which contains details of the service provider's
         * Server url, service types, and other details. We should take only one
         * XRD element as this is a requirement of the Yadis Specification.
         */
        $xrdNodes = $xrds->xpath('/xrds:XRDS[1]/xrd:XRD');
        if(!$xrdNodes)
        {
            return null;
        }
        return $xrdNodes[count($xrdNodes) - 1];
    }

}