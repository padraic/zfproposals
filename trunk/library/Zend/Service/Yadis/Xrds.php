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

/**
 * The Zend_Service_Yadis_Xrds class is a wrapper for Service elements of an
 * XRD document which is parsed using SimpleXML, and contains methods for
 * retrieving data about each Service, including Type, Url and other arbitrary
 * data added in a separate namespace, e.g. openid:Delegate.
 *
 * @uses       SeekableIterator
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Yadis
 * @author     Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Yadis_Xrds implements Iterator
{

    /**
     * The Yadis Services resultset
     *
     * @var array
     */ 
    protected $_services = array();
 
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
     * Default XRI namespaces as defined in the Yadis Specification. The xrd
     * namespace XRI should be present although its not declared a requirement.
     *
     * @var array
     */
    protected $_namespaces = array(
        'xrds' => 'xri://$xrds',
        'xrd' => 'xri://$xrd*($v*2.0)'
    );
 
    /**
     * Constructor; parses and validates an XRD document. All access to
     * the data held in the XML is left to a concrete subclass specific to
     * expected XRD format and data types.
     * Cannot be directly instantiated; must call from subclass.
     * 
     * @param   SimpleXMLElement $xrds
     * @param   array $namespaces
     */ 
    protected function __construct(SimpleXMLElement $xrds, array $namespaces = null)
    {
        if(isset($namespaces)){
            $this->_namespaces = array_merge($namespaces, $this->_namespaces);
        }
        $xrdNodes = $this->_getValidXrdNodes($xrds);
        if(!$xrdNodes){
            require_once 'Zend/Service/Yadis/Exception.php';
            throw new Zend_Service_Yadis_Exception('The XRD document was found to be invalid');
        }
        $this->_xrdNodes = $xrdNodes;
    }
 
    /**
     * Implements Iterator::current()
     * 
     * Return the current element.
     *
     * @return Zend_Service_Yadis_Service
     */ 
    public function current()
    {}
 
    /**
     * Implements Iterator::key()
     *
     * Return the key of the current element.
     * 
     * @return integer
     */ 
    public function key()
    {}
 
    /**
     * Implements Iterator::next()
     * 
     * Increments pointer to next Service object.
     */ 
    public function next()
    {}
 
    /**
     * Implements Iterator::rewind()
     * 
     * Rewinds the Iterator to the first Service object
     *
     * @return boolean
     */ 
    public function rewind()
    {}
 
    /**
     * Implement Iterator::valid()
     *
     * @param  integer $key
     * @return boolean
     */ 
    public function valid($key = null)
    {}

        /**
     * Add a list (array) of additional namespaces to be utilised by the XML
     * parser when it receives a valid XRD document.
     *
     * @param   array $namespaces
     */
    public function addNamespaces(array $namespaces)
    {
        foreach($namespaces as $namespace=>$namespaceUrl) {
            $this->addNamespace($namespace, $namespaceUrl);
        }
        return $this;
    }

    /**
     * Add a single namepsace to be utilised by the XML parser when it receives
     * a valid XRD document.
     *
     * @param   string $namespace
     * @param   string $namespaceUrl
     */
    public function addNamespace($namespace, $namespaceUrl)
    {
        if (empty($namespace) || empty($namespaceUrl) || !Zend_Uri::check($namespaceUrl)) {
            require_once 'Zend/Service/Yadis/Exception.php';
            throw new Zend_Service_Yadis_Exception('Requires a valid namespace id and url');
        }
        $this->_namespaces[$namespace] = $namespaceUrl;
        return $this;
    }

    /**
     * Return the value of a specific namespace.
     *
     * @return   string|null
     */
    public function getNamespace($namespace)
    {
        if (array_key_exists($namespace, $this->_namespaces)) {
            return $this->_namespaces[$namespace];
        }
        return null;
    }

    /**
     * Returns an array of all currently set namespaces.
     *
     * @return  array
     */
    public function getNamespaces()
    {
        return $this->_namespaces;
    }

    protected function _getValidXrdNodes(SimpleXMLElement $xrds)
    {
        /**
         * Register all namespaces to this SimpleXMLElement.
         */
        $this->_registerNamespacesOn($xrds);

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

    protected function _registerNamespacesOn(SimpleXMLElement $element)
    {
        foreach ($this->_namespaces as $namespace=>$namespaceUrl) {
            $element->registerXPathNamespace($namespace, $namespaceUrl);
        }
    }

    /**
     * Sort all elements in a list by priority in accordance with the rules
     * defined by Clause 3.3.3 of the XRI Resolution 2.0 Specification with
     * the aim of establishing order descending of highest priority.
     *      http://yadis.org/wiki/XRI_Resolution_2.0_specification
     *
     * @param   Zend_Service_Yadis_Service $service
     */
    protected function _sortByPriority(array $elements)
    {
        /**
         * Sort by numeric priority index ascending, i.e. higher priorities
         * occur at the top of the iterable list.
         */
        ksort($elements, SORT_NUMERIC);

        /**
         * Detect key collisions and apply a random order to such duplicated
         * keys.
         */
        
        
    }

}