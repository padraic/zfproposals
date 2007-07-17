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
 * @version    $Id: Association.php 50 2007-06-29 16:01:41Z padraic $
 */

/** Zend_Openid_Response_Exception */
require_once 'Zend/Openid/Response/Exception.php';

/** Zend_Openid_KVContainer */
require_once 'Zend/Openid/KVContainer.php';

/**
 * OpenID Authentication 2.0 implementation in PHP5.
 *
 * @category   Zend
 * @package    Zend_Openid
 * @copyright  Copyright (c) 2007 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Openid_Response_Association
{

    /**
     * KVContainer object for this instance used to transform response
     * data into a transfer object capable of parsing and emitting
     * Key:Value formatted data strings
     *
     * @var Zend_Openid_KVContainer
     */
    protected $_kv = null;

    /**
     * Is this response an error?
     *
     * @var boolean
     */
    protected $_isError = false;

    /**
     * Constructor
     *
     * @param Zend_Http_Response $response
     */
    public function __construct(Zend_Http_Response $response)
    {
        $this->setResponse($response);
    }

    /**
     * Parse the resulting Response object's body into a more
     * useful Key:Value format captured by Zend_Openid_KVContainer
     *
     * @param Zend_Http_Response $response
     * @return void
     */
    public function setResponse(Zend_Http_Response $response)
    {
        if (!$response->isSuccessful()) {
            // handle as an error in future
            throw new Zend_Openid_Response_Exception('The OP Server reported an Error in its association reponse');
        }
        try {
            $kv = new Zend_Openid_KVContainer($response->getBody());
        } catch (Zend_Openid_Exception $e) {
            throw new Zend_Openid_Response_Exception($e->getMessage(), $e->getCode());
        }
        if (isset($kv->ns) && !in_array($kv->ns, array(Zend_Openid::OPENID_2_0_NAMESPACE))) {
            if (in_array($kv->ns, array(Zend_Openid::OPENID_1_1_NAMESPACE, Zend_Openid::OPENID_1_0_NAMESPACE))) {
                Zend_Openid::setVersion(1.1);
            } else {
                // Invalid response, but may not yet be implemented by all 2.0 Providers
            }
        }
        if (isset($kv->error)) {
            $this->_isError = true;
            $this->_kv = $kv;
            return;
        }
        if (!$this->_isValid($kv)) {
            throw new Zend_Openid_Response_Exception('Invalid association response received');
        }
        $this->_kv = $kv;
    }

    /**
     * Return the current KVContainer object
     *
     * @return Zend_Openid_KVContainer
     */
    public function getKV()
    {
        return $this->_kv;
    }

    /**
     * Public method to determine if this response container error data
     *
     * @return boolean
     */
    public function isError()
    {
        return $this->_isError;
    }

    /**
     * Validation method to ensure response data isn't malformed or poses
     * a potential security risk.
     *
     * @todo implement nonce comparison to previously received nonces
     * @param Zend_Openid_KVContainer
     * @return boolean
     */
    protected function _isValid(Zend_Openid_KVContainer $kv)
    {
        if (!isset($kv->assoc_handle, $kv->assoc_type, $kv->dh_server_public, $kv->enc_mac_key, $kv->expires_in, $kv->session_type)) {
            return false;
        }
        if (!preg_match("/^\{HMAC-SHA(1|256)\}\{[A-za-z0-9]+\}\{[A-za-z0-9\/\+]+[=]{0,2}\}$/", $kv->assoc_handle)) {
            return false;
        }
        elseif (!preg_match("/^HMAC-SHA(1|256)$/", $kv->assoc_type)) {
            return false;
        }
        elseif (!preg_match("/^[A-za-z0-9\/\+]+[=]{0,2}$/", $kv->enc_mac_key)) {
            return false;
        }
        elseif (!preg_match("/^\d+$/", $kv->expires_in)) {
            return false;
        }
        elseif (!preg_match("/^DH-SHA(1|256)$/", $kv->session_type)) {
            return false;
        }
        return true;
    }

}