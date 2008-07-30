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
class Zend_Feed_Reader_Feed extends Zend_Feed_Reader
{

    protected $_feed = null;

    protected $_data = array();

    protected $_domDocument = null;

    public function __construct(Zend_Feed_Abstract $feed, $type = null) 
    {
        $this->_feed = $feed;
        $this->_domDocument = $feed->getDOM()->ownerDocument;
        if (!is_null($type)) {
            $this->_data['type'] = $type;
        } else {
            $this->_data['type'] = self::detectType($feed);
        }
    }

    public static function import($url) 
    {
        $feed = Zend_Feed::import($url);
        return self::importFeed($feed);
    }

    public static function importString($string) 
    {
        $feed = Zend_Feed::importString($string);
        return self::importFeed($feed);
    }

    public static function importFeed(Zend_Feed_Abstract $feed) 
    {
        $type = self::detectType($feed);
        if (substr($feed, 3) == 'rss') {
            require_once 'Zend/Feed/Reader/Feed/Rss.php';
            $reader = new Zend_Feed_Reader_Feed_Rss($feed, $type);
        } else {
            require_once 'Zend/Feed/Reader/Feed/Atom.php';
            $reader = new Zend_Feed_Reader_Feed_Atom($feed, $type);
        }
        return $reader;
    }

    public static function detectType(Zend_Feed_Abstract $feed)
    {
        $xpath = new DOMXPath($feed->getDOM()->ownerDocument);
        if ($xpath->query('/rss')->length) {
            $type = self::TYPE_RSS_ANY;
            $version = $xpath->evaluate('string(/rss/@version)');
            if (strlen($version) > 0) {
                switch($version) {
                    case '2.0':
                        $type = self::TYPE_RSS_20;
                        break;
                    case '0.94':
                        $type = self::TYPE_RSS_094;
                        break;
                    case '0.93':
                        $type = self::TYPE_RSS_093;
                        break;
                    case '0.92':
                        $type = self::TYPE_RSS_092;
                        break;
                    case '0.91':
                        $type = self::TYPE_RSS_091;
                        break;
                }
            }
            return $type;
        }
        $xpath->registerNamespace('rdf', self::NAMESPACE_RDF);
        if ($xpath->query('/rdf:RDF')->length) {
            $xpath->registerNamespace('rss', self::NAMESPACE_RSS_10);
            if ($xpath->query('/rdf:RDF/rss:channel')->length
                || $xpath->query('/rdf:RDF/rss:image')->length
                || $xpath->query('/rdf:RDF/rss:item')->length
                || $xpath->query('/rdf:RDF/rss:textinput')->length) {
                return self::TYPE_RSS_10;
            }
            $xpath->registerNamespace('rss', self::NAMESPACE_RSS_090);
            if ($xpath->query('/rdf:RDF/rss:channel')->length
                || $xpath->query('/rdf:RDF/rss:image')->length
                || $xpath->query('/rdf:RDF/rss:item')->length
                || $xpath->query('/rdf:RDF/rss:textinput')->length) {
                return self::TYPE_RSS_090;
            }
        }
        $type = self::TYPE_ATOM_ANY;
        $xpath->registerNamespace('atom', self::NAMESPACE_ATOM_10);
        if ($xpath->query('//atom:feed')->length) {
            return self::TYPE_ATOM_10;
        }
        $xpath->registerNamespace('atom', self::NAMESPACE_ATOM_03);
        if ($xpath->query('//atom:feed')->length) {
            return self::TYPE_ATOM_03;
        }
        return self::TYPE_ANY;
    }

    public function count() 
    {
        return $this->_feed->count();
    }

    public function rewind() 
    {
        $this->_feed->rewind();
    }

    public function current() 
    {
        $item = $this->_feed->current();
        // get entry reader when ready
        return $entry;
    }

    public function key() 
    {
        return $this->_feed->key();
    }

    public function next() 
    {
        $this->_feed->next();
    }

}