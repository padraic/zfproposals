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
     * Handle any callback from a Hub Server responding to a subscription or
     * unsubscription request. This should be the Hub Server confirming the
     * the request prior to taking action on it.
     *
     */
    public function handle(array $httpGetData = null)
    {
        if ($httpGetData === null) {
            $httpGetData = $_GET;
        }
        if (!$this->isValid($httpGetData)) {
            // 404!
            return;
        }
        // 2xx with hub.challenge attached!
    }

    /**
     * Checks validity of the request simply by making a quick pass and
     * confirming the presence of all REQUIRED parameters.
     *
     * @param array $httpGetData
     * @return bool
     */
    public function isValid(array $httpGetData = null)
    {
        if ($httpGetData === null) {
            $httpGetData = $_GET;
        }
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
        if ($httpGetData['hub.mode'] == 'subscribe') {
            if (!array_key_exists('hub.lease_seconds', $httpGetData)) {
                return false;
            }
        }
        if ($httpGetData['hub.mode'] !== 'subscribe'
        && $httpGetData['hub.mode'] !== 'unsubscribe') {
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



}
