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
 * @see Zend_Uri
 */
require_once 'Zend/Uri.php';

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
     * Handle any callback from a Hub Server responding to a subscription or
     * unsubscription request. This should be the Hub Server confirming the
     * the request prior to taking action on it.
     *
     */
    public function handle(array $httpGetData, $sendResponseNow = false)
    {
        if (!$this->isValid($httpGetData)) {
            $this->getHttpResponse()->setHttpResponseCode(404);
        } else {
            $this->getHttpResponse()->setBody($httpGetData['hub.challenge']);
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
    public function isValid(array $httpGetData)
    {
        /**
         * As per the specification, the hub.verify_token is OPTIONAL. This
         * implementation of Pubsubhubbub considers it REQUIRED and will
         * always send a hub.verify_token parameter to be echoed back
         * by the Hub Server. Therefore, its absence is considered invalid.
         */
        $required = array('hub.mode', 'hub.topic', 'hub.challenge', 'hub.verify_token');
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
        $verifyTokenExists = $this->getStorage()->exists(
            $httpGetData['hub.mode'],
            $httpGetData['hub.topic'],
            null,
            $httpGetData['hub.verify_token']
        );
        if (!$verifyTokenExists) {
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
        if (!$httpResponse instanceof Zend_Pubsubhubbub_HttpResponse
        && !$httpResponse instanceof Zend_Controller_Response_Http) {
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

}
