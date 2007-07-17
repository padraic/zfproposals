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
 * @version    $Id: Openid.php 8 2007-06-17 20:45:27Z padraic $
 */

/**
 * Container for translating data in the {Key}:{Value} format delimited by newlines.
 * This class doesn't appear to be needed outside of OpenID but can moved to a
 * more central location if ever required.
 *
 * @category   Zend
 * @package    Zend_Openid
 * @copyright  Copyright (c) 2007 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Openid_KVContainer implements Iterator
{

    /**
     * A simple array holding the KV key=>value pairings
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Iterator validity for self::$_data
     *
     * @var bool
     */
    protected $_valid = true;

    /**
     * Prefix attached to all keys in form {prefix}.{key}
     *
     * @var string
     */
    protected $_keyPrefix = null;

    /**
     * Constructor; create a container for parsing or generating strings which
     * implement a newline delimited Key:Value format.
     *
     * @param string $data
     * @param string $keyPrefix
     * @return void
     */
    public function __construct($data = null, $keyPrefix = null)
    {
        if (!is_null($keyPrefix)) {
            $this->setKeyPrefix($keyPrefix);
        }
        if (!is_null($data)) {
            if (is_array($data)) {
                $this->fromArray($data);
            } else {
                $this->fromString($data);
            }
        }
    }

    /**
     * Construct a KVContainer from a String input
     *
     * @param string $data
     * @return void
     */
    public function fromString($data)
    {
        if (isset($data) && empty($data)) {
            require_once 'Zend/Openid/Exception.php';
            throw new Zend_Openid_Exception('Invalid data; the data passed does not appear to contain anything');
        }
        $kv = $this->_parseString($data);
        if (!$kv) {
            require_once 'Zend/Openid/Exception.php';
            throw new Zend_Openid_Exception('Invalid data; the data passed does not appear to be in a valid Key:Value format where keys and values are separated by a colon, and each pair is delimited by a newline');
        }
    }

    /**
     * Magically convert KVContainer into it's KV string representation.
     *
     * @return string
     */
    public function __toString()
    {
        $string = '';
        foreach($this->_data as $key=>$value) {
            $string .= $key . ':' . (string) $value . PHP_EOL;
        }
        return $string;
    }

    /**
     * Proxy to __toString()
     *
     * @return string
     */
    public function toString()
    {
        return $this->__toString();
    }

    /**
     * Construct the container using an array of values.
     *
     * @param array $data
     * @return void
     */
    public function fromArray(array $data)
    {
        if (!isset($data) || empty($data)) {
            require_once 'Zend/Openid/Exception.php';
            throw new Zend_Openid_Exception('Invalid data; the data passed does not appear to contain anything');
        }
        $kv = $this->_parseArray($data);
        if (!$kv) {
            require_once 'Zend/Openid/Exception.php';
            throw new Zend_Openid_Exception('Invalid data; the data passed does not appear to be in a valid single-dimensional array');
        }
        $this->_data = $kv;
    }

    /**
     * Extract the array of key value pairs.
     *
     * @return array
     */
    public function toArray()
    {
        if (isset($this->_data)) {
            return $this->_data;
        }
        return null;
    }

    /**
     * Set a prefix that is attached to all keys in the data array
     * with a period delimiter. e.g. the openid prefix create keys
     * like "openid.ns". Prefixes are not required when referencing
     * values.
     *
     * @param string $prefix
     * @return void
     */
    public function setKeyPrefix($key)
    {
        $this->_keyPrefix = rtrim((string) $key, '.') . '.';
    }

    /**
     * Boolean return on whether next KV element is valid
     *
     * @return bool
     */
    public function valid()
    {
        return $this->_valid;
    }

    /**
     * Return current array element
     *
     * @return string
     */
    public function current()
    {
        return current($this->_data);
    }

    /**
     * Move array pointer forward by 1 and set Validity based on whether
     * this operation is successful
     *
     * @return bool
     */
    public function next()
    {
        $this->valid = (false !== next($this->_data));
    }

    /**
     * Return array pointer to the first element
     *
     * @return bool
     */
    public function rewind()
    {
        $this->_valid = (false !== reset($this->_data));
    }

    /**
     * Returns the key of the current array element
     *
     * @return bool
     */
    public function key()
    {
        return $this->_keyPrefix . key($this->_data);
    }

    /**
     * Setter for KV values
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    public function __set($key, $value)
    {
        if (strrpos($key, '.') === false && isset($this->_keyPrefix)) {
            $key = $this->_keyPrefix . $key;
        }
        $this->_data[$key] = (string) $value;
    }

    /**
     * Getter for KV values
     *
     * @param string $key
     * @return string|null
     */
    public function __get($key)
    {
        if (strrpos($key, '.') === false && isset($this->_keyPrefix)) {
            $key = $this->_keyPrefix . $key;
        }
        if (isset($this->_data[$key])) {
            return $this->_data[$key];
        }
        return null;
    }

    /**
     * Isset check for KV values
     *
     * @param string $key
     * @return bool
     */
    public function __isset($key)
    {
        if (strrpos($key, '.') === false && isset($this->_keyPrefix)) {
            $key = $this->_keyPrefix . $key;
        }
        return isset($this->_data[$key]);
    }

    /**
     * Unsetter for KV values
     *
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        if (strrpos($key, '.') === false && isset($this->_keyPrefix)) {
            $key = $this->_keyPrefix . $key;
        }
        if ($this->__isset($key)) {
            unset($this->_data[$key]);
        }
    }

    /**
     * Reset the KVContainer to a clean state
     *
     * @return void
     */
    public function reset()
    {
        $this->_data = array();
        $this->rewind();
    }

    /**
     * Parse the string KV formed data into an array
     *
     * @param string $data
     * @return array
     */
    protected function _parseString($data)
    {
        $kv = array();
        $pairs = explode("\n", $data);
        if (count($pairs) == 0) {
            return false;
        }
        foreach($pairs as $pair) {
            $pair = trim($pair);
            if (empty($pair)) {
                continue;
            }
            $pairSplit = explode(':', $pair, 2);
            if (!is_array($pairSplit) || count($pairSplit) !== 2) {
                return false;
            }
            $this->__set($pairSplit[0], $pairSplit[1]);
        }
        return true;
    }

    /**
     * Parse an array of values into KV formed data casting non-string data to
     * String
     *
     * @param array $data
     * @return array
     */
    protected function _parseArray($data)
    {
        $kv = array();
        if (count($data) == 0) {
            return false;
        }
        foreach($data as $key => $value) {
            if (is_array($value)) {
                return false;
            }
            $this->__set($key, (string) $value);
        }
        return true;
    }

}