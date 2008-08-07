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
        /**
         * TODO: Add authors support.
         * Atom seems to store author info both in the "common" section and in
         * the entries.
         */
    }
    
    public function getAuthor()
    {
        
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
        
        $link = $this->_xpath->evaluate('string(/feed/link)');
        
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