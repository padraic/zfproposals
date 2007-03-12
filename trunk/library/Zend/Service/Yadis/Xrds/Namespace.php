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
     * Class constructor
     */
    public function __construct()
    {}

    /**
     * Add a list (array) of additional namespaces to be utilised by the XML
     * parser when it receives a valid XRD document.
     *
     * @param   array $namespaces
     * @todo    Extract namespaces (common to three classes) to new shared class
     */
    public function addNamespaces(array $namespaces)
    {
        foreach($namespaces as $namespace=>$namespaceUrl) {
            $this->addNamespace($namespace, $namespaceUrl);
        }
    }

    /**
     * Add a single namespace to be utilised by the XML parser when it receives
     * a valid XRD document.
     *
     * @param   string $namespace
     * @param   string $namespaceUrl
     * @return  void
     * @uses    Zend_Uri
     */
    public function addNamespace($namespace, $namespaceUrl)
    {
        if (empty($namespace) || empty($namespaceUrl)) {
            require_once 'Zend/Service/Yadis/Exception.php';
            throw new Zend_Service_Yadis_Exception('Parameters must be non-empty strings');
        } elseif (!Zend_Uri::check($namespaceUrl)) {
            require_once 'Zend/Service/Yadis/Exception.php';
            throw new Zend_Service_Yadis_Exception('Invalid namespace URI: ' . htmlentities($namespaceUrl, ENT_QUOTES, 'utf-8'));
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
        foreach ($this->_namespaces as $namespace=>$namespaceUrl) {
            $element->registerXPathNamespace($namespace, $namespaceUrl);
        }
    }

    /**
     * Order an array of elements by priority. This assumes an array form of:
     *      $array[$priority] = <array of elements with equal priority>
     * Where multiple elements are assigned to a priority, their order in the
     * priority array should be made random. After ordering, the array is
     * flattened to a single array of elements for iteration.
     *
     * @param   array $unsorted
     * @return  array
     */
    public function sortByPriority(array $unsorted)
    {
        /**
         * Perform simple numeric ordering of the priorities. This ensures the
         * initial priority keys are ordered numerically ascending (higher 
         * priorities are the first to be reached when iterating the array).
         */
        $priorities = array_keys($unsorted);
        sort($priorities, SORT_NUMERIC);
        $unflattened = array();
        foreach($priorities as $priority) {
            $unflattened[$priority][] = $unsorted[$priority];
        }
        /**
         * Flatten the priority arrays to a one-dimensional array after
         * ordering elements assigned the same priority randomly. The random 
         * ordering ensures non/same prioritised elements are randomly selected
         * to avoid a bias towards any particular element.
         */
        $flattened = array();
        foreach ($unflattened as $priority) {
            if (count($priority) > 1){
                shuffle($priority);
                $flattened = array_merge($flattened, $priority)
            } else {
                $flattened[] = $priority[0];
            } 
        }
        return $flattened;
    }

}