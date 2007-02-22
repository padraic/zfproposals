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
 */

/** Zend_Service_Abstract */
require_once 'Zend/Service/Abstract.php';

/** Zend_Service_Yadis_Xrds */
require_once 'Zend/Service/Yadis/Xrds.php';

/**
 * Zend_Service_Yadis will provide a method of Service Discovery implemented
 * in accordance with the Yadis Specification 1.0. This describes a protocol
 * for locating an XRD document which details Services available. The XRD is
 * typically specific to a single user, identified by their Yadis ID. 
 * Zend_Service_Yadis_XRDS will be a wrapper which is responsible for parsing
 * and presenting an iterable list of Zend_Service_Yadis_Service objects
 * holding the data for each specific Service discovered.
 *
 * Note that class comments cannot substitute for a full understanding of the
 * rules and nuances required to implement the Yadis protocol. Where doubt
 * exists, refer to the Yadis Specification 1.0 at:
 *      http://yadis.org/papers/yadis-v1.0.pdf
 *
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Yadis extends Zend_Service_Abstract
{

    /**
     * Constants referring to Yadis response types
     */
    const XRDS_META_HTTP_EQUIV = 2;
    const XRDS_LOCATION_HEADER = 4;
    const XRDS_CONTENT_TYPE = 8;
    const XRDS_ERROR = 16;

    /**
     * The current Yadis ID; this is the raw form initially submitted prior
     * to any transformation/validation as an URL.
     *
     * @var string
     */
    protected $_yadisId = '';

    /**
     * The current Yadis URL; this is a URL either validated or transformed
     * from the initial Yadis ID. This URL is used to make the initial HTTP
     * GET request during Service Discovery.
     *
     * @var string
     */
    protected $_yadisUrl = '';

    /**
     * Holds the first response received during Service Discovery.
     * 
     * This is required to allow certain Service specific fallback methods.
     * For example, OpenID allows a Yadis fallback which relies on seeking a
     * set of appropriate <link> elements.
     *
     * @var Zend_Http_Response
     */
    protected $_initialResponse = null;

    /**
     * A URL parsed from a HTML document's <meta> element inserted in
     * accordance with the Yadis Specification and which points to a Yadis
     * XRD document.
     *
     * @var string
     */
    protected $_metaHttpEquivUrl = '';

    /**
     * A URI parsed from an X-XRDS-Location response-header. This value must
     * point to a Yadis XRD document otherwise the Yadis discovery process
     * should be considered to have failed.
     *
     * @var string
     */
    protected $_xrdsLocationHeaderUrl = '';

    /*
     * Default XRI namespaces as defined in the Yadis Specification. The xrd
     * namespace XRI should be present although its not declared a requirement.
     *
     * @var array
     */
    protected $_namespaces = array(
        'xrds' => 'xri://$xrds',
        'xrd' => 'xri://$xrd*($v*2.0)'
    );

    /*
     * Contains the current valid Yadis response type as declared in the class
     * constants. This allows the discovery process determine when the response
     * cycle described by the Yadis Specification becomes invalid, i.e. a failure
     * to obtain any URI pointing to an XRD document.
     *
     * @var integer
     */
    protected $_status = null;
    
    /**
     * Class Constructor
     *
     * Allows settings of the initial Yadis ID (an OpenID URL for example) and
     * an optional list of additional namespaces. For example, OpenID uses a
     * namespace such as:
     *    xmlns:openid="http://openid.net/xmlns/1.0"
     *
     * @param   string $yadisId
     * @param   array $namespaces
     */
    public function __construct($yadisId = null, $namespaces = null)
    {
        if (!is_null($yadisId)) {
            $this->setYadisId($yadisId);
        }
        if (isset($namespaces) && is_array($namespaces) && count($namespaces) > 0) {
            $this->addNamespaces($namespaces);
        } elseif (isset($namespaces)) {
            throw new Zend_Service_Yadis_Exception('Expected parameter $namespaces to be an array; got ' . gettype($namespaces));
        }
    }

    /**
     * A Yadis ID is usually an URL, but can also include an XRI, IRI, or i-name.
     * The initial version will support URLs as standard before examining options
     * for supporting alternatives (IRI,XRI,i-name) since they require additional 
     * validation and conversion steps (e.g. Punycode for IRI) before use.
     *
     * Note: The current Zend_Uri/Zend_Filter classes have no apparent support
     * for validating Internationalized Resource Indicators (ILI) nor for
     * transforming such IRI's to a valid URI via Zend_Uri.
     *
     * @param   string $yadisId
     * @returns Zend_Service_Yadis
     * @throws  Zend_Service_Yadis_Exception
     * @uses    Zend_Uri
     */
    public function setYadisId($yadisId)
    {
        $this->_yadisId = $yadisId;
        require_once 'Zend/Uri.php';
        if (Zend_Uri::check($yadisId)) {
            $this->_yadisUrl = $yadisId;
            return $this;
        }
        require_once 'Zend/Service/Yadis/Exception.php';
        throw new Zend_Service_Yadis_Exception('The ID provided by the user was found to be invalid');
    }

    /**
     * Returns the original Yadis ID string set for this class.
     *
     * @returns string
     */
    public function getYadisId()
    {
        return $this->_yadisId;
    }

    /**
     * Returns the Yadis URL. This will usually be identical to the Yadis ID,
     * unless the Yadis ID (in the future) was one of ILI, XRI or i-name which
     * required transformation to a valid URI.
     *
     * @returns string
     */
    public function getYadisUrl()
    {
        return $this->_yadisUrl;
    }

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
            throw new Zend_Service_Yadis_Exception('Require a valid namespace id and url.');
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

    /**
     * Performs Service Discovery, i.e. the requesting and parsing of a valid
     * Yadis (XRD) document into a list of Services and Service Data. The
     * return value will be an instance of Zend_Service_Yadis_Xrds which will
     * implement SeekableIterator. Returns FALSE on failure.
     *
     * @return  Zend_Service_Yadis_Xrds|boolean
     * @throws  Zend_Service_Yadis_Exception
     * @uses    Zend_Http_Response
     */
    public function discover()
    {
        $currentUri = $this->getYadisUrl();
        $xrdsDocument = null;
        $response = null;

        while($xrdsDocument === null) {
            unset($response);
            $response = $this->_get($currentUri);
            $resourceType = $this->_getResourceType($response);
            /*
             * For reference, the Yadis Spec 1.0 specifies that we must use a
             * valid response-header in preference all other responses. So even
             * if we receive an XRDS Content-Type, if it also includes an
             * X-XRDS-Location header we must follow it, and ignore the response
             * body. Being forgiving would breach the specification.
             *
             * Note: The specifications allow a HEAD request instead of a GET
             * request where the response might be headers-only. Here, we solely
             * use GET requests. Would using an initial HEAD make an overall
             * difference in terms of execution time? Might if the majority do
             * not have personal URLs as aliases.
             */
            switch($resourceType) {
                case self::XRDS_LOCATION_HEADER:
                    $currentUri = $this->_xrdsLocationHeaderUrl;
                    break;
                case self::XRDS_META_HTTP_EQUIV:
                    $this->_userUriResponse = $response;
                    $currentUri = $this->_metaHttpEquivUrl;
                    break;
                case self::XRDS_CONTENT_TYPE:
                    $xrdsDocument = $response->getBody();
                    break;
                default: 
                    $resourceType = self::XRDS_ERROR;
                    $xrdsDocument = false; // exit the while loop
            }
        }

        if ($resourceType == self::XRDS_ERROR) {
            throw new Zend_Service_Yadis_Exception('Yadis service failure: Unable to locate a valid XRDS resource.');
        }

        if ($xrdsDocument)
        {
            /*
             * If we found a valid XRDS document, then we use SimpleXML to
             * parse out the Service List and data and return it to the
             * user as an object of type Zend_Service_Yadis_Xrds which will
             * implement SeekableIterator.
             */
            $serviceList = $this->_parseXrds($xrdsDocument);
            return $serviceList;
        }
        return false;
    }

    /**
     * Return the very first response received when using a valid Yadis URL.
     * This is important for Services, like OpenID, which can attempt a
     * fallback solution in case Yadis fails.
     *
     * @return  Zend_Http_Response
     */
    public function getInitialResponse()
    {
        if ($this->_initialResponse instanceof Zend_Http_Response)
        {
            return $this->_initialResponse;
        }
        return null;
    }

    /**
     * Run any instance of Zend_Http_Response through a set of filters to
     * determine the Yadis Response type which in turns determines how the
     * response should be reacted to or dealt with.
     *
     * @return  integer
     */
    protected function _getResourceType(Zend_Http_Response $response)
    {
        if ($this->_isXrdsLocationHeader($response)) {
            return self::XRDS_LOCATION_HEADER;
        } elseif ($this->_isXrdsContentType($response)) {
            return self::XRDS_CONTENT_TYPE;
        } elseif ($this->_isMetaHttpEquiv($response)) {
            return self::XRDS_META_HTTP_EQUIV;
        }
        return self::XRDS_ERROR;
    }

    /**
     * Use the Zend_Http_Client to issue an HTTP GET request carrying the
     * "Accept" header value of "application/xrds+xml". This can allow
     * servers to quickly respond with a valid XRD document rather than
     * forcing the client to follow the X-XRDS-Location bread crumb trail.
     *
     * @param   string $url
     * @return  Zend_Http_Response
     * @uses    Zend_Http_Client
     */
    protected function _get($url)
    {
        $client = self::getHttpClient();
        $client->setUri($url);
        $client->setMethod(Zend_Http_Client::GET);
        $client->setHeaders('Accept', 'application/xrds+xml');
        return $client->request();
    }
    
    /**
     * Checks whether the Response contains headers which detail where
     * we can find the XRDS resource for this user. If exists, the value
     * is set to the private $_xrdsLocationHeaderUrl property.
     *
     * @param   Zend_Http_Response $response
     * @return  boolean
     * @throws  Zend_Service_Yadis_Exception
     * @uses    Zend_Uri 
     */
    protected function _isXrdsLocationHeader(Zend_Http_Response $response)
    {
        if ($response->getHeader('x-xrds-location')) {
            $location = $response->getHeader('x-xrds-location');
        } elseif ($response->getHeader('x-yadis-location')) {
            $location = $response->getHeader('x-yadis-location');
        }
        if (empty($location)) {
            return false;
        } elseif (!Zend_Uri::check($location)) {
            throw new Zend_Service_Yadis_Exception('Invalid URI: ' . htmlentities($location, ENT_QUOTES, 'utf-8'));
        }
        $this->_xrdsLocationHeaderUrl = $location;
        return true;
    }

    /**
     * Checks whether the Response contains the XRDS resource. It should, per
     * the specifications always be served as application/xrds+xml
     *
     * @param   Zend_Http_Response $response
     * @return  boolean
     */
    protected function _isXrdsContentType(Zend_Http_Response $response)
    {
        if (!$response->getHeader('Content-Type') || $response->getHeader('Content-Type') !== 'application/xrds+xml') {
            return false;
        }
        return true;
    }

    /**
     * Assuming this user is hosting a third party sourced identity under an
     * alias personal URL, we'll need to check if the website's HTML body
     * has a http-equiv meta element with a content attribute pointing to where
     * we can fetch the XRD document.
     *
     * @param   Zend_Http_Response $response
     * @return  boolean
     * @uses    Zend_Uri
     * @throws  Zend_Service_Yadis_Exception
     */
    protected function _isMetaHttpEquiv(Zend_Http_Response $response)
    {
        /*
         * This is a very strict regex for <meta> elements. Might need to be
         * loosened unless specifically required to be strictly adhered to.
         */
        $metaRegex = "|<meta[^>]+http-equiv=\"([^\"]*)\"[^>]+content=\"([^\"]*)\"[^>]+>|i";
        $matches = null;
        $location = null;
        preg_match_all($metaRegex, $response->getBody(), $matches, PREG_PATTERN_ORDER);
        for ($i=0;$i < count($matches[1]);$i++) {
            if (strtolower($matches[1][$i]) == "x-xrds-location" || strtolower($matches[1][$i]) == "x-yadis-location") {
                $location = $matches[2][$i];
            }
        }
        if (empty($location)) {
            return false;
        } elseif (!Zend_Uri::check($location)) {
            throw new Zend_Service_Yadis_Exception('Invalid URI: ' . htmlentities($location, ENT_QUOTES, 'utf-8'));
        }
        /*
         * Should now contain the content value of the http-equiv type pointing
         * to an XRDS resource for the user's Identity Provider, as found by
         * passing the meta regex across the response body. e.g.
         *      http://yourname.myopenid.com/xrds
         */
        $this->_metaHttpEquivUrl = $location;
        return true;
    }

    /**
     * Creates a new Zend_Service_Yadis_Xrds object which uses SimpleXML to
     * parse the XML into a list of Iterable Zend_Service_Yadis_Service
     * objects.
     *
     * @param   string $xrdsDocument
     * @return  Zend_Service_Yadis_Xrds|boolean
     */
    protected function _parseXrds($xrdsDocument)
    {
        try {
            $xrds = new SimpleXMLElement($xrdsDocument);
            $serviceSet = new Zend_Service_Yadis_Xrds($xrds);
        } catch (Zend_Exception $e) {
            return false;
        }
        return $serviceSet;
    }

}