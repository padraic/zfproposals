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
 * @version    $Id: Authorisation.php 42 2007-06-25 15:23:56Z padraic $
 */

/**
 * Container for an Authorisation Redirect back to the Identity Provider
 *
 * @category   Zend
 * @package    Zend_Openid
 * @copyright  Copyright (c) 2007 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Openid_Redirect_Authorisation
{

    /**
     * Association data for this request
     *
     * @var Zend_Openid_KVContainer
     */
    protected $_associationData = null;

    /**
     * Arguments to be sent with the authorisation request
     *
     * @var array
     */
    protected $_arguments = array();

    /**
     * Optional extension arguments to be sent with the authorisation
     * request
     *
     * @var array
     */
    protected $_extensionArguments = array();

    /**
     *
    protected $_extensionNamespacesSet = array();

    protected $_consumer = null;

    /**
     * Constructor; instantiate a new Authorisation Request object with the
     * association data for a new request.
     *
     * @param Zend_Openid_KVContainer $associationData
     * @param null|string $identityProviderRequestUri
     * @return void
     */
    public function __construct(Zend_Openid_KVContainer $associationData, Zend_Openid_Consumer $consumer)
    {
        $this->_consumer = $consumer;
        $this->_associationData = $associationData;
    }

    /**
     * Perform the redirect to the Identity Provider
     *
     * @param string $returnTo
     * @param string $trustRoot
     * @param bool $immediateResult
     * @return void
     */
    public function redirect($returnTo, $trustRoot, $immediateResult = false)
    {
        $redirectUri = $this->getRedirectUri($returnTo, $trustRoot, $immediateResult = false);
        if (!headers_sent()) {
            header('Location: ' . $redirectUri);
            exit(0);
        }
        require_once 'Zend/Openid/Redirect/Exception.php';
        throw new Zend_Openid_Redirect_Exception('unable to redirect to the OpenID Identity Provider server since Headers have already been sent and I cannot perform a redirect');
    }

    /**
     * Get the URI to which we should redirect the user. Setting the
     * immediateResult option to true will request the Identity Provider
     * to authorise immediately and to not interact with the User.
     * If this request fails, you'll need to try again.
     *
     * @todo Need to shift around the hacked crap from OIDLIB-0.6 and implement 2.0 Extensions in here.
     * @param string $returnTo
     * @param string $trustRoot
     * @param bool $immediateResult
     * @return string
     */
    public function getRedirectUri($returnTo, $trustRoot, $immediateResult = false)
    {
        /**
         * Move this to an optional methods. Check it is matched by the
         * provided trust root or else throw an Exception.
         */
        if (!Zend_Uri::check($returnTo)) {
            require_once 'Zend/Openid/Redirect/Exception.php';
            throw new Zend_Openid_Redirect_Exception('Return-To URI is not a valid URI');
        }

        // generate the arguments to be appended to redirect URI as the query string
        $this->_prepareArguments($returnTo, $trustRoot, $immediateResult);
        $allArgs = array_merge($this->getArguments(), $this->getExtensionArguments());
        $queryString = http_build_query($allArgs, null, '&');
        $redirectUri = $this->_consumer->getOpEndpoint() . '?' . $queryString;

        $stateData = array(
            'return_to' => $returnTo,
            'trust_root' => $trustRoot,
            'claimed_identifier' => $this->_consumer->getClaimedIdentifier(),
            'op_endpoint' => $this->_consumer->getOpEndpoint(),
            'time' => time(),
            'version' => Zend_Openid::getVersion(),
            'shared_secret' => $this->getAssociationData()->sharedSecret
        );
        if (isset($this->getAssociationData()->assoc_handle)) {
            $stateData['assoc_handle'] = $this->getAssociationData()->assoc_handle;
        }
        $this->_consumer->addSessionData($stateData);

        return $redirectUri;
    }

    /**
     * Add a new request argument to this request, and add the openid
     * prefix if missing.
     *
     * @param string $key
     * @param string $value
     */
    public function setArgument($key, $value)
    {
        if (strrpos($key, '.') === false) {
            $key = implode('.', array(Zend_Openid::OPENID_ARGUMENT_KEY_PREFIX, $key));
        }
        $this->_arguments[$key] = $value;
    }

    /**
     * Getter to return an array of all non-Extension arguments
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->_arguments;
    }

    /**
     * Add an extension argument to this request
     *
     * @param string $namespace
     * @param string $key
     * @param string $value
     * @return void
     */
    public function setExtensionArgument($namespace, $key, $value)
    {
        if (!in_array($namespace, $this->_extensionNamespacesSet)) {
            $namespacesAllowed = array_keys(Zend_Openid::getExtensionNamespaces());
            if (in_array($namespace, $namespacesAllowed)) {
                $this->_extensionNamespacesSet[] = $namespace;
                $nsKey = implode('.', array(Zend_Openid::OPENID_ARGUMENT_KEY_PREFIX, 'ns', $namespace));
                $this->_extensionArguments[$nsKey] = Zend_Openid::getExtensionNamespace($namespace);
            }
        }
        if (strrpos($namespace, '.') === false) {
            $namespace = implode('.', array(Zend_Openid::OPENID_ARGUMENT_KEY_PREFIX, $namespace));
        }
        $extensionArgKey = implode('.', array($namespace, $key));
        $this->_extensionArguments[$extensionArgKey] = $value;
    }

    /**
     * Getter to return an array of all Extension arguments
     *
     * @return array
     */
    public function getExtensionArguments()
    {
        return $this->_extensionArguments;
    }
    
    /**
     * Getter for the Association Data
     *
     * @return Zend_Openid_KVContainer|null
     */
    public function getAssociationData()
    {
        return $this->_associationData;
    }

    /**
     * OpenID 1.1 and 2.0 differ on their argument list. Setup the
     * arguments based on the specific OpenID Sepcification verison
     * this consumer is applying.
     * 
     * @return void
     */
    protected function _prepareArguments($returnTo, $trustRoot, $immediateResult)
    {
        $mode = ($immediateResult === false) ? 'checkid_setup' : 'checkid_immediate'; 
        if (Zend_Openid::getVersion() == '2.0') {
            $this->setArgument('ns', Zend_Openid::OPENID_2_0_NAMESPACE);
            $this->setArgument('mode', $mode);
            $this->setArgument('identity', $this->_consumer->getClaimedIdentifier());
            $this->setArgument('claimed_id', $this->_consumer->getClaimedIdentifier());
            $this->setArgument('return_to', $returnTo);
            $this->setArgument('realm', $trustRoot);
        } else {
            $this->setArgument('mode', $mode);
            $this->setArgument('identity', $this->_consumer->getClaimedIdentifier());
            $this->setArgument('return_to', $returnTo);
            $this->setArgument('trust_root', $trustRoot);
        }

        $assoc = $this->getAssociationData();
        if (isset($assoc) && isset($assoc->assoc_handle)) {
            $this->setArgument('assoc_handle', $assoc->assoc_handle);
        } else {
            // something for stateless mode when I can give a care for it later
        }
    }

}