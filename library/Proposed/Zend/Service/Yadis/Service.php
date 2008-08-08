<?php
/**
 * Implementation of the Yadis Specification 1.0 protocol for service
 * discovery from an Identity URI/XRI or other.
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2007 Pádraic Brady <padraic.brady@yahoo.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *    * The name of the author may not be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS
 * IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Services
 * @package    Zend_Service_Yadis
 * @author     Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @version    $Id$
 */

/**
 * The Zend_Service_Yadis_Service class represents a service parsed from the
 * XRD node of a Yadis 1.0 XRDS document.
 *
 * @category   Services
 * @package    Zend_Service_Yadis
 * @author     Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
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
     * XRDS document, and the current Zend_Service_Yadis_Xrds_Namespace
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
        return $this->getElements('xrd:Type');
    }

    /**
     * Return an array of Service Type URI strings. This method will NOT
     * validate the resulting URIs. URI values in the array will have key
     * values matching their priority, and be ordered based on their
     * priority values - highest (i.e. lowest integer) priority first.
     *
     * @return array|boolean
     */
    public function getUris()
    {
       return $this->getElements('xrd:URI');
    }

    /**
     * Returns the Priority integer of this Service node.
     *
     * @return integer|boolean
     */
    public function getPriority()
    {
        $attributes = $this->_serviceNode->attributes();
        foreach($attributes as $attribute=>$value) {
            if($attribute == 'priority') {
                return intval($value);
            }
        }
        return false;
    }

    /**
     * Return the current XRDS Service node as a SimpleXMLElement object.
     *
     * @return  SimpleXMLElement
     */
    public function getSimpleXmlObject()
    {
        return $this->_serviceNode;
    }

    /**
     * Return the current XRDS Service node as a DOMDocument object.
     * This is just a simple transfer by loading the XML output from
     * the SimpleXMLElement object into a new DOMDocument instance.
     *
     * @return DOMDocument
     */
    public function getDomObject()
    {
        return dom_import_simplexml($this->serviceNode);
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
     * @return array
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
     * @param string $name
     * @return array|boolean
     */
    public function getElements($name)
    {
        $return = array();
        $elements = $this->_serviceNode->xpath($name);
        if (!is_array($elements) || count($elements) < 1) {
            return false;
        }
        foreach ($elements as $element) {
            $string = strval($element);
            if(!empty($string)) {
                $return[] = $string;
            }
        }
        return $return;
    }

}