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
        $entries = $this->_xpath->evaluate('//entry');
        
        foreach($entries as $index=>$entry) {
            $this->_entries[$index] = $entry;
        }
    }

}