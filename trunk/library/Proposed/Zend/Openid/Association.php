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
 * @package    Zend_Openid
 * @copyright  Copyright (c) 2007 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Service_Yadis */
require_once 'Zend/Service/Yadis.php';

/** Zend_Openid_Request_Association */
require_once 'Zend/Openid/Request/Association.php';

/** Zend_Crypt_DiffieHellman */
require_once 'Zend/Crypt/Hmac.php';

/** Zend_Openid_Exception */
require_once 'Zend/Openid/Exception.php';

/**
 * Retrieves data of an Association with an OP, i.e. where the Consumer has
 * established a shared secret key which will be used by the OP to sign
 * responses with a message authentication code.
 *
 * @category   Zend
 * @package    Zend_Openid
 * @copyright  Copyright (c) 2007 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Openid_Association
{
    /**
     * The type of Identifier a user appears to be using; one of the values
     * defined as constants ASSOCIATION_URI or ASSOCIATION_XRI.
     *
     * @var string
     */
    protected $_identifierType = null;

    /**
     * The Consumer object for which Association data is being requested
     *
     * @var Zend_Openid_Consumer
     */
    protected $_consumer = null;

    /**
     * A store object which is used to both retrieve and write cached
     * Association data.
     *
     * @var Zend_Openid_Store_Interface
     */
    protected $_store = null;

    /**
     * During Yadis Discovery (OpenID 2.0) the initial request may return the
     * HTML retrieved from an OpenID alias URI. This can be cached and reused
     * for HTML based Discovery should the Yadis protocol fail, saving at
     * least one round-trip.
     *
     * @var string
     */
    protected $_userHtml = null;

    /**
     * Array of all characters an XRI identifier can start with.
     *
     * @var array
     */
    private $_xriIdentifiers = array('=', '$', '!', '@', '+');

    /**
     * Constants:
     * The Identifier Type detected
     */
    const ASSOCIATION_URI = 'uri';
    const ASSOCIATION_XRI = 'xri';

    /**
     * Constructor; Creates a new Association object with a reference to the
     * Consumer object which requires the Association data
     *
     * @param Zend_Openid_Consumer $consumer
     */
    public function __construct(Zend_Openid_Consumer $consumer)
    {
        $this->_consumer = $consumer;
        $this->_store = $consumer->getStore();
        $this->_identifierType = $this->getIdentifierType($this->_consumer->getUserIdentifier());
    }

    /**
     * Get Association data. This can be fetched from a valid cache whose
     * Association data has not yet expired, or otherwise by sending
     * a new Association request to the discovered OP server.
     *
     * @return Zend_Openid_KVContainer
     */
    public function getAssociation()
    {
        $opData = $this->discover($this->_consumer->getUserIdentifier());

        // Reset the claimed identifier to the OP Local Identifier if needed
        if (isset($opData['localId']) && !empty($opData['localId'])) {
            $this->_consumer->setClaimedIdentifier($opData['localId']);
        } else {
            $this->_consumer->setClaimedIdentifier($this->_consumer->getUserIdentifier());
        }

        $this->_consumer->setOpEndpoint($opData['opEndpoint']);
        $this->_consumer->setServiceTypes($opData['serviceTypes']);

        if ($this->_hasCachedAssociation($this->_consumer->getOpEndpoint())) {
            $cache = $this->_getCachedAssociation($this->_consumer->getOpEndpoint());
            if ($cache->expires > time()) {
                return $cache;
            }
        } elseif ($this->_hasCachedAssociation($this->_consumer->getClaimedIdentifier())) {
            $cache = $this->_getCachedAssociation($this->_consumer->getClaimedIdentifier());
            // inform subsequent steps that we should operate to a different assoc_type
            if ($cache->assoc_type !== Zend_Openid::getAssocType()) {
                $this->_resetAssocTypeTo($cache->assoc_type);
            }
            if ($cache->expires > time()) {
                return $cache;
            }
        }

        // Previous cache was stale or did not exist.
        $data = $this->_renewAssociation();
        if (isset($data->error)) {
            return $data; // no caching of errors
        }

        $this->_setCachedAssociation($this->_consumer->getOpEndpoint(), $data->toString(), $this->_consumer->getClaimedIdentifier());

        return $data;
    }

    /**
     * Associate with an OP server and retrieve valid association data. The data
     * may be cached based on the OP determined expires_in value (seconds).
     *
     * @return Zend_Openid_KVContainer
     */
    public function associate()
    {
        $associationRequest = new Zend_Openid_Request_Association($this->_consumer);
        $associationResponse = $associationRequest->request();
        $data = $associationResponse->getKV();

        if ($associationResponse->isError()) {
            return $data;
        } elseif (isset($data->mac_key)) {
            // passing the MAC in the clear means the response assumes
            // we're operating as a stateless client - no requirement
            // therefore for Diffie-Hellman processing
            Zend_Openid::setMode(Zend_Openid::OPENID_STATELESS);
        }

        $cache = new Zend_Openid_KVContainer;
        if (Zend_Openid::getMode() !== Zend_Openid::OPENID_STATELESS) {
            $sharedSecret = $this->getSharedSecret($data, $associationRequest->getDiffieHellman());
            $cache->sharedSecret = base64_encode($sharedSecret);
        } else {
            $cache->mac_key = $data->mac_key;
        }

        $cache->assoc_handle = $data->assoc_handle;
        $cache->assoc_type = $data->assoc_type;
        $cache->session_type = $data->session_type;
        $cache->version = Zend_Openid::getVersion();
        $cache->expires = $data->expires_in + time() - 30;
        return $cache;
    }

    /**
     * Discover the OP server by following the User's identity URI (or
     * alternately passing their XRI identity to an XRI Proxy service)
     * and parsing either the resulting Yadis XRD document, or HTML
     * page.
     *
     * One of the above should provide the URI to which OP server
     * requests may be sent.
     *
     * @return array
     */
    public function discover($userIdentifier)
    {
        $OpRequestUri = null;
        $result = false;
        if (Zend_Openid::getVersion() == 2.0) {
            $result = $this->discoverYadis($userIdentifier);
        }
        if ($result === false) {
            $result = $this->discoverHtml($userIdentifier);
        }
        if ($result === false) {
            throw new Zend_Openid_Exception('Unable to discover an OpenID service for this Identifier. Service discovery failed.');
        }
        return $result;
    }

    /**
     * Perform discovery for OpenID 2.0 using the Yadis 1.0 Specification
     * protocol to request the User's identifier URI. If the User's
     * identifier is an XRI, request the XRI using the XDI proxy URI.
     * Return details of the Services discovered and the OP Server URI to
     * which association and authentication request should be directed.
     *
     * @param string $userIdentifier
     * @return array
     */
    public function discoverYadis($userIdentifier)
    {
        $yadis = new Zend_Service_Yadis($userIdentifier);
        $this->_setUserHtml($yadis);
        $yadis->addNamespace('openid', Zend_Openid::OPENID_XML_NAMESPACE);
        $serviceList = $yadis->discover();
        $this->_setUserHtml($yadis);
        if (!$serviceList->valid()) {
            return false;
        }
        foreach($serviceList as $service) {
            $types = $service->getTypes();
            $serverService = ($types == Zend_Openid::OPENID_2_0_SERVICE_SERVER_TYPE || in_array(Zend_Openid::OPENID_2_0_SERVICE_SERVER_TYPE, $types));
            $signonService = ($types == Zend_Openid::OPENID_2_0_SERVICE_SIGNON_TYPE || in_array(Zend_Openid::OPENID_2_0_SERVICE_SIGNON_TYPE, $types));
            if ($serverService === true || $signonService === true) {
                $OpService = $service;
                break 1;
            }
        }
        if (!isset($OpService)) {
            return false;
        }
        if ($this->_identifierType == self::ASSOCIATION_URI) {
             // endpoints need to be prioritised
            $localId = $OpService->getElements('xrd:LocalID');
            $opEndpoint = $OpService->getUris();
            if (isset($localId) && !Zend_Uri::check($localId[0]) || !Zend_Uri::check($opEndpoint[0])) {
                return false;
            }
            $return = array();
            if (isset($localId)) {
                $return['localId'] = $localId[0];
            }
            $return['opEndpoint'] = $opEndpoint[0];
            $return['serviceTypes'] = $types;
            return $return;

        } elseif ($this->_identifierType == self::ASSOCIATION_XRI) {
            return false;
            // testing to be performed
            $canonicalId = $serviceList->getXmlObject->CanonicalID;
            $providerId = $serviceList->getXmlObject->ProviderID;
            if (!$this->_isCanonicalId($canonicalId) || !$this->_isProviderId($providerId)) {
                return false;
            }
            // set as claimed identifier
            exit('Not Implemented');
        }
        return false;
    }

    /**
     * Incomplete; Perform discovery for OpenID 1.1, or OpenID 2.0 (if Yadis
     * protocol discovery failed). Return details of the Services discovered
     * and the OP Server URI to which association and authentication requests
     * should be directed.
     *
     * @todo Needs completion!
     * @param string $userIdentifier
     * @return array
     */
    public function discoverHtml($userIdentifier)
    {
        return false;
        $yadisHtml = $this->_getUserHtml();
        if (!isset($yadisHtml)) {
            // make a new request to get it
        }
        $html = new Zend_Openid_DiscoveryHtml();
        return false;
    }

    /**
     * Based on an Association response and the current Diffie-Hellman session,
     * compute the shared secret established with the OP Server during
     * association.
     *
     * @param Zend_Openid_KVContainer $response
     * @param Zend_Crypt_DiffieHellman $diffieHellman
     * @return string
     */
    public function getSharedSecret(Zend_Openid_KVContainer $response, Zend_Crypt_DiffieHellman $diffieHellman)
    {
        $hashAlgorithm = $this->_getHash($response->assoc_type);
        $enc_mac_key = base64_decode($response->enc_mac_key);
        $dh_server_public = base64_decode($response->dh_server_public);
        $diffieHellman->computeSecretKey($dh_server_public, Zend_Crypt_DiffieHellman::BINARY);
        $dh_shared_secret = $diffieHellman->getSharedSecretKey(Zend_Crypt_DiffieHellman::BTWOC);
        
        $dh_shared_secret_hash = $this->_hash($hashAlgorithm, $dh_shared_secret);

        $shared_secret = '';
        $numberEncodedBytes = strlen( bin2hex($enc_mac_key) ) / 2;
        for ($i = 0;$i < $numberEncodedBytes;$i++) {
            $shared_secret .= chr(ord($enc_mac_key[$i]) ^ ord($dh_shared_secret_hash[$i]));
        }
        return $shared_secret;
    }

    /**
     * Get the type of identifier (URI or XRI) that the Identifier's format
     * matches.
     *
     * @param string $identifier
     * @return string
     */
    public function getIdentifierType($identifier)
    {
        if (Zend_Uri::check($identifier)) {
            $identifierType = self::ASSOCIATION_URI;
        } elseif ($this->_isXri($identifier)) {
            $identifierType = self::ASSOCIATION_XRI;
        } else {
            throw new Zend_Openid_Exception('Discovery may only be performed on a valid URI or XRI Identifier.');
        }
        return $identifierType;
    }

    /**
     * In the event the Association Response is an error, we may be able to
     * recover by altering our required Association Type if an alternate
     * had been suggested by the OP in their response
     *
     * @return Zend_Openid_KVContainer
     */
    protected function _renewAssociation()
    {
        static $_renewed = false; // limit recursion
        $data = $this->associate();
        if (isset($data->error) && $data->error_code == 'unsupported-type' && $_renewed === false) {
            if (isset($data->assoc_type)) {
                $this->_resetAssocTypeTo($cache->assoc_type);
                $_renewed = true;
                $data = $this->_renewAssociation();
            } elseif (isset($data->session_type)) {
                $this->_resetAssocTypeTo($cache->session_type);
                $_renewed = true;
                $data = $this->_renewAssociation();
            } else {
                // may need to revert to (ugh!) stateless unencrypted mode here
                require_once 'Zend/Openid/Exception.php';
                throw new Zend_Openid_Exception('Provider returns an "unsupported-type" response but did not provide an alternate supported method of association');
            }
        }
        return $data;
    }

    /**
     * Check for the existence of a cached association
     *
     * @param string $uri
     * @return bool
     */
    protected function _hasCachedAssociation($uri)
    {
        return $this->_store->hasAssociation($uri);
    }

    /**
     * Check for the existence of a cached association
     *
     * @param string $uri
     * @return bool
     */
    protected function _getCachedAssociation($uri)
    {
        $dataString = $this->_store->getAssociation($uri);
        $data = new Zend_Openid_KVContainer($dataString);
        return $data;
    }

    /**
     * Check for the existence of a cached association
     *
     * @param string $uri
     * @return bool
     */
    protected function _setCachedAssociation($uri, $data, $alias = null)
    {
        return $this->_store->setAssociation($uri, $data, $alias);
    }

    /**
     * Establish whether a claimed identifier is an XRI
     *
     * @var string $claimedIdentifier
     */
    protected function _isXri($identifier)
    {
        if (strpos($identifier, 'xri://') === 0 || in_array($identifier[0], $this->_xriIdentifiers)) {
            return true;
        }
        return false;
    }

    /**
     * Establish whether a claimed identifier is an XRI Canonical ID
     *
     * @todo Some servers will prefix this with the "xri://" protocol
     * @param string $canonicalId
     * @return bool
     */
    protected function _isCanonicalId($canonicalId)
    {
        return (bool) preg_match("/^!=[A-Z0-9]{4}\.[A-Z0-9]{4}\.[A-Z0-9]{4}\.[A-Z0-9]{4}$/", $canonicalId);
    }

    /**
     * Incomplete; Check whether the validity of the optional Provider URI
     *
     * @param string $providerId
     * @return bool
     */
    protected function _isProviderId($providerId)
    {
        return true;
    }

    /**
     * Set the HTML cache of a Yadis protocol request to an OpenID user
     * identifier
     *
     * @param Zend_Service_Yadis $yadis
     * @return void
     */
    protected function _setUserHtml(Zend_Service_Yadis $yadis)
    {
        $this->_userHtml = $yadis->getUserResponse();
    }

    /**
     * Return the HTML cached from Yadis Service Discovery
     *
     * @return string
     */
    protected function _getUserHtml()
    {
        return $this->_userHtml;
    }

    /**
     * Simple hash digest support returning raw binary
     *
     * @param string $value
     * @param string $hash
     * @return string
     */
    protected function _hash($hash, $value)
    {
        return hash($hash, $value, true);
    }

    /**
     * Get a hash type for the current process
     *
     * @param $assocType
     * @return string
     */
    protected function _getHash($assocType)
    {
        if (Zend_Openid::getVersion() == '2.0') {
            $hashAlgorithm = Zend_Openid::OPENID_2_0_HASH_ALGORITHM;
        } else {
            $hashAlgorithm = Zend_Openid::OPENID_1_1_HASH_ALGORITHM;
        }
        if ($hashAlgorithm !== Zend_Openid::OPENID_1_1_HASH_ALGORITHM && preg_match('/' . Zend_Openid::OPENID_1_1_HASH_ALGORITHM . '$/i', $assocType)) {
            $hashAlgorithm = Zend_Openid::OPENID_1_1_HASH_ALGORITHM; // in reality this means the server decided they can't support SHA256 and we may need to re-associate with the SHA1 type.
        }
        return $hashAlgorithm;
    }

    /**
     * Reset association and session types based on the association
     * response from the Provider
     *
     * @param string $type
     * @return void
     */
    protected function _resetAssocTypeTo($type)
    {
        if (preg_match("/SHA256$/i", $type)) {
            Zend_Openid::setAssocType('SHA256');
            Zend_Openid::setSessionType('SHA256');
        } elseif (preg_match("/SHA1$/i", $type)) {
            Zend_Openid::setAssocType('SHA1');
            Zend_Openid::setSessionType('SHA1');
        } else {
            require_once 'Zend/Openid/Exception.php';
            throw new Zend_Openid_Exception('Invalid association type: ', $type);
        }
    }

}