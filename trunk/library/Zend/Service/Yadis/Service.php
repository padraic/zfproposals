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
 * The Zend_Service_Yadis_Service class represents a service parsed from the
 * XRD node of a Yadis 1.0 XRDS document.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Yadis
 * @author     Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Yadis_Service
{

    /**
     * Holds the Service node parsed from a Yadis XRDS document as a
     * SimpleXMLElement object.
     *
     * @var SimpleXMLElement
     */
    protected $_serviceNode = null;

    /**
     * Holds the Service node parsed from a Yadis XRDS document as a
     * SimpleXMLElement object.
     *
     * @var SimpleXMLElement
     */
    protected $_namespace = null;

    /**
     * Class constructor; initialise the object with a Service node from the
     * XRDS document, and the current Zend_Services_Yadis_Xrds_Namespace
     * object to provide the current namespaces for using XPath queries.
     *
     * @param   SimpleXMLElement $serviceNode
     * @param   Zend_Service_Yadis_Xrds_Namespace $namespace
     */
    public function __construct(SimpleXMLElement $serviceNode, Zend_Service_Yadis_Xrds_Namespace $namespace)
    {
        $this->_serviceNode = $serviceNode;
        $this->_namespace = $namespace;
        $this->_namespace->registerXpathNamespaces($this->_serviceNode);
    }

    /**
     * Return an array of Service Type URI strings. This method will NOT
     * validate the resulting URIs.
     *
     * @return  array
     */
    public function getTypes()
    {
        $return = array();
        $types = $this->_serviceNode->xpath('xrd:Type');
        foreach ($types as $type) {
            $string = strval($type);
            if(!empty($string)) {
                $return[] = $string;
            }
        }
        return $return;
    }

    /**
     * Return an array of Service Type URI strings. This method will NOT
     * validate the resulting URIs. URI values in the array will have key
     * values matching their priority, and be ordered based on their
     * priority values - highest (i.e. lowest integer) priority first.
     *
     * @return  array
     */
    public function getUris()
    {
        $defaultArray = array();
        $uris = $this->_serviceNode->xpath('xrd:Uri');
        foreach ($uris as $uri) {
            $string = strval($uri);
            if(!empty($string)) {
                $defaultArray[] = $string;
            }
        }
        return $defaultArray;
    }

    /**
     * Returns the Priority integer of this Service node.
     *
     * @return  integer
     */
    public function getPriority()
    {
        $attributes = $this->_serviceNode->attributes();
        if(array_key_exists('priority', $attributes)) {
            return intval($attributes['priority']);
        }
        return null;
    }

    /**
     * Return the current XRDS Service node as a SimpleXMLElement object.
     *
     * @return  SimpleXMLElement
     */
    public function getXmlObject()
    {
        return $this->_serviceNode;
    }
    
    /**
     * Return the current Zend_Service_Yadis_Xrds_Namespace object.
     *
     * @return  Zend_Service_Yadis_Xrds_Namespace
     */
    public function getNamespaceObject()
    {
        return $this->_namespace;
    }

    /**
     * Return an array of the current XRDS namespaces for working with any
     * XPath queries on the Service node.
     *
     * @return  array
     */
    public function getNamespaces()
    {
        return $this->_namespace->getNamespaces();
    }

    /**
     * Retrieve Elements of the current Service node by their name, and return
     * as a SimpleXMLElement object. The Elements should be direct children of
     * the Service node. This method basically just passes the $element string
     * as an XPath query so it's open to other uses despite the assumed use
     * case.
     *
     * @param   string $element
     */
    public function getElements($element)
    {
        return $this->_serviceNode->xpath($element);
    }

    /**
     * Order an array of values by priority. This assumes an array form of:
     * $array[$priority] = <array of elements>
     * Where multiple elements are assigned to a priority, their order in the
     * priority array should be made random. After ordering, the array is
     * flattened to a single array of elements for iteration.
     *
     * @param   array $unsorted
     * @return  array
     */
    protected function _sortByPriority(array $unsorted)
    {
        $sorted = array();
        foreach ($unsorted as $priority) {
            if (count($priority) > 1){
                shuffle($priority);
                $sorted = array_merge($sorted, $priority)
            } else {
                $sorted[] = $priority[0];
            } 
        }
        return $sorted;
    }

}