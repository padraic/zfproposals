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

/** Zend_Openid_Response_Exception */
require_once 'Zend/Openid/Response/Exception.php';

/** Zend_Crypt_Hmac */
require_once 'Zend/Crypt/Hmac.php';

/**
 * Container for an Authorisation Response
 *
 * @category   Zend
 * @package    Zend_Openid
 * @copyright  Copyright (c) 2007 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Openid_Response_Authorisation
{

    /**
     * Stored parameters for this Response
     *
     * @var array
     */
    protected $_params = array();

    /**
     * Typical namespaces in an authorisation response
     *
     * @var array
     */
    protected $_authResponseNamespaces = array('openid', 'ns');

    /**
     * Stores for later retrieval, the response type. See the list of
     * possible authorisation response Constants defined in Zend_Openid
     *
     * @var string
     */
    protected $_result = false;

    /**
     * Constructor
     *
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->_setParams($params);
        $this->_result = $this->_check();
    }

    /**
     * Return the current result type; one of the authorisation
     * response Constant values in Zend_Openid
     *
     * @return string
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * Gets a value from the current namespaces $_SESSION array
     *
     * @param string $key
     * @return mixed
     */
    public function getSessionValue($key)
    {
        if (isset($_SESSION[Zend_Openid::OPENID_PHP_SESSION_NAMESPACE][$key])) {
            return $_SESSION[Zend_Openid::OPENID_PHP_SESSION_NAMESPACE][$key];
        }
        return null;
    }

    /**
     * Based on an array of values, constructs the Key:Value form
     * of all key names (i.e. period delimited key spacing)
     *
     * @param array $params
     * @return void
     */
    public function _setParams(array $params)
    {
        $namespaces = $this->_authResponseNamespaces;
        $malformedKeys = array_keys($params);
        $arguments = array();
        foreach($malformedKeys as $key) {
            $fixed = $key;
            if (preg_match("/^openid/", $key)) {
                foreach($namespaces as $namespace) {
                    if (preg_match("/{$namespace}_/", $key)) {
                        $fixed = preg_replace("/{$namespace}_/", $namespace . '.', $fixed);
                    }
                }
                if ($fixed != $key) {
                    $value = $params[$key];
                    unset($params[$key]);
                    $arguments[$fixed] = $value;
                }
            }
        }

        $this->_params = $arguments;
    }

    /**
     * Return the array of parameters
     *
     * @return array
     */
    public function getParams()
    {
        if (empty($this->_params)) {
            throw new Zend_Openid_Response_Exception('No query string values were found');
        }
        return $this->_params;
    }

    /**
     * Get a specific parameter from the response
     *
     * @param string $key
     * @return string
     */
    public function get($key)
    {
        if (isset($this->_params[$key])) {
            return $this->_params[$key];
        }
        return null;
    }

    /**
     * Perform a sequence of validation checks on the response data including
     * signature matching to ensure the encrypted data was returned from a
     * Provider server which we have agreed a Diffie-Hellman shared key with
     *
     * @return
     */
    protected function _check()
    {
        $keys = explode(',', $this->_params['openid.signed']);

        $hashAlgorithm = 'SHA256';
        if (isset($this->_params['openid.assoc_type'])) {
            $hashAlgorithm = Zend_Openid::getHashFromType($this->_params['openid.assoc_type']);
        }

        // check all signed keys actually exist in the response
        $signedKeys = array();
        foreach($keys as $key) {
            $fixed = $key;
            if (preg_match("/^openid/", $key)) {
                if (preg_match("/{$namespace}_/", $key)) {
                    $fixed = preg_replace("/{$namespace}_/", $namespace . '.', $fixed);
                }
            }
            $signedKeys[] = $fixed;
        }
        foreach($signedKeys as $key) {
            if (!in_array($key, $keys)) {
                return Zend_Openid::OPENID_RESPONSE_PARSE_ERROR;
            }
        }

        // construct the KV form of the response
        $signedData = '';
        foreach($signedKeys as $key) {
            $signedData .= $key . ':' . $this->_params['openid.' . $key] . "\n";
        }

        // check the signature
        $sharedSecret = base64_decode($this->getSessionValue('shared_secret'));
        $opSignature = base64_decode($this->_params['openid.sig']);
        $hmac = new Zend_Crypt_Hmac($sharedSecret, $hashAlgorithm);
        $thisSignature = $hmac->hash($signedData, Zend_Crypt_Hmac::BINARY);
        if ($thisSignature !== $opSignature) {
            return Zend_Openid::OPENID_RESPONSE_PARSE_ERROR;
        }

        // check namespaces are returned and valid
        if (isset($this->_params['openid.ns'])) {
            if (in_array($this->_params['openid.ns'], array(Zend_Openid::OPENID_1_1_NAMESPACE, Zend_Openid::OPENID_1_0_NAMESPACE))) {
                Zend_Openid::setVersion(1.1);
            } elseif ($this->_params['openid.ns'] !== Zend_Openid::OPENID_2_0_NAMESPACE) {
                return Zend_Openid::OPENID_RESPONSE_PARSE_ERROR;
            }
        }

        // check mode values
        if (isset($this->_params['openid.mode'])) {
            if ($this->_params['openid.mode'] == 'setup_needed') {
                return Zend_Openid::OPENID_RESPONSE_SETUP_NEEDED;
            } elseif ($this->_params['openid.mode'] == 'cancel') {
                return Zend_Openid::OPENID_RESPONSE_CANCEL;
            } elseif ($this->_params['openid.mode'] !== 'id_res') {
                return Zend_Openid::OPENID_RESPONSE_PARSE_ERROR;
            }
        }

        // check validity of the nonce
        if (strlen($this->_params['openid.response_nonce']) >= 255 || !preg_match("/^\d{4}-\d{2}-\d{2}T{1}\d{2}:\d{2}:\d{2}Z[0-9A-Za-z]{1,234}$/", $this->_params['openid.response_nonce'])) {
            return Zend_Openid::OPENID_RESPONSE_PARSE_ERROR;
        } else {
            // check we haven't previously received this nonce from the OP
                // otherwise this request is suspect and should be discarded
        }

        return Zend_Openid::OPENID_RESPONSE_SUCCESS;
    }

}