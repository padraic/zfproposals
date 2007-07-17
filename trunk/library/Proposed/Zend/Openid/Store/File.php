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
 * @subpackage Consumer
 * @copyright  Copyright (c) 2007 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Openid_Store_Interface */
require_once 'Zend/Openid/Store/Interface.php';

/**
 * File based storage solution for Association data. No effort was spared in
 * finding ways to be lazy here. This is good enough for small systems but
 * a database solution is much preferred since you can easily skip all the
 * duplication from matching aliases to real IP server addresses.
 *
 * @category   Zend
 * @package    Zend_Openid
 * @copyright  Copyright (c) 2007 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Openid_Store_File implements Zend_Openid_Store_Interface
{

    /**
     * Path of the file to use for caching association data
     *
     * @var string
     */
    protected $_path = null;

    /**
     * Maximimum cache opens before we decide some idiot beens sprinkling too
     * many aliases in our source code.
     *
     * @var int
     */
    protected $_maxOpens = 5;

    /**
     * Current maxOpens count
     *
     * @var int
     */
    protected static $_currentMaxOpens = 0;

    /**
     * Constructor; create File Store object for caching Associations to a temporary file.
     *
     * @param string $file
     * @return void
     */
    public function __construct($path)
    {
        $path = rtrim($path, '\\/');
        if (!file_exists($path) || !is_writable($path)) {
            require_once 'Zend/Openid/Store/Exception.php';
            throw new Zend_Openid_Store_Exception('the storage path does not exist or cannot be written to');
        }
        $this->_path = $path;
    }

    /**
     * Check for the existence of an Association for a Claimed Identifier
     *
     * @param string $uri
     * @return bool
     */
    public function hasAssociation($uri)
    {
        $key = md5($uri);
        $path = $this->_path($key);
        if (!$path) {
            return false;
        }
        $associationKV = $this->_open($path);
        if ($associationKV) {
            return true;
        }
        return false;
    }

    /**
     * Save an association data to cache file, and create an Alias for it
     * if one is presented.
     *
     * @param string $uri
     * @param string $data
     * @param string $alias
     * @return void
     */
    public function setAssociation($uri, $data, $alias = null)
    {
        if (isset($alias)) {
            $aliasKey = md5($alias);
            $aliasString = 'alias:' . $uri;
            $this->_put($aliasKey, $aliasString);
        }
        $key = md5($uri);
        $this->_put($key, $data);
    }

    /**
     * Fetch Association data for a given Claimed Identifier. Similar to
     * hasAssociation() returns false on failure.
     *
     * @param string $uri
     * @return string|bool
     */
    public function getAssociation($uri)
    {
        $key = md5($uri);
        $path = $this->_path($key);
        if (!$path) {
            return null;
        }
        $associationKV = $this->_open($path);
        if ($associationKV) {
            return $associationKV;
        }
        return null;
    }

    /**
     * Delete an association. This is imperfect since it only deletes the
     * association cache file and leaves files containing known Identity
     * Aliases intact.
     * Returns false if the deletion could not be performed.
     *
     * @param string $uri
     * @return void
     */
    public function deleteAssociation($uri)
    {
        $key = md5($uri);
        $path = $this->_path($key);
        if (!$path) {
            return false;
        }
        return $this->_delete($path);
    }

    /**
     * Return a viable path to a cache file or FALSE if invalid
     *
     * @param string $key
     * @return string|bool
     */
    protected function _path($key, $noCheck = false)
    {
        $path = $this->_path . DIRECTORY_SEPARATOR . $key;
        if ($noCheck === false && (!file_exists($path) || !is_readable($path))) {
            return false;
        }
        return $path;
    }

    /**
     * Opens a cache file, and recursively seeks the actual data attached
     * the final Identity Provider Key for any alias identity.
     *
     * @param string $key
     * @return string
     */
    protected function _open($path)
    {
        if (self::$_currentMaxOpens > 5) {
            require_once 'Zend/Openid/Store/Exception.php';
            throw new Zend_Openid_Store_Exception('unable to open cache; too many alias redirections');
        }
        if (!$path) {
            require_once 'Zend/Openid/Store/Exception.php';
            throw new Zend_Openid_Store_Exception('the association cache file does not exist or is not readable');
        }
        $cache = file_get_contents($path);
        if (strpos($cache, 'alias:') === 0) {
            $array = explode('alias:', $cache);
            $actual = $array[1];
            $path = $this->_path(md5($actual));
            self::$_currentMaxOpens++;
            return $this->_open($path);
        }
        return $cache;
    }

    /**
     * Delete an Association based on Alias/Actual URI. This does
     * not delete other Alias files, though these WILL return false
     * when called with hasAssociation().
     *
     * @param string $path
     * @return void
     */
    protected function _delete($path)
    {
        if (self::$_currentMaxOpens > 5) {
            require_once 'Zend/Openid/Store/Exception.php';
            throw new Zend_Openid_Store_Exception('unable to delete cache; too many alias redirections');
        }
        if (!is_writable($path)) {
            return false;
        }
        $cache = file_get_contents();
        if (strpos($cache, 'alias:') === 0) {
            $array = explode($cache, ':');
            $actual = $array[1];
            self::$_currentMaxOpens++;
            return $this->_delete($actual);
        }
        unlink($path);
    }

    /**
     * Put the data to cache into a file
     *
     * @param string $key
     * @param string $data
     * @return void
     */
    protected function _put($key, $data)
    {
        $path = $this->_path($key, true);
        if (!$path) {
            require_once 'Zend/Openid/Store/Exception.php';
            throw new Zend_Openid_Store_Exception('the path for storing association caches does not exist or is not writeable');
        }
        file_put_contents($path, $data, LOCK_EX);
    }

}