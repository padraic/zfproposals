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

/** Zend_Service_Yadis_Xrds */
require_once 'Zend/Service/Yadis/Xrds.php';

/** Zend_Service_Yadis_Service */
require_once 'Zend/Service/Yadis/Service.php';

/**
 * The Zend_Service_Yadis_Xrds_Service class is a wrapper for Service elements of an
 * XRD document which is parsed using SimpleXML, and contains methods for
 * retrieving data about each Service, including Type, Url and other arbitrary
 * data added in a separate namespace, e.g. openid:Delegate.
 *
 * This class extends the basic Zend_Service_Yadis_Xrds wrapper to implement a
 * Service object specific to the Yadis Specification 1.0. XRDS itself is not
 * an XML format ruled by Yadis, but by an OASIS proposal.
 *
 * @uses       Iterator
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Yadis
 * @author     Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Yadis_Xrds_Service extends Zend_Service_Yadis_Xrds implements Iterator
{

    /**
     * Establish a lowest priority integer; we'll take the upper 2^31
     * integer limit.
     */
    const SERVICE_LOWEST_PRIORITY = 2147483647;

    /**
     * Holds the last XRD node of the XRD document as required by Yadis 1.0.
     *
     * @var SimpleXMLElement
     */
    protected $_xrdNode = null;
    
    /**
     * The Yadis Services resultset
     *
     * @var array
     */ 
    protected $_services = array();

    /**
     * Constructor; Accepts an XRD document for parsing.
     * Parses the XRD document by <xrd:Service> element to construct an array
     * of Zend_Service_Yadis_Service objects ordered by their priority.
     *
     * @param   SimpleXMLElement $xrds
     * @param   Zend_Service_Yadis_Xrds_Namespace $namespace
     */
    public function __construct(SimpleXMLElement $xrds, Zend_Service_Yadis_Xrds_Namespace $namespace)
    {
        parent::__construct($xrds, $namespace);
        /**
         * The Yadis Specification requires we only use the last xrd node. The
         * rest being ignored (if present for whatever reason). Important to
         * note when writing an XRD document for multiple services - put
         * the authentication service XRD node last.
         */
        $this->_xrdNode = $this->_xrdNodes[count($this->_xrdNodes) - 1];
        $this->_registerXpathNamespaces($this->_xrdNode);
        $services = $this->_xrdNode->xpath('xrd:Service');
        foreach ($services as $service) {
            var_dump($service);
            $this->_addService( new Zend_Service_Yadis_Service($service, $this->_namespace) );
        }
        $this->_sortByPriority($this->_services);
    }

    /**
     * Add a service to the Service list indexed by priority. Assumes
     * a missing or invalid priority should be shuffled to the bottom
     * of the priority order.
     *
     * @param   Zend_Service_Yadis_Service $service
     */
    protected function _addService(Zend_Service_Yadis_Service $service)
    {
        $servicePriority = $service->getPriority();
        if(is_null($servicePriority) || !is_numeric($servicePriority)) {
            $servicePriority = self::SERVICE_LOWEST_PRIORITY;
        }
        $this->_services[$servicePriority] = $service;
    }

}