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
class Zend_Pubsubhubbub_Storage_Filesystem implements Zend_Pubsubhubbub_StorageInterface
{

    /**
     * The directory to which values will be stored. If left unset, will attempt
     * to detect and use a valid writable temporary directory.
     *
     * @var string
     */
    protected $_directory = null;

    /**
     * Set the directory to which values will be stored.
     *
     * @param string $directory
     */
    public function setDirectory()
    {
        if (!file_exists($directory) || !is_writable($directory)) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('The directory "'
            . $directory . '" is not writable or does not exist and therefore'
            . ' cannot be used');
        }
        $this->_directory = rtrim($directory, '/\\');
    }

    /**
     * Get the directory to which values will be stored.
     *
     * @return string
     */
    public function getDirectory()
    {
        if ($this->_directory === null) {
            $this->_directory = $this->_getTempDirectory();
        }
        return $this->_directory;
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
    public function store($data, $hubUrl, $topicUrl, $type)
    {
        if (empty($data) || !is_string($data)) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid parameter "data"'
                .' of "' . $data . '" must be a non-empty string');
        }
        if (empty($hubUrl) || !is_string($hubUrl) || !Zend_Uri::check($hubUrl)) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid parameter "url"'
                .' of "' . $hubUrl . '" must be a non-empty string and a valid'
                .'URL');
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
        $filename = $this->_getFilename($hubUrl, $topicUrl, $type);
        $path = $this->getDirectory() . '/' . $filename;
        file_put_contents($path, $data);
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
    public function get($hubUrl, $topicUrl, $type)
    {
        if (empty($hubUrl) || !is_string($hubUrl) || !Zend_Uri::check($hubUrl)) {
            require_once 'Zend/Pubsubhubbub/Exception.php';
            throw new Zend_Pubsubhubbub_Exception('Invalid parameter "url"'
                .' of "' . $hubUrl . '" must be a non-empty string and a valid'
                .'URL');
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
        $filename = $this->_getFilename($hubUrl, $topicUrl, $type);
        $path = $this->getDirectory() . '/' . $filename;
        if (!file_exists($path) || !is_readable($path)) {
            return false;
        }
        return file_get_contents($path);
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
     * Based on parameters, generate a valid one-way hashed filename for a
     * store entry
     *
     * @param string $hubUrl The Hub Server URL
     * @param string $topicUrl The Topic (RSS or Atom feed) URL
     * @param string $type
     * @return string
     */
    protected function _getFilename($hubUrl, $topicUrl, $type)
    {
        return preg_replace(array("/+/", "/\//", "/=/"),
            array('_', '.', ''), base64_encode(sha1($hubUrl . $topicUrl . $type)));
    }

    /**
     * Detect and return the path to a writable temporary directory.
     * Harder than it looks!
     *
     * @see Zend_File_Transfer_Adapter_Abstract for the original impl.
     *
     * @return string
     * @throws Zend_Pubsubhubbub_Exception if unable to determine directory
     */
    protected function _getTempDirectory()
    {
        $tmpdir = array();
        foreach (array($_ENV, $_SERVER) as $tab) {
            foreach (array('TMPDIR', 'TEMP', 'TMP', 'windir', 'SystemRoot') as $key) {
                if (isset($tab[$key])) {
                    if (($key == 'windir') or ($key == 'SystemRoot')) {
                        $dir = realpath($tab[$key] . '\\temp');
                    } else {
                        $dir = realpath($tab[$key]);
                    }
                    if ($this->_isGoodTmpDir($dir)) {
                        return $dir;
                    }
                }
            }
        }
        if (function_exists('sys_get_temp_dir')) {
            $dir = sys_get_temp_dir();
            if ($this->_isGoodTmpDir($dir)) {
        	    return $dir;
            }
        }
        $tempFile = tempnam(md5(uniqid(rand(), TRUE)), '');
        if ($tempFile) {
            $dir = realpath(dirname($tempFile));
            unlink($tempFile);
            if ($this->_isGoodTmpDir($dir)) {
                return $dir;
            }
        }
        if ($this->_isGoodTmpDir('/tmp')) {
            return '/tmp';
        }
        if ($this->_isGoodTmpDir('\\temp')) {
            return '\\temp';
        }
        require_once 'Zend/Pubsubhubbub/Exception.php';
        throw new Zend_Pubsubhubbub_Exception('Could not determine temp'
        . ' directory, please specify a cache_dir manually');
    }

    /**
     * Verify if the given temporary directory is readable and writable
     *
     * @param $dir temporary directory
     * @return boolean true if the directory is ok
     */
    protected function _isGoodTmpDir($dir)
    {
        if (is_readable($dir) && is_writable($dir)) {
            return true;
        }
    	return false;
    }

}
