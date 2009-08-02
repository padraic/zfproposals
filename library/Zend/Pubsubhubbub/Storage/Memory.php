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
 * NOTE: Mainly for testing; will add a other versions at some point using
 * Zend_Db and Zend_Cache. This is largely a simple keypair store anyway.
 * If stuck, use the interface to roll your own...
 */

/**
 * @see Zend_Pubsubhubbub_StorageInterface
 */
require_once 'Zend/Pubsubhubbub/StorageInterface.php';

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
class Zend_Pubsubhubbub_Storage_Memory implements Zend_Pubsubhubbub_StorageInterface
{

    /**
     * Constructor; checks that apc has been loaded
     */
    public function __construct()
    {
        if (!extension_loaded('apc')) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('The apc extension must be'
            . 'loaded to use this Storage medium');
        }
    }

    /**
     * Store data which is associated with the given Hub Server URL and Topic
     * URL and where that data relates to the given Type. The Types supported
     * include: "subscription", "unsubscription". These Type strings may also
     * be referenced by constants on the Zend_Pubsubhubbub class.
     *
     * @param string|integer $data
     * @param string $hubUrl The Hub Server URL
     * @param string $topicUrl The Topic (RSS or Atom feed) URL
     * @param string $type
     */
    public function store($data, $type, $topicUrl, $hubUrl = null)
    {
        if (empty($data) || !is_string($data)) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid parameter "data"'
                .' of "' . $data . '" must be a non-empty string');
        }
        if (empty($topicUrl) || !is_string($topicUrl) || !Zend_Uri::check($topicUrl)) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid parameter "url"'
                .' of "' . $topicUrl . '" must be a non-empty string and a valid'
                .'URL');
        }
        if (!in_array($type, array('subscription', 'unsubscription'))) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid parameter "type"'
                .' of "' . $type . '" must be a non-empty string and a valid'
                . ' type for storage');
        }
        if (!is_null($hubUrl) && (empty($hubUrl) || !is_string($hubUrl) || !Zend_Uri::check($hubUrl))) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid parameter "url"'
                .' of "' . $hubUrl . '" must be a non-empty string and a valid'
                .'URL');
        }
        $key = $this->_getKey($type, $topicUrl, $hubUrl);
        apc_store($key, $data);
    }

    /**
     * Get data which is associated with the given Hub Server URL and Topic
     * URL and where that data relates to the given Type. The Types supported
     * include: "subscription", "unsubscription". These Type strings may also
     * be referenced by constants on the Zend_Pubsubhubbub class.
     *
     * @param string $hubUrl The Hub Server URL
     * @param string $topicUrl The Topic (RSS or Atom feed) URL
     * @param string $type
     * @return string
     */
    public function get($type, $topicUrl, $hubUrl = null)
    {
        if (empty($topicUrl) || !is_string($topicUrl) || !Zend_Uri::check($topicUrl)) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid parameter "url"'
                .' of "' . $topicUrl . '" must be a non-empty string and a valid'
                .'URL');
        }
        if (!is_null($hubUrl) && (empty($hubUrl) || !is_string($hubUrl) || !Zend_Uri::check($hubUrl))) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid parameter "url"'
                .' of "' . $hubUrl . '" must be a non-empty string and a valid'
                .'URL');
        }
        if (!in_array($type, array('subscription', 'unsubscription'))) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid parameter "type"'
                .' of "' . $type . '" must be a non-empty string and a valid'
                . ' type for storage');
        }
        $key = $this->_getKey($type, $topicUrl, $hubUrl);
        return apc_fetch($key);
    }

    /**
     * Checks for the existence of a record agreeing with the given parameters
     *
     * @param string $hubUrl The Hub Server URL
     * @param string $topicUrl The Topic (RSS or Atom feed) URL
     * @param string $type
     * @return bool
     */
    public function exists($type, $topicUrl, $hubUrl = null)
    {
        if (empty($topicUrl) || !is_string($topicUrl) || !Zend_Uri::check($topicUrl)) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid parameter "url"'
                .' of "' . $topicUrl . '" must be a non-empty string and a valid'
                .'URL');
        }
        if (!is_null($hubUrl) && (empty($hubUrl) || !is_string($hubUrl) || !Zend_Uri::check($hubUrl))) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid parameter "url"'
                .' of "' . $hubUrl . '" must be a non-empty string and a valid'
                .'URL');
        }
        if (!in_array($type, array('subscription', 'unsubscription'))) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid parameter "type"'
                .' of "' . $type . '" must be a non-empty string and a valid'
                . ' type for storage');
        }
        $key = $this->_getKey($type, $topicUrl, $hubUrl);
        if (apc_fetch($key)) {
            return true;
        }
        return false;
    }

    /**
     * When/If implemented: deletes all records for any given valid Type
     *
     * @param string $type
     */
    public function cleanup($type)
    {
        require_once 'Zend/Pubsubhubbub/Exception.php';
        throw new Zend_Pubsubhubbub_Exception('Not Implemented');
    }

    /**
     * Based on parameters, generate a valid one-way hashed key for a
     * store entry
     *
     * @param string $hubUrl The Hub Server URL
     * @param string $topicUrl The Topic (RSS or Atom feed) URL
     * @param string $type
     * @return string
     */
    protected function _getKey($type, $topicUrl, $hubUrl = null)
    {
        if ($hubUrl === null) {
            $hubUrl = '';
        }
        return preg_replace(array("/+/", "/\//", "/=/"),
            array('_', '.', ''), base64_encode(sha1($type . $topicUrl . $hubUrl)));
    }

}
