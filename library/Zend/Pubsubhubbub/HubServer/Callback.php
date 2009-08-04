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
 * to padraic dot brady at yahoo dot com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Pubsubhubbub
 * @copyright  Copyright (c) 2009 Padraic Brady
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Pubsubhubbub
 */
require_once 'Zend/Pubsubhubbub.php';

/**
 * @see Zend_Pubsubhubbub
 */
require_once 'Zend/Pubsubhubbub/CallbackAbstract.php';

/**
 * @see Zend_Feed_Reader
 */
require_once 'Zend/Feed/Reader.php';

/**
 * @category   Zend
 * @package    Zend_Pubsubhubbub
 * @copyright  Copyright (c) 2009 Padraic Brady
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pubsubhubbub_HubServer_Callback
    extends Zend_Pubsubhubbub_CallbackAbstract
{

    /**
     * The URL Hub Servers must use when communicating with this Subscriber
     *
     * @var string
     */
    protected $_callbackUrl = '';

    /**
     * The number of seconds which this Hub defaults to as lease seconds.
     *
     * @var int
     */
    protected $_leaseSeconds = 2592000;

    /**
     * The POST payload as a parameter array. Where multiple values are
     * attached to an identical key, the parameter is an array of those
     * values in the order in which they were presented in the payload.
     *
     * @var array
     */
    protected $_postData = array();

    /**
     * The preferred verification mode (sync or async). By default, this
     * Hub Server prefers synchronous verification, but will support
     * asynchronous in the future.
     *
     * @var string
     */
    protected $_preferredVerificationMode
        = Zend_Pubsubhubbub::VERIFICATION_MODE_SYNC;

    /**
     * Handle any callback related to a subscription, unsubscription or
     * publisher notification of new feed updates.
     *
     * @param array $httpGetData GET data if available (NOT USED BY HUB)
     * @param bool $sendResponseNow Whether to send response now or when asked
     */
    public function handle(array $httpGetData = null, $sendResponseNow = false)
    {
        $this->_postData = $this->_parseParameters();
        if (strtolower($SERVER['REQUEST_METHOD']) !== 'post') {
            $this->setHttpResponseCode(404);
        } elseif ($this->isValidSubscription()) {

        } elseif ($this->isValidUnsubscription()) {

        } else {
            $this->setHttpResponseCode(404);
        }
        if ($sendResponseNow) {
            $this->sendResponse();
        }
    }

    /**
     * Set the callback URL to be used by Publishers or Subscribers when
     * communication with the Hub Server
     *
     * @param string $url
     */
    public function setCallbackUrl($url)
    {
        if (empty($url) || !is_string($url) || !Zend_Uri::check($url)) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid parameter "url"'
                .' of "' . $url . '" must be a non-empty string and a valid'
                .'URL');
        }
        $this->_callbackUrl = $url;
    }

    /**
     * Get the callback URL to be used by Publishers or Subscribers when
     * communication with the Hub Server
     *
     * @return string
     */
    public function getCallbackUrl()
    {
        if (empty($this->_callbackUrl)) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('A valid Callback URL MUST be'
            . ' set before attempting any operation');
        }
        return $this->_callbackUrl;
    }

    /**
     * Set the number of seconds for which any subscription will remain valid
     *
     * @param int $seconds
     */
    public function setLeaseSeconds($seconds)
    {
        $seconds = intval($seconds);
        if ($seconds <= 0) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Expected lease seconds'
            . ' must be an integer greater than zero');
        }
        $this->_leaseSeconds = $seconds;
    }

    /**
     * Get the number of lease seconds on subscriptions
     *
     * @return int
     */
    public function getLeaseSeconds()
    {
        return $this->_leaseSeconds;
    }

    /**
     * Set preferred verification mode (sync or async). By default, this
     * Hub Server prefers synchronous verification, but will support
     * asynchronous in the future.
     *
     * @param string $mode Should be 'sync' or 'async'
     */
    public function setPreferredVerificationMode($mode)
    {
        if ($mode !== Zend_Pubsubhubbub::VERIFICATION_MODE_SYNC
        && $mode !== Zend_Pubsubhubbub::VERIFICATION_MODE_ASYNC) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid preferred'
            . ' mode specified: "' . $mode . '" but should be one of'
            . ' Zend_Pubsubhubbub::VERIFICATION_MODE_SYNC or'
            . ' Zend_Pubsubhubbub::VERIFICATION_MODE_ASYNC');
        }
        $this->_preferredVerificationMode = $mode;
    }

    /**
     * Get preferred verification mode (sync or async).
     *
     * @return string
     */
    public function getPreferredVerificationMode()
    {
        return $this->_preferredVerificationMode;
    }

    /**
     * Checks validity of the request simply by making a quick pass and
     * confirming the presence of all REQUIRED parameters.
     *
     * @param array $httpGetData
     * @return bool
     */
    public function isValidSubscription()
    {
        if ($postData['hub.mode'] !== 'subscribe') {
            return false;
        }
        if (!$this->_hasValidSubscriptionOpParameters()) {
            return false;
        }
        if (array_key_exists('hub.lease_seconds', $this->_postData)) {
            if (intval($this->_postData['hub.lease_seconds']) <= 0) { // can we do this?
                return false;
            }
        }
    }

    /**
     * Checks validity of the request simply by making a quick pass and
     * confirming the presence of all REQUIRED parameters.
     *
     * @param array $httpGetData
     * @return bool
     */
    public function isValidUnsubscription()
    {
        if ($postData['hub.mode'] !== 'unsubscribe') {
            return false;
        }
        if (!$this->_hasValidSubscriptionOpParameters()) {
            return false;
        }
    }

    /**
     * Check validity of request omitting the hub.mode for a subscription or
     * unsubscription POST request
     *
     */
    protected function _hasValidSubscriptionOpParameters()
    {
        $required = array('hub.mode', 'hub.callback',
            'hub.topic', 'hub.verify');
        foreach ($required as $key) {
            if (!array_key_exists($key, $this->_postData)) {
                return false;
            }
        }
        if (!Zend_Uri::check($httpGetData['hub.topic'])) {
            return false;
        }
        return true;
    }

    /**
     * Build an array of POST parameters from the raw body (this prevents)
     * the overwrites of keys in $_POST for repeated keyed parameters
     *
     * @return array|void
     */
    protected function _parseParameters()
    {
        $params = array();
        $body = $this->_getRawBody();
        if (empty($body)) {
            return array();
        }
        $parts = explode('&', $body);
        foreach ($parts as $kvpair) {
            $pair = explode('=', $kvpair);
            $key = urldecode($pair[0]);
            $value = urldecode($pair[1]);
            if (isset($params[$key])) {
                if (is_array($params[$key])) {
                    $params[$key][] = $value;
                } else {
                    $params[$key] = array($value);
                }
            } else {
                $params[$key] = $value;
            }
        }
        return $params;
    }

}
