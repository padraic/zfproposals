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
 * @see Zend_Pubsubhubbub_HttpResponse
 */
require_once 'Zend/Pubsubhubbub/HttpResponse.php';


/**
 * @see Zend_Feed_Reader My baby!
 */
require_once 'Zend/Feed/Reader.php';

/**
 * @category   Zend
 * @package    Zend_Pubsubhubbub
 * @copyright  Copyright (c) 2009 Padraic Brady
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pubsubhubbub_Subscriber_Callback
{

    /**
     * An instance of Zend_Pubsubhubbub_StorageInterface used to background
     * save any verification tokens associated with a subscription or other.
     *
     * @var Zend_Pubsubhubbub_StorageInterface
     */
    protected $_storage = null;

    /**
     * An instance of a class handling Http Responses. This is implemented in
     * Zend_Pubsubhubbub_HttpResponse which shares an unenforced interface with
     * (i.e. not inherited from) Zend_Controller_Response_Http.
     *
     * @var Zend_Pubsubhubbub_HttpResponse|Zend_Controller_Response_Http
     */
    protected $_httpResponse = null;

    /**
     * The number of Subscribers for which any updates are on behalf of.
     *
     * @var int
     */
    protected $_subscriberCount = 1;

    /**
     * Contains the content of any feeds sent as updates to the Callback URL
     *
     * @var string
     */
    protected $_feedUpdate = null;

    /**
     * Contains an instance of Zend_Feed_Reader_FeedAbstract containing the
     * feed update
     *
     * @var Zend_Feed_Reader_FeedAbstract
     */
    protected $_feedObject = null;

    /**
     * Handle any callback from a Hub Server responding to a subscription or
     * unsubscription request. This should be the Hub Server confirming the
     * the request prior to taking action on it.
     *
     */
    public function handle(array $httpGetData = null, $sendResponseNow = false)
    {
        if ($httpGetData === null) {
            $httpGetData = $_GET;
        }
        /**
         * Handle any feed updates (sorry for the mess :P)
         */
        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post'
        && $this->_hasValidVerifyToken(null, false)
        && ($this->_getHeader('Content-Type') == 'application/atom+xml'
        || $this->_getHeader('Content-Type') == 'application/rss+xml'
        || $this->_getHeader('Content-Type') == 'application/rdf+xml')) {
            $feed = Zend_Feed_Reader::importString($this->_getRawBody());
            $feedType = Zend_Feed_Reader::detectType($feed);
            if ((substr($feedType, 0, 4) == 'atom'
            && $this->_getHeader('Content-Type') == 'application/atom+xml')
            || (substr($feedType, 0, 3) == 'rss'
            && ($this->_getHeader('Content-Type') == 'application/rss+xml'
            || $this->_getHeader('Content-Type') == 'application/rdf+xml'))) {
                $this->setFeedUpdate($this->_getRawBody());
                $this->setFeedUpdateObject($feed);
                $this->getHttpResponse()->setHeader('X-Hub-On-Behalf-Of',
                    $this->getSubscriberCount());
            } else {
                $this->getHttpResponse()->setHttpResponseCode(404);
            }
        /**
         * Handle any (un)subscribe confirmation requests
         */
        } elseif ($this->isValidHubVerification($httpGetData)) {
            $this->getHttpResponse()->setBody($httpGetData['hub.challenge']);
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
     * Send the response, including all headers.
     * If you wish to handle this via Zend_Controller, use the getter methods
     * to retrieve any data needed to be set on your HTTP Response object, or
     * simply give this object the HTTP Response instance to work with for you!
     *
     * @return void
     */
    public function sendResponse()
    {
        $this->getHttpResponse()->sendResponse();
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
        $required = array('hub.mode', 'hub.topic',
            'hub.challenge', 'hub.verify_token');
        foreach ($required as $key) {
            if (!array_key_exists($key, $httpGetData)) {
                return false;
            }
        }
        if ($httpGetData['hub.mode'] !== 'subscribe'
        && $httpGetData['hub.mode'] !== 'unsubscribe') {
            return false;
        }
        if ($httpGetData['hub.mode'] == 'subscribe'
        && !array_key_exists('hub.lease_seconds', $httpGetData)) {
            return false;
        }
        if (!Zend_Uri::check($httpGetData['hub.topic'])) {
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
     * Sets an instance of Zend_Pubsubhubbub_StorageInterface used to background
     * save any verification tokens associated with a subscription or other.
     *
     * @param Zend_Pubsubhubbub_StorageInterface $storage
     */
    public function setStorage(Zend_Pubsubhubbub_StorageInterface $storage)
    {
        $this->_storage = $storage;
    }

    /**
     * Gets an instance of Zend_Pubsubhubbub_StorageInterface used to background
     * save any verification tokens associated with a subscription or other.
     *
     * @return Zend_Pubsubhubbub_StorageInterface
     */
    public function getStorage()
    {
        if ($this->_storage === null) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('No storage object has been'
            . ' set that implements Zend_Pubsubhubbub_StorageInterface');
        }
        return $this->_storage;
    }

    /**
     * An instance of a class handling Http Responses. This is implemented in
     * Zend_Pubsubhubbub_HttpResponse which shares an unenforced interface with
     * (i.e. not inherited from) Zend_Controller_Response_Http.
     *
     * @param Zend_Pubsubhubbub_HttpResponse|Zend_Controller_Response_Http $httpResponse
     */
    public function setHttpResponse($httpResponse)
    {
        if (!is_object($httpResponse)
        || (!$httpResponse instanceof Zend_Pubsubhubbub_HttpResponse
        && !$httpResponse instanceof Zend_Controller_Response_Http)) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('HTTP Response object must'
            . ' implement one of Zend_Pubsubhubbub_HttpResponse or'
            . ' Zend_Controller_Response_Http');
        }
        $this->_httpResponse = $httpResponse;
    }

    /**
     * An instance of a class handling Http Responses. This is implemented in
     * Zend_Pubsubhubbub_HttpResponse which shares an unenforced interface with
     * (i.e. not inherited from) Zend_Controller_Response_Http.
     *
     * @return Zend_Pubsubhubbub_HttpResponse|Zend_Controller_Response_Http
     */
    public function getHttpResponse()
    {
        if ($this->_httpResponse === null) {
            $this->_httpResponse = new Zend_Pubsubhubbub_HttpResponse;
        }
        return $this->_httpResponse;
    }

    /**
     * Sets the number of Subscribers for which any updates are on behalf of.
     * In other words, is this class serving one or more subscribers? How many?
     * Defaults to 1 if left unchanged.
     *
     * @param string|int $count
     */
    public function setSubscriberCount($count)
    {
        $count = intval($count);
        if ($count <= 0) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Subscriber count must be'
            . ' greater than zero');
        }
        $this->_subscriberCount = $count;
    }

    /**
     * Gets the number of Subscribers for which any updates are on behalf of.
     * In other words, is this class serving one or more subscribers? How many?
     *
     * @return int
     */
    public function getSubscriberCount()
    {
        return $this->_subscriberCount;
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
     * Sets a newly received feed (Atom/RSS) sent by a Hub as an update to a
     * Topic we've subscribed to.
     *
     * @param string $feed
     */
    public function setFeedUpdateObject(Zend_Feed_Reader_FeedAbstract $feed)
    {
        $this->_feedObject = $feed;
    }

    /**
     * Gets a newly received feed (Atom/RSS) sent by a Hub as an update to a
     * Topic we've subscribed to.
     *
     * @return Zend_Feed_Reader_FeedAbstract
     */
    public function getFeedUpdateObject()
    {
        return $this->_feedObject;
    }

    protected function _hasValidVerifyToken(array $httpGetData = null, $checkValue = true)
    {
        $verifyTokenKey = $this->_detectVerifyTokenKey();
        if (empty($verifyTokenKey)) {
            return false;
        }
        $verifyTokenExists = $this->getStorage()->hasVerifyToken($verifyTokenKey);
        if (!$verifyTokenExists) {
            return false;
        }
        if ($checkValue) {
            $verifyToken = $this->getStorage()->getVerifyToken($verifyTokenKey);
            if ($verifyToken !== hash('sha256', $httpGetData['hub.verify_token'])) {
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
        $callbackUrl = $this->_detectCallbackUrl();
        $path = parse_url($callbackUrl, PHP_URL_PATH);
        $parts = explode('/', $path);
        $tokenKey = urldecode(ltrim(array_pop($parts), '/\\'));
        return $tokenKey;
    }

    /**
     * Attempt to detect the callback URL (specifically the path forward)
     */
    protected function _detectCallbackUrl()
    {
        $callbackUrl = '';
        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
            $callbackUrl = $_SERVER['HTTP_X_REWRITE_URL'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $callbackUrl = $_SERVER['REQUEST_URI'];
            $scheme = 'http';
            if ($_SERVER['HTTPS'] == 'on') {
                $scheme = 'https';
            }
            $schemeAndHttpHost = $scheme . '://' . $this->_getHttpHost();
            if (strpos($callbackUrl, $schemeAndHttpHost) === 0) {
                $callbackUrl = substr($callbackUrl, strlen($schemeAndHttpHost));
            }
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
            $callbackUrl= $_SERVER['ORIG_PATH_INFO'];
            if (!empty($_SERVER['QUERY_STRING'])) {
                $callbackUrl .= '?' . $_SERVER['QUERY_STRING'];
            }
        }
        return $callbackUrl;
    }

    /**
     * Get the HTTP host
     *
     * @return string
     */
    public function _getHttpHost()
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        }
        $scheme = 'http';
        if ($_SERVER['HTTPS'] == 'on') {
            $scheme = 'https';
        }
        $name = $_SERVER['SERVER_NAME'];
        $port = $_SERVER['SERVER_PORT'];
        if (($scheme == 'http' && $port == 80)
        || ($scheme == 'https' && $port == 443)) {
            return $name;
        } else {
            return $name . ':' . $port;
        }
    }

    /**
     * Retrieve a Header value from either $_SERVER or Apache
     *
     * @param string $header
     */
    protected function _getHeader($header)
    {
        $temp = strtoupper(str_replace('-', '_', $header));
        if (!empty($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (!empty($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (!empty($headers[$header])) {
                return $headers[$header];
            }
        }
        return false;
    }

    /**
     * Return the raw body of the request
     *
     * @return string|false Raw body, or false if not present
     */
    protected function _getRawBody()
    {
        $body = file_get_contents('php://input');
        if (strlen(trim($body)) == 0 && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $body = $GLOBALS['HTTP_RAW_POST_DATA'];
        }
        if (strlen(trim($body)) > 0) {
            return $body;
        }
        return false;
    }

}
