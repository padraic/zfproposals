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
    const NAMESPACE_RSS_20 = ''; // RSS 2.0 has no default namespace
    const NAMESPACE_RDF = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
    const NAMESPACE_ATOM_03 = 'http://purl.org/atom/ns#';
    const NAMESPACE_ATOM_10 = 'http://www.w3.org/2005/Atom';

}