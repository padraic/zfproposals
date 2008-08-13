<?php

require_once 'Zend/Feed/Reader.php';

/**
 * Interpretive class for Zend_Feed which interprets incoming
 * Zend_Feed_Abstract objects and presents a common unified API for all RSS
 * and Atom versions.
 * Or will...when it's been completed ;).
 *
 * @copyright 2007-2008 Pádraic Brady (http://blog.astrumfutura.com)
 */
abstract class Zend_Feed_Reader_Feed_Abstract
{
    /**
     * Enter description here...
     *
     * @var Zend_Feed_Abstract
     */
    protected $_feed = null;

    /**
     * Enter description here...
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Enter description here...
     *
     * @var DOMDocument
     */
    protected $_domDocument = null;

    /**
     * Enter description here...
     *
     * @var DOMXPath
     */
    protected $_xpath = null;

    /**
     * Enter description here...
     *
     * @var array
     */
    protected $_entries = array();

    /**
     * Enter description here...
     *
     * @var int
     */
    protected $_entriesKey = 0;

    /**
     * Enter description here...
     *
     * @param Zend_Feed_Abstract $feed
     * @param string $type
     */
    public function __construct(Zend_Feed_Abstract $feed, $type = null)
    {
        $this->_feed = $feed;
        $this->_domDocument = $feed->getDOM()->ownerDocument;
        $this->_xpath = new DOMXPath($this->_domDocument);
        if (!is_null($type)) {
            $this->_data['type'] = $type;
        } else {
            $this->_data['type'] = Zend_Feed_Reader::detectType($feed);
        }
        $this->_registerDefaultNamespaces();
        $this->_indexEntries();
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getType()
    {
        return $this->_data['type'];
    }

    /**
     * Enter description here...
     *
     * @return int
     */
    public function count()
    {
        return $this->_feed->count();
    }

    /**
     * Enter description here...
     *
     */
    public function rewind()
    {
        $this->_feed->rewind();
    }

    /**
     * Enter description here...
     *
     * @return Zend_Feed_Reader_Entry_Interface
     */
    public function current()
    {
        if (substr($this->getType(), 0, 3) == 'rss') {
            require_once 'Zend/Feed/Reader/Entry/Rss.php';
            $reader = new Zend_Feed_Reader_Entry_Rss($this->_feed->current(), $this->_feed->key(), $this->getType());
        } else {
            require_once 'Zend/Feed/Reader/Entry/Atom.php';
            $reader = new Zend_Feed_Reader_Entry_Atom($this->_feed->current(), $this->_feed->key(), $this->getType());
        }
        $reader->setXpath($this->_xpath);
        return $reader;
    }

    /**
     * Enter description here...
     *
     * @return unknown
     */
    public function key()
    {
        return $this->_feed->key();
    }

    /**
     * Enter description here...
     *
     */
    public function next()
    {
        $this->_feed->next();
    }

    /**
     * Enter description here...
     *
     * @return array
     */
    public function toArray() // untested
    {
        return $this->_data;
    }
    
    /**
     * Enter description here...
     *
     */
    abstract protected function _registerDefaultNamespaces();

    /**
     * Enter description here...
     *
     */
    abstract protected function _indexEntries();
}