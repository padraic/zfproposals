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
 * @package    Zend_Service
 * @subpackage Yadis
 * @copyright  Copyright (c) 2007 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Service_Yadis_Xrds_Namespace */
require_once 'Zend/Service/Yadis/Xrds/Namespace.php';

/**
 * The Zend_Service_Yadis_Xrds class is a wrapper for elements of an
 * XRD document which is parsed using SimpleXML, and contains methods for
 * retrieving data about the document. The concrete aspects of retrieving
 * specific data elements is left to a concrete subclass.
 *
 * @uses       SeekableIterator
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Yadis
 * @author     Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Yadis_Xrds
{
 
    /**
     * Current key/pointer for the Iterator
     * 
     * @var integer
     */ 
    protected $_currentKey = 0;

    /**
     * Contains the valid xrd:XRD nodes parsed from the XRD document.
     *
     * @var SimpleXMLElement
     */
    protected $_xrdNodes = null;

    /**
     * Instance of Zend_Service_Yadis_Xrds_Namespace for managing namespaces
     * associated with an XRDS document.
     *
     * @var Zend_Service_Yadis_Xrds_Namespace
     */
    protected $_namespace = null;
 
    /**
     * Constructor; parses and validates an XRD document. All access to
     * the data held in the XML is left to a concrete subclass specific to
     * expected XRD format and data types.
     * Cannot be directly instantiated; must call from subclass.
     * 
     * @param   SimpleXMLElement $xrds
     * @param   Zend_Service_Yadis_Xrds_Namespace $namespace
     */ 
    protected function __construct(SimpleXMLElement $xrds, Zend_Service_Yadis_Xrds_Namespace $namespace)
    {
        $this->_namespace = $namespace;
        $xrdNodes = $this->_getValidXrdNodes($xrds);
        if(!$xrdNodes){
            require_once 'Zend/Service/Yadis/Exception.php';
            throw new Zend_Service_Yadis_Exception('The XRD document was found to be invalid');
        }
        $this->_xrdNodes = $xrdNodes;
    }
 
    /**
     * Add a list (array) of additional namespaces to be utilised by the XML
     * parser when it receives a valid XRD document.
     *
     * @param   array $namespaces
     * @return  Zend_Service_Yadis
     */
    public function addNamespaces(array $namespaces)
    {
        $this->_namespace->addNamespaces($namespaces);
        return $this;
    }

    /**
     * Add a single namespace to be utilised by the XML parser when it receives
     * a valid XRD document.
     *
     * @param   string $namespace
     * @param   string $namespaceUrl
     * @return  Zend_Service_Yadis
     */
    public function addNamespace($namespace, $namespaceUrl)
    {
        $this->_namespace->addNamespace($namespace, $namespaceUrl);
        return $this;
    }

    /**
     * Return the value of a specific namespace.
     *
     * @return   string|null
     */
    public function getNamespace($namespace)
    {
        return $this->_namespace->getNamespace($namespace);
    }

    /**
     * Returns an array of all currently set namespaces.
     *
     * @return  array
     */
    public function getNamespaces()
    {
        return $this->_namespace->getNamespaces();
    }

    protected function _getValidXrdNodes(SimpleXMLElement $xrds)
    {
        /**
         * Register all namespaces to this SimpleXMLElement.
         */
        $this->_namespace->registerXpathNamespaces($xrds);

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
         * Grab the XRD elements which contains details of the service provider's
         * Server url, service types, and other details. Concrete subclass may
         * have additional requirements concerning node priority or valid position
         * in relation to other nodes. E.g. Yadis requires only using the *last*
         * node.
         */
        $xrdNodes = $xrds->xpath('/xrds:XRDS[1]/xrd:XRD');
        if(!$xrdNodes)
        {
            return null;
        }
        return $xrdNodes;
    }

}