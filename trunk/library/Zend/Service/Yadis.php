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

/** Zend_Service_Abstract */
require_once 'Zend/Service/Abstract.php';

/** Zend_Service_Yadis_Xrds_Service */
require_once 'Zend/Service/Yadis/Xrds/Service.php';

/** Zend_Service_Yadis_Xrds_Namespace */
require_once 'Zend/Service/Yadis/Xrds/Namespace.php';

/** Zend_Uri */
require_once 'Zend/Uri.php';

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
 * @uses       Zend_Service_Abstract
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Yadis
 * @author     Pádraic Brady (http://blog.astrumfutura.com)
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

    /**
     * The current Yadis ID; this is the raw form initially submitted prior
     * to any transformation/validation as an URL. This *may* allow IRI support
     * in the future given IRIs map to URIs and adoption of the IRI standard
     * and are entering common use internationally.
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
    protected $_metaHttpEquivResponse = null;

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

    /**
     * Instance of Zend_Service_Yadis_Xrds_Namespace for managing namespaces
     * associated with an XRDS document.
     *
     * @var Zend_Service_Yadis_Xrds_Namespace
     */
    protected $_namespace = null;

    /**
     * Array of valid HTML Content-Types. Required since Yadis states agents
     * must parse a document if received as the first response and with an
     * MIME type indicating HTML or XHTML. Listed in order of prioroty, with
     * HTML taking priority over XHTML.
     *
     * @link http://www.w3.org/International/articles/serving-xhtml/Overview.en.php
     * @var array
     */
    protected $_validHtmlContentTypes = array(
        'text/html',
        'application/xhtml+xml',
        'application/xml',
        'text/xml'
    );

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
    public function __construct($yadisId = null, array $namespaces = null)
    {
        $this->_namespace = new Zend_Service_Yadis_Xrds_Namespace;
        if (isset($namespaces) && count($namespaces) > 0) {
            $this->addNamespaces($namespaces);
        } elseif (isset($namespaces)) {
            require_once 'Zend/Service/Yadis/Exception.php';
            throw new Zend_Service_Yadis_Exception('Expected parameter $namespaces to be an array; but array appears to be empty');
        }
        if (isset($yadisId)) {
            $this->setYadisId($yadisId);
        }
    }

    /**
     * A Yadis ID is usually an URL, but can also include an IRI, or XRI i-name.
     * The initial version will support URLs as standard before examining options
     * for supporting alternatives (IRI,XRI,i-name) since they require additional 
     * validation and conversion steps (e.g. Punycode for IRI) before use.
     *
     * Note: The current Zend_Uri/Zend_Filter classes have no apparent support
     * for validating Internationalized Resource Indicators (ILI) nor for
     * transforming such IRI's to a valid URI via Zend_Uri.
     *
     * @param   string $yadisId
     */
    public function setYadisId($yadisId)
    {
        $this->_yadisId = $yadisId;
        $this->setYadisUrl($yadisId);
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
     * Attempts to create a valid URI based on the value of the parameter
     * which would typically be the Yadis ID.
     * Note: This currently only supports XRI transformations.
     *
     * @param   string $yadisId
     * @return  Zend_Service_Yadis
     * @throws  Zend_Service_Yadis_Exception
     */
    public function setYadisUrl($yadisId)
    {
        /**
         * This step should validate IDNs (see ZF-881)
         */
        if (Zend_Uri::check($yadisId)) {
            $this->_yadisUrl = $yadisId;
            return $this;
        }
        
        /**
         * Check if the Yadis ID is an XRI
         */
        if (strpos($yadisId, 'xri://') === 0 || in_array($yadisId[0], $this->_xriIdentifiers))
        {
            //throw new Exception('XRI support in progress but incomplete');
            require_once 'Zend/Service/Yadis/Xri.php';
            $this->_yadisUrl = Zend_Service_Yadis_Xri::getInstance()
                    ->setNamespace($this->_namespace)
                    ->toUri($yadisId);

            $cid = Zend_Service_Yadis_Xri::getInstance()->getCanonicalId();

        }

        /**
         * The use of IRIs (International Resource Identifiers) is governed by
         * RFC 3490. This is currently a Zend Framework issue (ZF-881) with an
         * implementation version set at 0.9. That's ~March 15 2007 so fingers
         * crossed ;).
         */
        
        require_once 'Zend/Service/Yadis/Exception.php';
        throw new Zend_Service_Yadis_Exception('Unable to validate a Yadis ID as a URI, or to transform a Yadis ID into a valid URI.');
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
     * @return  Zend_Service_Yadis
     */
    public function addNamespaces(array $namespaces)
    {
        $this->_namespace->addNamespaces($namespaces);
        return $this;
    }

    /**
     * Add a single namespace to be utilised by the XML parser when it receives
     * a valid XRD document.
     *
     * @param   string $namespace
     * @param   string $namespaceUrl
     * @return  Zend_Service_Yadis
     */
    public function addNamespace($namespace, $namespaceUrl)
    {
        $this->_namespace->addNamespace($namespace, $namespaceUrl);
        return $this;
    }

    /**
     * Return the value of a specific namespace.
     *
     * @return   string|null
     */
    public function getNamespace($namespace)
    {
        return $this->_namespace->getNamespace($namespace);
    }

    /**
     * Returns an array of all currently set namespaces.
     *
     * @return  array
     */
    public function getNamespaces()
    {
        return $this->_namespace->getNamespaces();
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
        $xrdStatus = false;

        while($xrdsDocument === null) {
            $response = $this->_get($currentUri);
            if (!$this->_metaHttpEquivResponse) {
                $this->_metaHttpEquivResponse = $response;
            }
            $responseType = $this->_getResponseType($response);
            
            /**
             * If prior response type was a location header, or a http-equiv
             * content value, then it should have contained a valid URI to
             * an XRD document. Each of these when detected would set the
             * xrdStatus flag to true.
             */
            if (!$responseType == self::XRDS_CONTENT_TYPE && $xrdStatus == true) {
                require_once 'Zend/Service/Yadis/Exception.php';
                throw new Zend_Service_Yadis_Exception('Yadis protocol could not locate a valid XRD document');
            }
            
            /**
             * The Yadis Spec 1.0 specifies that we must use a valid response
             * header in preference to other responses. So even if we receive
             * an XRDS Content-Type, if it also includes an X-XRDS-Location
             * header we must request the URI and ignore the response body.
             */
            switch($responseType) {
                case self::XRDS_LOCATION_HEADER:
                    $xrdStatus = true;
                    $currentUri = $this->_xrdsLocationHeaderUrl;
                    break;
                case self::XRDS_META_HTTP_EQUIV:
                    $xrdStatus = true;
                    $currentUri = $this->_metaHttpEquivUrl;
                    break;
                case self::XRDS_CONTENT_TYPE:
                    $xrdsDocument = $response->getBody();
                    break;
                default:
                    require_once 'Zend/Service/Yadis/Exception.php';
                    throw new Zend_Service_Yadis_Exception('Yadis protocol could not locate a valid XRD document');
            }
        }

        try {
            $serviceList = $this->_parseXrds($xrdsDocument);
        } catch (Zend_Exception $e) {
            require_once 'Zend/Service/Yadis/Exception.php';
            throw new Zend_Service_Yadis_Exception('XRD Document could not be parsed with the following message: ' . $e->getMessage(), $e->getCode());
        }
        return $serviceList;
    }

    /**
     * Return the very first response received when using a valid Yadis URL.
     * This is important for Services, like OpenID, which can attempt a
     * fallback solution in case Yadis fails, and the response came from a
     * user's personal URL acting as an alias.
     *
     * @return  Zend_Http_Response
     */
    public function getUserResponse()
    {
        if ($this->_metaHttpEquivResponse instanceof Zend_Http_Response)
        {
            return $this->_metaHttpEquivResponse;
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
    protected function _getResponseType(Zend_Http_Response $response)
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
        /**
         * Enable Host to use content negotiation where possible.
         */
        $client->setHeaders('Accept', 'application/xrds+xml');
        $response = $client->request();
        if (!$response->isSuccessful()) {
            require_once 'Zend/Service/Yadis/Exception.php';
            throw new Zend_Service_Yadis_Exception('Invalid response to Yadis protocol received: ' . $response->getStatus() . ' ' . $response->getMessage());
        }
        return $response;
    }
    
    /**
     * Checks whether the Response contains headers which detail where
     * we can find the XRDS resource for this user. If exists, the value
     * is set to the private $_xrdsLocationHeaderUrl property.
     *
     * @param   Zend_Http_Response $response
     * @return  boolean
     * @throws  Zend_Service_Yadis_Exception
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
            throw new Zend_Service_Yadis_Exception('Invalid URI: '
                . htmlentities($location, ENT_QUOTES, 'utf-8'));
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
     * @throws  Zend_Service_Yadis_Exception
     */
    protected function _isMetaHttpEquiv(Zend_Http_Response $response)
    {
        if (!in_array($response->getHeader('Content-Type'), $this->_validHtmlContentTypes)) {
            return false;
        }
        /**
         * Find a match for a relevant <meta> element, then iterate through the
         * results to see if a valid http-equiv value and matching content URI
         * exist.
         * Todo: need to check this is located inside the <head> element too.
         */
        $metaRegex = "%<meta[^>]+http-equiv=([\"]{0,1})([^\"]*)([\"]{0,1})[^>]+content=([\"]{0,1})([^\"]*)([\"]{0,1})[^>]*>%i";
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
            require_once 'Zend/Service/Yadis/Exception.php';
            throw new Zend_Service_Yadis_Exception('The URI parsed from the HTML document appears to be invalid: ' . htmlentities($location, ENT_QUOTES, 'utf-8'));
        }
        /**
         * Should now contain the content value of the http-equiv type pointing
         * to an XRDS resource for the user's Identity Provider, as found by
         * passing the meta regex across the response body.
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
        $xrds = new SimpleXMLElement($xrdsDocument);
        $serviceSet = new Zend_Service_Yadis_Xrds_Service($xrds, $this->_namespace);
        return $serviceSet;
    }

}