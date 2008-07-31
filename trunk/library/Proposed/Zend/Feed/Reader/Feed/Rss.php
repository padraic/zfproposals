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

    public function getAuthor() 
    {
    }

    public function getAuthors() 
    {
    }

    public function getContributor() 
    {
    }

    public function getContributors() 
    {
    }

    public function getCopyright() 
    {
    }

    public function getDescription() 
    {
    }

    public function getEncoding() 
    {
    }

    public function getFavicon() 
    {
    }

    public function getLanguage() 
    {
    }

    public function getLink() 
    {
    }

    public function getLinks() 
    {
    }

    public function getPermalink() 
    {
    }

    public function getTitle() 
    {
        if (!empty($this->_data['title'])) {
            return $this->_data['title'];
        }
        $title = '';
    }

}