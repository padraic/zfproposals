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

/**
 * The Zend_Service_Yadis_Xrds_Service class is a wrapper for Service elements of an
 * XRD document which is parsed using SimpleXML, and contains methods for
 * retrieving data about each Service, including Type, Url and other arbitrary
 * data added in a separate namespace, e.g. openid:Delegate.
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
     * Constructor; Accepts an XRD document for parsing.
     * Parses the XRD document by <xrd:Service> element to construct an array
     * of Zend_Service_Yadis_Service objects ordered by their priority.
     *
     * @param   SimpleXMLElement $xrds
     * @param   array $namespaces
     */
    public function __construct(SimpleXMLElement $xrds, array $namespaces = null)
    {
        parent::__construct($xrds, $namespaces);
        $this->_registerNamespacesOn($this->_xrdNode);
        $services = $this->_xrdNode->xpath('xrd:Service');
        foreach ($services as $service) {
            var_dump($service);
            //$this->_addService( new Zend_Service_Yadis_Service($service, $this->getNamespaces()) );
        }
        $this->_sortServicesByPriority();
    }

    /**
     * Add a service to the Service list indexed by priority.
     *
     * @param   Zend_Service_Yadis_Service $service
     */
    protected function _addService(Zend_Service_Yadis_Service $service)
    {
        $servicePriority = $service->getPriority();
        $this->_services[$servicePriority] = $service;
    }

    /**
     * Sort all Services in the list by priority in accordance with the rules
     * defined by Clause 3.3.3 of the XRI Resolution 2.0 Specification.
     *      http://yadis.org/wiki/XRI_Resolution_2.0_specification
     *
     * @param   Zend_Service_Yadis_Service $service
     */
    protected function _sortServicesByPriority()
    {
        /**
         * Sort by numeric priority index ascending.
         */
        ksort($this->_services, SORT_NUMERIC);
    }



}