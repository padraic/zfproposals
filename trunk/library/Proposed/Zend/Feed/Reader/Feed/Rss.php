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

    public function getTitle()
    {
        if (isset($this->_data['title'])) {
            return $this->_data['title'];
        }
        if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10 && $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090) {
            $title = $this->_xpath->evaluate('string(/rss/channel/title)');
            if (!$title) {
                $title = $this->_xpath->evaluate('string(/rss/channel/dc11:title)');
            }
            if (!$title) {
                $title = $this->_xpath->evaluate('string(/rss/channel/dc10:title)');
            }
        } else {
            $title = $this->_xpath->evaluate('string(/rdf:RDF/rss:channel/rss:title)');
            if (!$title) {
                $title = $this->_xpath->evaluate('string(/rdf:RDF/rss:channel/dc11:title)');
            }
            if (!$title) {
                $title = $this->_xpath->evaluate('string(/rdf:RDF/rss:channel/dc10:title)');
            }
        }
        if (!$title) {
            $title = null;
        }
        $this->_data['title'] = $title;
        return $this->_data['title'];
    }

    public function getAuthors() 
    {
        if (isset($this->_data['authors'])) {
            return $this->_data['authors'];
        }
        $authors = array();
        $list = $this->_xpath->query('//dc11:creator');
        if (!$list->length) {
            $list = $this->_xpath->query('//dc10:creator');
        }
        if (!$list->length) {
            if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10 && $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090) {
                $list = $this->_xpath->query('//author');
            } else {
                $list = $this->_xpath->query('//rss:author');
            }
        }
        foreach ($list as $authorObj) {
            $authors[] = $authorObj->nodeValue;
        }
        if (empty($authors)) {
            $authors = null;
        }
        $this->_data['authors'] = $authors;
        return $this->_data['authors'];
    }

    public function getAuthor() 
    {
        $authors = $this->getAuthors();
        return $authors[0];
    }

    protected function _registerDefaultNamespaces()
    {
        switch ($this->_data['type']) {
            case Zend_Feed_Reader::TYPE_RSS_10:
                $this->_xpath->registerNamespace('rdf', Zend_Feed_Reader::NAMESPACE_RDF);
                $this->_xpath->registerNamespace('rss', Zend_Feed_Reader::NAMESPACE_RSS_10);
                break;
            case Zend_Feed_Reader::TYPE_RSS_090:
                $this->_xpath->registerNamespace('rdf', Zend_Feed_Reader::NAMESPACE_RDF);
                $this->_xpath->registerNamespace('rss', Zend_Feed_Reader::NAMESPACE_RSS_090);
                break;
        }
        $this->_xpath->registerNamespace('dc10', Zend_Feed_Reader::NAMESPACE_DC_10);
        $this->_xpath->registerNamespace('dc11', Zend_Feed_Reader::NAMESPACE_DC_11);
    }

}