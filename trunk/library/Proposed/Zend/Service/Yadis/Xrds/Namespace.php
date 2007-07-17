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

/** Zend_Uri */
require_once 'Zend/Uri.php';

/**
 * The Zend_Service_Yadis_Xrds_Namespace class is a container for namespaces
 * which need to be registered to an XML parser in order to correctly consume
 * an XRDS document using the parser's XPath functionality.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Yadis
 * @author     Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Yadis_Xrds_Namespace
{

    /**
     * Default XRDS namespaces which should always be registered.
     *
     * @var array
     */
    protected $_namespaces = array(
        'xrds' => 'xri://$xrds',
        'xrd' => 'xri://$xrd*($v*2.0)'
    );

    /**
     * Add a list (array) of additional namespaces to be utilised by the XML
     * parser when it receives a valid XRD document.
     *
     * @param  array $namespaces
     */
    public function addNamespaces(array $namespaces)
    {
        foreach($namespaces as $namespaceKey=>$namespaceUrl) {
            $this->addNamespace($namespaceKey, $namespaceUrl);
        }
    }

    /**
     * Add a single namespace to be utilised by the XML parser when it receives
     * a valid XRD document.
     *
     * @param string $namespaceKey
     * @param string $namespaceUrl
     * @return void
     * @uses Zend_Uri
     */
    public function addNamespace($namespaceKey, $namespaceUrl)
    {
        if (!isset($namespaceKey) || !isset($namespaceUrl) || empty($namespaceKey) || empty($namespaceUrl)) {
            require_once 'Zend/Service/Yadis/Exception.php';
            throw new Zend_Service_Yadis_Exception('Parameters must be non-empty strings');
        } elseif (!Zend_Uri::check($namespaceUrl)) {
            require_once 'Zend/Service/Yadis/Exception.php';
            throw new Zend_Service_Yadis_Exception('Invalid namespace URI: ' . htmlentities($namespaceUrl, ENT_QUOTES, 'utf-8'));
        } elseif (array_key_exists($namespaceKey, $this->getNamespaces())) {
            require_once 'Zend/Service/Yadis/Exception.php';
            throw new Zend_Service_Yadis_Exception('You may not redefine the "xrds" or "xrd" XML Namespaces');
        }
        $this->_namespaces[$namespace] = $namespaceUrl;
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

    /**
     * Register all stored namespaces to the parameter SimpleXMLElement object.
     *
     * @param   SimpleXMLElement
     * @return  void
     */
    public function registerXpathNamespaces(SimpleXMLElement $element)
    {
        foreach ($this->_namespaces as $namespaceKey=>$namespaceUrl) {
            $element->registerXPathNamespace($namespaceKey, $namespaceUrl);
        }
    }

}