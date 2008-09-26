<?php

require_once 'Zend/Feed/Reader/Feed/Abstract.php';

/**
 * Interpretive class for Zend_Feed which interprets incoming
 * Zend_Feed_Abstract objects and presents a common unified API for all RSS
 * and Atom versions.
 *
 * @copyright 2007-2008 Pádraic Brady (http://blog.astrumfutura.com)
 */
class Zend_Feed_Reader_Feed_Dc extends Zend_Feed_Reader_Feed_Abstract
{

    protected $_xpathPrefix = '';

    public function setXpathPrefix($prefix) 
    {
        $this->_xpathPrefix = $prefix;
    }

    public function getXpathPrefix() 
    {
        return $this->_xpathPrefix;
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
        foreach ($list as $authorObj) {
            $authors[] = $authorObj->nodeValue;
        }
        if (!empty($authors)) {
            $authors = array_unique($authors);
        }
        $this->_data['authors'] = $authors;
        return $this->_data['authors'];
    }

    public function getCopyright()
    {
        if (isset($this->_data['copyright'])) {
            return $this->_data['copyright'];
        }
        $copyright = null;
        $copyright = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/dc11:rights)');
        if (!$copyright) {
            $copyright = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/dc10:rights)');
        }
        if (!$copyright) {
            $copyright = null;
        }
        $this->_data['copyright'] = $copyright;
        return $this->_data['copyright'];
    }

    public function getDescription()
    {
        if (isset($this->_data['description'])) {
            return $this->_data['description'];
        }
        $description = null;
        $description = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/dc11:description)');
        if (!$description) {
            $description = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/dc10:description)');
        }
        if (!$description) {
            $description = null;
        }
        $this->_data['description'] = $description;
        return $this->_data['description'];
    }

    public function getId()
    {
        if (isset($this->_data['id'])) {
            return $this->_data['id'];
        }
        $id = null;
        $id = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/dc11:identifier)');
        if (!$id) {
            $id = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/dc10:identifier)');
        }
        $this->_data['id'] = $id;
        return $this->_data['id'];
    }

    public function getLanguage()
    {
        if (isset($this->_data['language'])) {
            return $this->_data['language'];
        }
        $language = null;
        $language = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/dc11:language)');
        if (!$language) {
            $language = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/dc10:language)');
        }
        if (!$language) {
            $language = null;
        }
        $this->_data['language'] = $language;
        return $this->_data['language'];
    }

    public function getTitle()
    {
        if (isset($this->_data['title'])) {
            return $this->_data['title'];
        }
        $title = null;
        $title = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/dc11:title)');
        if (!$title) {
            $title = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/dc10:title)');
        }
        if (!$title) {
            $title = null;
        }
        $this->_data['title'] = $title;
        return $this->_data['title'];
    }

    protected function _registerDefaultNamespaces()
    {
        $this->_xpath->registerNamespace('dc10', Zend_Feed_Reader::NAMESPACE_DC_10);
        $this->_xpath->registerNamespace('dc11', Zend_Feed_Reader::NAMESPACE_DC_11);
        /**
         * We require two switches, one for RSS and one for Atom, since technically
         * these are mutually exclusive and may coexist in the same feed with the
         * Dublin Core extension. Also technically this is placeholder code since
         * Atom/RSS merging is not supported...yet. Consider it an FYI to resolve ;).
         */
        /**
         * Duplicates Atom/RSS _registerDefaultNamespaces() methods...
         */
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
        switch ($this->_data['type']) {
            case Zend_Feed_Reader::TYPE_ATOM_10:
                $this->_xpath->registerNamespace('atom', Zend_Feed_Reader::NAMESPACE_ATOM_10);
                break;
            case Zend_Feed_Reader::TYPE_ATOM_03:
                $this->_xpath->registerNamespace('atom', Zend_Feed_Reader::NAMESPACE_ATOM_03);
                break;
        }
    }

    protected function _indexEntries()
    {
        // required for Abstract adherence but does nothing here
    }

}