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
 * @version    $Id$
 */

/** Zend_Service_Abstract */
require_once 'Zend/Service/Abstract.php';

/** Zend_Uri */
require_once 'Zend/Uri.php';

/**
 * Provides methods for translating an XRI into a URI.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Yadis
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Yadis_Xri extends Zend_Service_Abstract
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
        '=', '$', '!', '@', '+'
    );

    /**
     * Default proxy to append XRI identifier to when forming a valid URI.
     *
     * @var string
     */
    protected $_proxy = 'http://xri.net/';

    /**
     * Instance of Zend_Service_Yadis_Xrds_Namespace for managing namespaces
     * associated with an XRDS document.
     *
     * @var Zend_Service_Yadis_Xrds_Namespace
     */
    protected $_namespace = null;

    /**
     * The XRI string.
     *
     * @var string
     */
    protected $_xri = null;

    /**
     * The URI as translated from an XRI and appended to a Proxy.
     *
     * @var string
     */
    protected $_uri = null;

    /**
     * A Canonical ID if requested, and parsed from the XRDS document found
     * by requesting the URI created from a valid XRI.
     *
     * @var string
     */
    protected $_canonicalId = null;

    /**
     * Just in case someone needs special options, e.g. test proxy
     *
     * @var array
     */
    protected $_httpRequestOptions = null;

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
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * Set a Namespace object which contains all relevant namespaces
     * for XPath queries on this Yadis resource.
     *
     * @param Zend_Service_Yadis_Xrds_Namespace
     * @return Zend_Service_Yadis_Xri
     */
    public function setNamespace(Zend_Service_Yadis_Xrds_Namespace $namespace)
    {
        $this->_namespace = $namespace;
        return $this;
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
        if (!Zend_Uri::check($proxy)) {
            require_once 'Zend/Service/Yadis/Exception.php';
            throw new Zend_Service_Yadis_Exception('Invalid URI; unable to set as an XRI proxy');
        }
        $this->_proxy = $proxy;
        return $this;
    }

    /**
     * Return the URI of the current proxy.
     *
     * @param string $proxy
     */
    public function getProxy()
    {
        return $this->_proxy;
    }

    /**
     * Set an XRI to be translated to a URI.
     *
     * @param  string $url
     * @return Zend_Service_Yadis_Xri
     * @throws Zend_Service_Yadis_Exception
     */
    public function setXri($xri)
    {
        /**
         * Check if the passed string is a likely XRI.
         */
        if (stripos($xri, 'xri://') === false && !in_array($xri[0], $this->_xriIdentifiers)) {
            require_once 'Zend/Service/Yadis/Exception.php';
            throw new Zend_Service_Yadis_Exception('Invalid XRI string submitted');
        }
        $this->_xri = $xri;
        return $this;
    }

    /**
     * Return the original XRI string.
     *
     * @return string
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
     * @param string $xri
     * @return string
     * @throws Zend_Service_Yadis_Exception
     * @uses Zend_Uri
     */
    public function toUri($xri = null, $serviceType = null)
    {
        if (!is_null($serviceType)) {
            $this->_serviceType = (string) $serviceType;
        }
        if (isset($xri)) {
            $this->setXri($xri);
        }
        /**
         * Get rid of the xri:// prefix before assembling the URI
         * including any IP or DNS wildcards
         */
        if (stripos($this->_xri, 'xri://') == 0) {
            if (stripos($this->_xri, 'xri://$ip*') == 0) {
                $iname = substr($xri, 10);
            } elseif (stripos($this->_xri, 'xri://$dns*') == 0) {
                $iname = substr($xri, 11);
            } else {
                $iname = substr($xri, 6);
            }
        } else {
            $iname = $xri;
        }
        $uri = $this->getProxy() . $iname;
        if (!Zend_Uri::check($uri)) {
            require_once 'Zend/Service/Yadis/Exception.php';
            throw new Zend_Service_Yadis_Exception('Unable to translate XRI to a valid URI using proxy: ' . $this->getProxy());
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
        if (!isset($xri) && !isset($this->_uri)) {
            require_once 'Zend/Service/Yadis/Exception.php';
            throw new Zend_Service_Yadis_Exception('No XRI passed as parameter as required unless called after Zend_Service_Yadis_Xri:toUri');
        } elseif (isset($xri)) {
            $uri = $this->toUri($xri);
        } else {
            $uri = $this->_uri;
        }

        $response = $this->_get($uri, null, $this->getHttpRequestOptions());
        if (stripos($response->getHeader('Content-Type'), 'application/xrds+xml') === false) {
            require_once 'Zend/Service/Yadis/Exception.php';
            throw new Zend_Service_Yadis_Exception('The response header indicates the response body is not an XRDS document');
        }

        $xrds = new SimpleXMLElement($response->getBody());
        $this->_namespace->registerXpathNamespaces($xrds);
        $this->_canonicalId = $xrds->xpath('/xrd:CanonicalID[last()]');
        if (!$this->_canonicalId) {
            return false;
        }
        $this->_canonicalId = $canonicalIds[count($canonicalIds) - 1];
        //var_dump($canonicalIds . __FILE__.__LINE__); exit;
        return $this->_canonicalId;
    }

    public function getCanonicalId()
    {
        if (!is_null($this->_canonicalId)) {
            return $this->_canonicalId;
        }
        if (is_null($this->_xri)) {
            require_once 'Zend/Service/Yadis/Exception.php';
            throw new Zend_Service_Yadis_Exception('Unable to get a Canonical Id since no XRI value has been set');
        }
        return $this->toCanonicalId($this->_xri);
    }

    /**
     * Set options to be passed to the Zend_Http_Request constructor
     *
     * @param array $options
     * @return void
     */
    public function setHttpRequestOptions(array $options)
    {
        $this->_httpRequestOptions = $options;
    }

    /**
     * Get options to be passed to the Zend_Http_Request constructor
     *
     * @return array
     */
    public function getHttpRequestOptions()
    {
        return $this->_httpRequestOptions;
    }

    /**
     * Required to request the root i-name (XRI) XRD which will provide an
     * error message that the i-name does not exist, or else return a valid
     * XRD document containing the i-name's Canonical ID.
     *
     * @param   string $uri
     * @return  Zend_Http_Response
     * @todo    Finish this a bit better using the QXRI rules.
     */
    protected function _get($url, $serviceType = null, array $options = null)
    {
        $client = self::getHttpClient();
        $client->setConfig($options);
        $client->setUri($url);
        $client->setMethod(Zend_Http_Client::GET);
        $client->setHeaders('Accept', 'application/xrds+xml');
        if ($serviceType) {
            $client->setParameterGet('_xrd_r', 'application/xrds+xml');
            $client->setParameterGet('_xrd_t', $serviceType);
        } else {
            $client->setParameterGet('_xrd_r', 'application/xrds+xml;sep=false');
        }

        if (!Zend_Uri::check($client->getUri())) {
            throw new Zend_Service_Yadis_Exception('Bad query from XRI Resolution');
        }

        $response = $client->request();
        if (!$response->isSuccessful()) {
            require_once 'Zend/Service/Yadis/Exception.php';
            throw new Zend_Service_Yadis_Exception('Invalid response to Yadis protocol received: ' . $response->getStatus() . ' ' . $response->getMessage());
        }
        return $response;
    }

}