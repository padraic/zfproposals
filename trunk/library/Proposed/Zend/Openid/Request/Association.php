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
 * @version    $Id: Association.php 12 2007-06-19 15:37:39Z padraic $
 */

/** Zend_Http_Client */
require_once 'Zend/Http/Client.php';

/** Zend_Crypt_DiffieHellman */
require_once 'Zend/Crypt/DiffieHellman.php';

/** Zend_Openid_Response_Exception */
require_once 'Zend/Openid/Request/Exception.php';

/** Zend_Openid_Response_Association */
require_once 'Zend/Openid/Response/Association.php';

/**
 * OpenID Authentication 2.0 implementation in PHP5.
 *
 * @category   Zend
 * @package    Zend_Openid
 * @copyright  Copyright (c) 2007 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Openid_Request_Association
{

    /**
     * Current Consumer object
     *
     * @var Zend_Openid_Consumer
     */
    protected $_consumer = null;

    /**
     * Cached object for Diffie-Hellman Key Agreement
     *
     * @var Zend_Crypt_DiffieHellman
     */
    protected $_diffieHellman = null;
    
    /**
     * Constructor
     *
     * @param Zend_Openid_Consumer $consumer
     */
    public function __construct(Zend_Openid_Consumer $consumer)
    {
        $this->_consumer = $consumer;
    }

    /**
     * Perform an association request and return the response data
     *
     * @todo Detect stateless mode and amend the default params
     * @return Zend_Openid_Response_Association
     */
    public function request()
    {
        /**
         * Set parameters for association request
         */
        $prime =  Zend_Openid::OPENID_DIFFIEHELLMAN_DEFAULT_PRIME;
        $generator = Zend_Openid::OPENID_DIFFIEHELLMAN_DEFAULT_GENERATOR;
        $diffieHellman = new Zend_Crypt_DiffieHellman($prime, $generator);
        $this->_setDiffieHellman($diffieHellman);
        $diffieHellman->generateKeys();
        $params = array(
            'openid.mode' => 'associate',
            'openid.dh_modulus' => Zend_Openid::OPENID_DIFFIEHELLMAN_DEFAULT_PRIME_BASE64,
            'openid.dh_gen' => Zend_Openid::OPENID_DIFFIEHELLMAN_DEFAULT_GENERATOR_BASE64,
            'openid.dh_consumer_public' => base64_encode($diffieHellman->getPublicKey(Zend_Crypt_DiffieHellman::BTWOC))
        );
        if (Zend_Openid::getVersion() == '2.0') {
            $params['openid.assoc_type'] = 'HMAC-' . Zend_Openid::OPENID_2_0_HASH_ALGORITHM;
            $params['openid.session_type'] = 'DH-' . Zend_Openid::OPENID_2_0_HASH_ALGORITHM;
        } else {
            $params['openid.assoc_type'] = 'HMAC-' . Zend_Openid::OPENID_1_1_HASH_ALGORITHM;
            $params['openid.session_type'] = 'DH-' . Zend_Openid::OPENID_1_1_HASH_ALGORITHM;
        }
        return $this->_makeRequest($this->_consumer->getOpEndpoint(), $params);
    }

    /**
     * Return the Association constructed DiffieHellman object when we need
     * to calculate the shared key for this transaction
     *
     * @return Zend_Crypt_DiffieHellman
     */
    public function getDiffieHellman()
    {
        return $this->_diffieHellman;
    }

    /**
     * Setup Zend_Http_Client to perform the association request and compose
     * a new Zend_Openid_Response_Association object with the response body
     *
     * @param string $uri
     * @param array $params
     * @return Zend_Openid_Response_Association
     */
    protected function _makeRequest($uri, array $params)
    {
        $client = new Zend_Http_Client();
        $client->setUri($uri);
        $client->setMethod('POST');
        $client->setParameterPost($params);
        $response = $client->request();
        if (!$response->isSuccessful()) {
            // may also check for any 400 responses
            throw new Zend_Openid_Request_Exception('Invalid response to OpenID association received: ' . $response->getStatus() . ' ' . $response->getMessage());
        }
        $associationResponse = new Zend_Openid_Response_Association($response);
        return $associationResponse;
    }

    /**
     * Set the DiffieHellman object for this request
     *
     * @param Zend_Crypt_DiffieHellman
     * @return void
     */
    protected function _setDiffieHellman(Zend_Crypt_DiffieHellman $diffieHellman)
    {
        $this->_diffieHellman = $diffieHellman;
    }

}