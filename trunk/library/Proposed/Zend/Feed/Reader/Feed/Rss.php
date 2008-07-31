<?php

require_once 'Zend/Feed/Reader/Feed.php';

/**
 * Interpretive class for Zend_Feed which interprets incoming
 * Zend_Feed_Abstract objects and presents a common unified API for all RSS
 * and Atom versions.
 *
 * @copyright 2007-2008 Pádraic Brady (http://blog.astrumfutura.com)
 */
class Zend_Feed_Reader_Feed_Rss extends Zend_Feed_Reader_Feed
{

    public function getContent()
    {
    }

    public function getTitle()
    {
        if (isset($this->_data['title'])) {
            return $this->_data['title'];
        }
        if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10 && $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090) {
            $title = $this->_xpath->evaluate('string(/rss/channel/title)');
        } else {
            $title = $this->_xpath->evaluate('string(/rdf:RDF/rss:channel/rss:title)');
        }
        if (!$title) {
            $this->_xpath->evaluate('string(/rss/channel/dc:title)')
        }
        $this->_data['title'] = $title;
        return $this->_data['title'];
    }

    protected function _registerDefaultNamespaces()
    {
        switch ($this->_data['type']) {
            case Zend_Feed_Reader::TYPE_RSS_20:
                break;
            case Zend_Feed_Reader::TYPE_RSS_10:
                $this->_xpath->registerNamespace('rdf', Zend_Feed_Reader::NAMESPACE_RDF);
                $this->_xpath->registerNamespace('rss', Zend_Feed_Reader::NAMESPACE_RSS_10);
                break;
            case Zend_Feed_Reader::TYPE_RSS_090:
                $this->_xpath->registerNamespace('rdf', Zend_Feed_Reader::NAMESPACE_RDF);
                $this->_xpath->registerNamespace('rss', Zend_Feed_Reader::NAMESPACE_RSS_090);
                break;
            case Zend_Feed_Reader::TYPE_RSS_094:
                $this->_xpath->registerNamespace('rss', Zend_Feed_Reader::NAMESPACE_RSS_094);
                break;
            case Zend_Feed_Reader::TYPE_RSS_093:
                $this->_xpath->registerNamespace('rss', Zend_Feed_Reader::NAMESPACE_RSS_093);
                break;
            case Zend_Feed_Reader::TYPE_RSS_092:
                $this->_xpath->registerNamespace('rss', Zend_Feed_Reader::NAMESPACE_RSS_092);
                break;
            case Zend_Feed_Reader::TYPE_RSS_091:
                $this->_xpath->registerNamespace('rss', Zend_Feed_Reader::NAMESPACE_RSS_091);
                break;
        }
    }

}