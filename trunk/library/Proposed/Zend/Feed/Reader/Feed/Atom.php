<?php

require_once 'Zend/Feed/Reader/Feed.php';

/**
 * Interpretive class for Zend_Feed which interprets incoming
 * Zend_Feed_Abstract objects and presents a common unified API for all RSS
 * and Atom versions.
 *
 * @copyright 2007-2008 Pádraic Brady (http://blog.astrumfutura.com)
 */
class Zend_Feed_Reader_Feed_Atom extends Zend_Feed_Reader_Feed
{
    public function getAuthors()
    {
        if (isset($this->_data['authors'])) {
            return $this->_data['authors'];
        }
        /**
         * TODO: The author elements contains or can contain a name, uri and email address.
         * These attributes should either be split up, or be ignored.
         */
        $authors = $this->_xpath->evaluate('string(/feed/author)');
        $contributors = $this->_xpath->evaluate('string(/feed/contributor)');
        
        $people = array();
        
        if ($authors->length) {
            foreach ($authors as $author) {
                $people[] = $author->nodeValue;
            }
        }
        
        if ($contributors->length) {
            foreach ($contributors as $contributor) {
                $people[] = $contributor->nodeValue;
            }
        }
        
        if (!empty($people)) {
            $people = array_unique($people);
        }
        
        $this->_data['authors'] = $people;
        return $this->_data['authors'];
    }
    
    public function getAuthor($index = 0)
    {
        $authors = $this->getAuthors();
        if (isset($authors[$index])) {
            return $authors[$index];
        }
        return null;
    }
    
    public function getCopyright()
    {
        if (isset($this->_data['copyright'])) {
            return $this->_data['copyright'];
        }
        
        $copyright = null;
        
        if ($this->getType() === Zend_Feed_Reader::TYPE_ATOM_03) {
            $copyright = $this->_xpath->evaluate('string(/feed/copyright)');
        } else {
            $copyright = $this->_xpath->evaluate('string(/feed/rights)');
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
        
        if ($this->getType() === Zend_Feed_Reader::TYPE_ATOM_03) {
            $description = $this->_xpath->evaluate('string(/feed/tagline)'); // TODO: Is this the same as subtitle?
        } else {
            $description = $this->_xpath->evaluate('string(/feed/subtitle)');
        }
        
        if (!$description) {
            $description = null;
        }
        
        $this->_data['description'] = $description;
        return $this->_data['description'];
    }
    
    public function getLanguage()
    {
        if (isset($this->_data['language'])) {
            return $this->_data['language'];
        }
        
        $language = $this->_xpath->evaluate('string(/feed/lang)');
        
        if (!$language) {
            $language = null;
        }
        
        $this->_data['language'] = $language;
        return $this->_data['language'];
    }
    
    public function getLink()
    {
        if (isset($this->_data['link'])) {
            return $this->_data['link'];
        }
        
        $link = $this->_xpath->evaluate('string(/feed/link[@href])');
        
        if (!$link) {
            $link = null;
        }
        
        $this->_data['link'] = $link;
        return $this->_data['link'];
    }

    public function getTitle()
    {
        if (isset($this->_data['title'])) {
            return $this->_data['title'];
        }
        
        $title = $this->_xpath->evaluate('string(/feed/title)');
        
        if (!$title) {
            $title = null;
        }
        
        $this->_data['title'] = $title;
        return $this->_data['title'];
    }
    
    public function getUpdated()
    {
        if (isset($this->_data['updated'])) {
            return $this->_data['updated'];
        }
        
        $updated = null;
        
        if ($this->getType() === Zend_Feed_Reader::TYPE_ATOM_03) {
            $updated = $this->_xpath->evaluate('string(/feed/modified)');
        } else {
            $updated = $this->_xpath->evaluate('string(/feed/updated)');
        }
        
        if (!$updated) {
            $updated = null;
        }
        
        $this->_data['updated'] = $updated;
        return $this->_data['updated'];
    }

    protected function _registerDefaultNamespaces()
    {
        switch ($this->_data['type']) {
            case Zend_Feed_Reader::TYPE_ATOM_10:
                $this->_xpath->registerNamespace('atom', Zend_Feed_Reader::NAMESPACE_ATOM_10);
                break;
            case Zend_Feed_Reader::TYPE_ATOM_03:
                $this->_xpath->registerNamespace('atom', Zend_Feed_Reader::NAMESPACE_ATOM_03);
                break;
        }
        $this->_xpath->registerNamespace('dc10', Zend_Feed_Reader::NAMESPACE_DC_10);
        $this->_xpath->registerNamespace('dc11', Zend_Feed_Reader::NAMESPACE_DC_11);
    }
    
    protected function _indexEntries()
    {
        $entries = array();
        $entries = $this->_xpath->evaluate('//entry');
        
        foreach($entries as $index=>$entry) {
            $this->_entries[$index] = $entry;
        }
    }
}