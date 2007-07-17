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

/** Zend_Uri */
require_once 'Zend/Uri.php';

/** Zend_Openid */
require_once 'Zend/Openid.php';

/** Zend_Openid_Association */
require_once 'Zend/Openid/Association.php';

/** Zend_Openid_Redirect_Authorisation */
require_once 'Zend/Openid/Redirect/Authorisation.php';

/** Zend_Openid_Response_Authorisation */
require_once 'Zend/Openid/Response/Authorisation.php';

/** Zend_Crypt_Hmac */
require_once 'Zend/Crypt/Hmac.php';

/**
 * OpenID Authentication 2.0 implementation in PHP5.
 *
 * @todo Remove unneeded XRI parsing checks in favour of a simple _isXRI boolean
 * @category   Zend
 * @package    Zend_Openid
 * @copyright  Copyright (c) 2007 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Openid_Consumer extends Zend_Openid
{

    /**
     * The URL/XRI supplied by the end user as an Identifier but which has not
     * yet been determined as a valid Claimed Identifier.
     *
     * @var string
     */
    private $_userIdentifier = null;

    /**
     * The URL/XRI supplied by the end user which they are claiming as an OpenID
     *
     * @var string
     */
    private $_claimedIdentifier = null;

    /**
     * The final OP-Local Identifier the OP has recognised.
     * This is typically the Identifier any User aliases are pointing to.
     *
     * @var string
     */
    private $_localIdentifier = null;

    /**
     * An object representing a method of storing associations
     *
     * @var Zend_Openid_Store_Interface
     */
    private $_store = null;

    /**
     * An array representing the SESSION
     *
     * @var array
     */
    private $_session = null;

    /**
     * Request URI for the Identity Provider
     *
     * @var string
     */
    private $_opEndpoint = null;

    /**
     * Association request type
     */
    private $_associationRequest = null;

    /**
     * List of Service types recovered during discovery of the OP Server
     */
    private $_serviceTypes = array();

    /**
     * Identifier characters for an XRI name
     *
     * @var array
     */
    private $_xriIdentifiers = array(
            '=', '$', '!', '@', '+'
        );

    /**
     * Constructor;
     *
     * @param Zend_Openid_Store_Interface $store
     * @param array $session
     * @return void
     */
    public function __construct(Zend_Openid_Store_Interface $store, array $session = null)
    {
        $this->_store = $store;
        $this->_session = $session; // not used yet
    }

    /**
     * Commence Association/Authorisation; the act of establishing a shared
     * secret key for encrypting subsequent communication.
     * The Claimed Identifier (user's URL) is used to locate the Identity
     * Provider server with which to associate using an OpenID 1.1 backwards
     * compatible Yadis Protocol.
     * If cached, the cached association data is returned preferentially.
     *
     * @param string $userIdentifier
     * @return Zend_Openid_KVContainer
     */
    public function start($userIdentifier)
    {
        $this->setUserIdentifier($userIdentifier);
        $association = new Zend_Openid_Association($this);
        $assocData = $association->getAssociation();
        if (isset($assocData->error)) {
            require_once 'Zend/Openid/Exception.php';
            throw new Zend_Openid_Exception('unable to association with the user\'s OP Provider: "' . $assocData->error . '"');
        }
        $authoriseRedirect = new Zend_Openid_Redirect_Authorisation($assocData, $this);
        return $authoriseRedirect;
    }

    /**
     * Finish authorisation by processing an authorisation response from
     * the server and returning a relevant success/other return object
     *
     * @param array $response Typically just $_GET or a validated array
     * @return Zend_Openid_Response_Authorisation
     */
    public function finish(array $params)
    {
        try {
            $authorisaton = new Zend_Openid_Response_Authorisation($params);
            return $authorisation->getResult();
        } catch (Zend_Openid_Response_Exception $e) {
            throw $e;
        }
    }

    /**
     * Set the user's URL (User Identifier) to be used
     *
     * @param string $UserIdentifier
     * @return void
     */
    public function setUserIdentifier($userIdentifier)
    {
        if ($this->_isXri($userIdentifier)) {
            $this->_userIdentifier = $userIdentifier;
            return;
        }
        if (strpos($userIdentifier, "://") === false) {
            $userIdentifier = rtrim("http://" . $userIdentifier, '\\/') . '/';
        }
        if (!Zend_Uri::check($userIdentifier)) {
            require_once 'Zend/Openid/Exception.php';
            throw new Zend_Openid_Exception('the User Identifier is not a valid URI or an XRI');
        }
        $this->_userIdentifier = $userIdentifier;
    }

    /**
     * Getter for the User Identifier
     *
     * @return string
     */
    public function getUserIdentifier()
    {
        return $this->_userIdentifier;
    }

    /**
     * Set the user's URL (Claimed Identifier) to be used
     *
     * @param string $claimedIdentifier
     * @return void
     */
    public function setClaimedIdentifier($claimedIdentifier)
    {
        if ($this->_isXri($claimedIdentifier)) {
            $this->_claimedIdentifier = $claimedIdentifier;
            return;
        }
        // otherwise it must be a URI
        if (strpos($claimedIdentifier, "://") === false) {
            $claimedIdentifier = rtrim("http://" . $claimedIdentifier, '\\/') . '/';
        }
        if (!Zend_Uri::check($claimedIdentifier)) {
            require_once 'Zend/Openid/Exception.php';
            throw new Zend_Openid_Exception('the Claimed Identifier is not a valid URI or an XRI');
        }
        $this->_claimedIdentifier = $claimedIdentifier;
    }

    /**
     * Getter for the Claimed Identifier
     *
     * @return string
     */
    public function getClaimedIdentifier()
    {
        return $this->_claimedIdentifier;
    }

    /**
     * This is the Identifier any user's alias corresponds to which
     * is determined during discovery.
     *
     * @param string $finalIdentifier
     * @return void
     */
    public function setLocalIdentifier($localIdentifier)
    {
        // if an XRI we should have located the associated Canonical ID by now
        if ($this->_isCanonicalId($localIdentifier)) {
            $this->_localIdentifier = $localIdentifier;
            return;
        }
        // otherwise it must be a URI
        if (strpos($localIdentifier, "://") === false) {
            $localIdentifier = rtrim("http://" . $localIdentifier, '\\/') . '/';
        }
        if (!Zend_Uri::check($localIdentifier)) {
            require_once 'Zend/Openid/Exception.php';
            throw new Zend_Openid_Exception('the Local OP Identifier is not a valid URI or an XRI Canonical ID');
        }
        $this->_localIdentifier = $localIdentifier;
    }

    /**
     * Getter for the Local Identifier
     *
     * @return string
     */
    public function getLocalIdentifier()
    {
        if (!isset($this->_localIdentifier)) {
            require_once 'Zend/Openid/Exception.php';
            throw new Zend_Openid_Exception('the Local Identifier; an OpenID Provider recognised Identity has not yet been set');
        }
        return $this->_localIdentifier;
    }

    /**
     * Setter for the Identity Provider Request URI
     *
     * @param string $requestUri
     * @return void
     */
    public function setOpEndpoint($requestUri)
    {
        $requestUri = trim($requestUri, '\\/');
        if (!Zend_Uri::check($requestUri)) {
            require_once 'Zend/Openid/Exception.php';
            throw new Zend_Openid_Exception('The Identity Provider URI discovered is not a valid URI');
        }
        $this->_opEndpoint = $requestUri;
    }

    /**
     * Getter for the Identity Provider Request URI
     *
     * @return string
     */
    public function getOpEndpoint()
    {
        return $this->_opEndpoint;
    }

    /**
     * Fetch the current Zend_Openid_Store_Interface object
     *
     * @return Zend_Openid_Store_Interface
     */
    public function getStore()
    {
        return $this->_store;
    }

    public function setServiceTypes(array $services)
    {
        $this->_services = $services;
    }

    public function getServiceTypes()
    {
        return $this->_services;
    }

    /**
     * Get the last Association request object
     *
     * @return Zend_Openid_Request_Association
     */
    public function getAssociationRequest()
    {
        return $this->_associationRequest;
    }

    /**
     * Simple Session wrapper until something more robust turns up
     *
     * @param array $data
     */
    public function addSessionData(array $data)
    {
        foreach($data as $key => $val) {
            $_SESSION[Zend_Openid::OPENID_PHP_SESSION_NAMESPACE][$key] = $val;
        }
    }

    /**
     * Simple Session wrapper until something more robust turns up
     *
     * @param array $data
     */
    public function getSessionData(array $data)
    {
        return $_SESSION[Zend_Openid::OPENID_PHP_SESSION_NAMESPACE];
    }

    /**
     * Establish whether a claimed identifier is an XRI
     *
     * @var string $claimedIdentifier
     */
    private function _isXri($identifier)
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
     * @param string $claimedIdentifier
     * @return bool
     */
    private function _isCanonicalId(&$identifier)
    {
        return (bool) preg_match("/^!=[A-Z0-9]{4}\.[A-Z0-9]{4}\.[A-Z0-9]{4}\.[A-Z0-9]{4}$/", $identifier);
    }

}