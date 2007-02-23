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
 * Provides methods for translating an XRI into a URI.
 *
 * @uses       Zend_Service_Abstract
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Yadis
 * @author     Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Yadis_Xri
{

    /**
     * Hold an instance of this object per the Singleton Pattern.
     *
     * @var Zend_Service_Yadis_Xri
     */
    protected static $_instance = null;

    /*
     * Array of characters which if found at the 0 index of a Yadis ID string
     * may indicate the use of an XRI.
     *
     * @var array
     */
    protected $_xriIdentifiers = array(
        '=', '$', '!', '@', '+', '('
    );

    /**
     * Default proxy to append XRI identifier to when forming a valid URI.
     *
     * @var string
     */
    protected $_proxy = 'http://xri.net/';

    /**
     * The XRI string.
     *
     * @var string
     */
    protected $_xri = null;

    /**
     * Constructor; protected since this class is a singleton.
     */
    protected function __construct()
    {}

    /**
     * Return a singleton instance of this class.
     *
     * @return  Zend_Service_Yadis_Xri
     */
    public function getInstance()
    {
        if(is_null($this->_instance)) {
            $this->_instance = new self;
        }
        return $this->_instance;
    }

    /**
     * Set an XRI proxy URI. A default of "http://xri.net/" is available.
     *
     * @param   string $proxy
     * @return  Zend_Service_Yadis_Xri
     * @throws  Zend_Service_Yadis_Exception
     * @uses    Zend_Uri
     */
    public function setProxy($proxy)
    {
        if(!Zend_Uri::check($proxy)) {
            require_once 'Zend/Service/Yadis/Exception.php';
            throw new Zend_Service_Yadis_Exception('Invalid URI; unable to set as an XRI proxy');
        }
        $this->_proxy = $proxy
        return $this;
    }

    /**
     * Return the URI of the current proxy.
     *
     * @param   string $proxy
     * @uses    Zend_Uri
     */
    public function getProxy()
    {
        return $this->_proxy;
    }

    /**
     * Set an XRI to be translated to a URI.
     *
     * @param   string $url
     * @return  Zend_Service_Yadis_Xri
     * @throws  Zend_Service_Yadis_Exception
     */
    public function setXri($xri)
    {
        /**
         * Check if the passed string is a likely XRI.
         */
        if(!strpos($yadisId, 'xri://') === 0 && !in_array($xri[0], $this->_xriIdentifiers)) {
            require_once 'Zend/Service/Yadis/Exception.php';
            throw new Zend_Service_Yadis_Exception('Invalid XRI submitted');
        }
        $this->_xri = $xri;
        return $this;
    }

    /**
     * Return the original XRI string.
     *
     * @return  string
     */
    public function getXri()
    {
        return $this->_xri;
    }

    /**
     * Attempts to convert an XRI into a URI. In simple terms this involves
     * removing the "xri://" prefix and appending the remainder to the URI of
     * an XRI proxy such as "http://xri.net/".
     *
     * @param   string $xri
     * @return  string
     * @throws  Zend_Service_Yadis_Exception
     * @uses    Zend_Uri
     */
    public function toUri($xri = null)
    {
        if(isset($xri)) {
            $this->setXri($xri);
        }
        /**
         * Get rid of the xri:// prefix before assembling the URI
         */
        if(strpos($this->_xri, 'xri://') === 0) {
            $id = substr($xri, 6);
        }
        $uri = $this->getProxy() . $id;
        if(!Zend_Uri::check($uri))
        {
            require_once 'Zend/Service/Yadis/Exception.php';
            throw new Zend_Service_Yadis_Exception('Unable to translate XRI to a valid URI using proxy:' . $this->getProxy());
        }
        $this->_uri = $uri;
        return $uri;
    }

    /**
     * Based on an XRI, will request the XRD document located at the proxy
     * prefixed URI and parse in search of the XRI Canonical Id. This is
     * a flexible requirement. OpenID 2.0 requires the use of the Canonical
     * ID instead of the raw i-name. 2idi.com, on the other hand, does not.
     *
     * @param   string $xri
     * @return  string
     * @throws  Zend_Service_Yadis_Exception
     * @uses    Zend_Uri
     */
    public function toCanonicalId($xri = null)
    {
        if(!isset($xri) && !isset($this->_uri)) {
            require_once 'Zend/Service/Yadis/Exception.php';
            throw new Zend_Service_Yadis_Exception('No XRI passed as parameter as required unless called after Zend_Service_Yadis_Xri:toUri');
        } elseif (isset($xri)) {
            $uri = $this->toUri($xri);
        } else {
            $uri = $this->_uri;
        }
        

        // for the moment
        return false;
    }

    /**
     * Required to request the root i-name (XRI) XRD which will provide an
     * error message that the i-name does not exist, or else return a valid
     * XRD document containing the i-name's Canonical ID.
     *
     * @param   string $uri
     * @return  Zend_Http_Response
     */
    protected function _get()
    {
    
    }
    
    /**
     * Creates a new Zend_Service_Yadis_Xrds_Iname object which uses SimpleXML
     * to parse the XML. In this case all we need access to is the Canonical ID
     * of an i-name, or an error message telling us the i-name is invalid.
     *
     * @param   string $xrdsDocument
     * @return  Zend_Service_Yadis_Xrds_Iname|boolean
     */
    protected function _parseXrds($xrds)
    {
    
    }
}