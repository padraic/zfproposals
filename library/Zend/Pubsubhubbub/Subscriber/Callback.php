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
class Zend_Pubsubhubbub_Subscriber_Callback
    extends Zend_Pubsubhubbub_CallbackAbstract
{

    /**
     * Contains the content of any feeds sent as updates to the Callback URL
     *
     * @var string
     */
    protected $_feedUpdate = null;

    /**
     * Handle any callback from a Hub Server responding to a subscription or
     * unsubscription request. This should be the Hub Server confirming the
     * the request prior to taking action on it.
     *
     * @param array $httpGetData GET data if available and not in $_GET
     * @param bool $sendResponseNow Whether to send response now or when asked
     */
    public function handle(array $httpGetData = null, $sendResponseNow = false)
    {
        if ($httpGetData === null) {
            $httpGetData = $_GET;
        }
        /**
         * Handle any feed updates (sorry for the mess :P) This MUST change to ignore feed validity ASAP
         * This should remove Zend_Feed_Reader as an immediate dependency
         */
        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post'
        && $this->_hasValidVerifyToken(null, false)
        && ($this->_getHeader('Content-Type') == 'application/atom+xml'
        || $this->_getHeader('Content-Type') == 'application/rss+xml'
        || $this->_getHeader('Content-Type') == 'application/rdf+xml')) {
            $this->setFeedUpdate($this->_getRawBody());
            $this->getHttpResponse()->setHeader('X-Hub-On-Behalf-Of',
                $this->getSubscriberCount());
        /**
         * Handle any (un)subscribe confirmation requests
         */
        } elseif ($this->isValidHubVerification($httpGetData)) {
            $this->getHttpResponse()->setBody($httpGetData['hub_challenge']);
        /**
         * Hey, C'mon! We tried everything else!
         */
        } else {
            $this->getHttpResponse()->setHttpResponseCode(404);
        }
        if ($sendResponseNow) {
            $this->sendResponse();
        }
    }

    /**
     * Checks validity of the request simply by making a quick pass and
     * confirming the presence of all REQUIRED parameters.
     *
     * @param array $httpGetData
     * @return bool
     */
    public function isValidHubVerification(array $httpGetData)
    {
        /**
         * As per the specification, the hub.verify_token is OPTIONAL. This
         * implementation of Pubsubhubbub considers it REQUIRED and will
         * always send a hub.verify_token parameter to be echoed back
         * by the Hub Server. Therefore, its absence is considered invalid.
         */
        if (strtolower($_SERVER['REQUEST_METHOD']) !== 'get') {
            return false;
        }
        $required = array('hub_mode', 'hub_topic',
            'hub_challenge', 'hub_verify_token');
        foreach ($required as $key) {
            if (!array_key_exists($key, $httpGetData)) {
                return false;
            }
        }
        if ($httpGetData['hub_mode'] !== 'subscribe'
        && $httpGetData['hub_mode'] !== 'unsubscribe') {
            return false;
        }
        if ($httpGetData['hub_mode'] == 'subscribe'
        && !array_key_exists('hub_lease_seconds', $httpGetData)) {
            return false;
        }
        if (!Zend_Uri::check($httpGetData['hub_topic'])) {
            return false;
        }
        /**
         * Attempt to retrieve any Verification Token Key attached to Callback
         * URL's path by our Subscriber implementation
         */
        if (!$this->_hasValidVerifyToken($httpGetData)) {
            return false;
        }
        return true;
    }

    /**
     * Sets a newly received feed (Atom/RSS) sent by a Hub as an update to a
     * Topic we've subscribed to.
     *
     * @param string $feed
     */
    public function setFeedUpdate($feed)
    {
        $this->_feedUpdate = $feed;
    }

    /**
     * Check if any newly received feed (Atom/RSS) update was received
     */
    public function hasFeedUpdate()
    {
        if (is_null($this->_feedUpdate)) {
            return false;
        }
        return true;
    }

    /**
     * Gets a newly received feed (Atom/RSS) sent by a Hub as an update to a
     * Topic we've subscribed to.
     *
     * @return string
     */
    public function getFeedUpdate()
    {
        return $this->_feedUpdate;
    }

    /**
     * Check for a valid verify_token. By default attempts to compare values
     * with that sent from Hub, otherwise merely ascertains its existence.
     *
     * @param array $httpGetData
     * @param bool $checkValue
     * @return bool
     */
    protected function _hasValidVerifyToken(array $httpGetData = null, $checkValue = true)
    {
        $verifyTokenKey = $this->_detectVerifyTokenKey();
        if (empty($verifyTokenKey)) {
            return false;
        }
        $verifyTokenExists = $this->getStorage()->hasToken($verifyTokenKey);
        if (!$verifyTokenExists) {
            return false;
        }
        if ($checkValue) {
            $verifyToken = $this->getStorage()->getToken($verifyTokenKey);
            if ($verifyToken !== hash('sha256', $httpGetData['hub_verify_token'])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Attempt to detect the verification token key. This would be passed in
     * the Callback URL (which we are handling with this class!) as a URI
     * path part (the last part by convention).
     *
     * Since specification disallows use of query string, attempt to detect
     * based the URI being requested (if we can discover it)
     *
     * @return string
     */
    protected function _detectVerifyTokenKey()
    {
        $params = $this->_parseQueryString();
        if (isset($params['xhub.subscription'])) {
            return $params['xhub.subscription'];
        }
        return false;
    }

    /**
     * Build an array of Query String parameters.
     * This bypasses $_GET which munges parameter names and cannot accept
     * multiple parameters with the same key.
     *
     * @return array|void
     */
    protected function _parseQueryString()
    {
        $params = array();
        $queryString = '';
        if (isset($_SERVER['QUERY_STRING'])) {
            $queryString = $_SERVER['QUERY_STRING'];
        }
        if (empty($queryString)) {
            return array();
        }
        $parts = explode('&', $queryString);
        foreach ($parts as $kvpair) {
            $pair = explode('=', $kvpair);
            $key = rawurldecode($pair[0]);
            $value = rawurldecode($pair[1]);
            if (isset($params[$key])) {
                if (is_array($params[$key])) {
                    $params[$key][] = $value;
                } else {
                    $params[$key] = array($params[$key], $value);
                }
            } else {
                $params[$key] = $value;
            }
        }
        return $params;
    }


}
