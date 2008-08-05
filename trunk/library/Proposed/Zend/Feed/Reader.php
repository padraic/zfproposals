<?php

require_once 'Zend/Feed.php';

/**
 * Interpretive class for Zend_Feed which interprets incoming
 * Zend_Feed_Abstract objects and presents a common unified API for all RSS
 * and Atom versions.
 *
 * There's an obvious inclination to duplicate certain functionality from
 * Zend_Feed since Zend_Feed_Reader presents an abstract API where
 * possible using proxy methods.
 *
 * @copyright 2007-2008 Pádraic Brady (http://blog.astrumfutura.com)
 */
class Zend_Feed_Reader
{

    const TYPE_RSS_090 = 'rss-090';
    const TYPE_RSS_091 = 'rss-091';
    const TYPE_RSS_091_NETSCAPE = 'rss-091n';
    const TYPE_RSS_091_USERLAND = 'rss-091u';
    const TYPE_RSS_092 = 'rss-092';
    const TYPE_RSS_093 = 'rss-093';
    const TYPE_RSS_094 = 'rss-094';
    const TYPE_RSS_10 = 'rss-10';
    const TYPE_RSS_20 = 'rss-20';
    const TYPE_RSS_ANY = 'rss';
    const TYPE_ATOM_03 = 'atom-03';
    const TYPE_ATOM_10 = 'atom-10';
    const TYPE_ATOM_ANY = 'atom';
    const TYPE_ANY = 'any';

    const NAMESPACE_RSS_090 = 'http://my.netscape.com/rdf/simple/0.9/';
    const NAMESPACE_RSS_10 = 'http://purl.org/rss/1.0/';
    const NAMESPACE_RDF = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
    const NAMESPACE_ATOM_03 = 'http://purl.org/atom/ns#';
    const NAMESPACE_ATOM_10 = 'http://www.w3.org/2005/Atom';

    const NAMESPACE_DC_10 = 'http://purl.org/dc/elements/1.0/';
    const NAMESPACE_DC_11 = 'http://purl.org/dc/elements/1.1/';
    const NAMESPACE_CONTENT_10 = 'http://purl.org/rss/1.0/modules/content/';
    const NAMESPACE_SLASH_10 = 'http://purl.org/rss/1.0/modules/slash/';
    const NAMESPACE_WFWCOMMENTAPI = 'http://wellformedweb.org/CommentAPI/';
    const NAMESPACE_GEO = 'http://www.w3.org/2003/01/geo/wgs84_pos#';
    const NAMESPACE_YAHOOWEATHER_10 = 'http://xml.weather.yahoo.com/ns/rss/1.0';
    const NAMESPACE_ITUNES_10 = 'http://www.itunes.com/dtds/podcast-1.0.dtd';

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
        if (substr($type, 0, 3) == 'rss') {
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

}