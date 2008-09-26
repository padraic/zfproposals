<?php

require_once 'Zend/Feed/Reader/Feed/Abstract.php';

require_once 'Zend/Feed/Reader/Feed/Interface.php';

require_once 'Zend/Feed/Reader/Feed/Atom.php';

require_once 'Zend/Feed/Reader/Feed/Dc.php';

/**
 * Interpretive class for Zend_Feed which interprets incoming
 * Zend_Feed_Abstract objects and presents a common unified API for all RSS
 * and Atom versions.
 *
 * @copyright 2007-2008 Pádraic Brady (http://blog.astrumfutura.com)
 */
class Zend_Feed_Reader_Feed_Rss extends Zend_Feed_Reader_Feed_Abstract implements Zend_Feed_Reader_Feed_Interface
{

    protected $_dc = null;

    protected $_atom = null;

    public function __construct(Zend_Feed_Abstract $feed, $type = null, DOMXPath $xpath = null) 
    {
        parent::__construct($feed, $type, $xpath);
        $this->_dc = new Zend_Feed_Reader_Feed_Dc($feed, $this->_data['type'], $xpath);
        $this->_atom = new Zend_Feed_Reader_Feed_Atom($feed, $this->_data['type'], $xpath);
        if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10 && $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090) {
            $this->_dc->setXpathPrefix('/rss/channel');
            $this->_atom->setXpathPrefix('/rss/channel');
        } else {
            $this->_dc->setXpathPrefix('/rdf:RDF/rss:channel');
            $this->_atom->setXpathPrefix('/rdf:RDF/rss:channel');
        }
    }

    public function getAuthors()
    {
        if (isset($this->_data['authors'])) {
            return $this->_data['authors'];
        }
        $authors = array();
        $authors = $this->_atom->getAuthors();
        if (empty($authors)) {
            $authors = $this->_dc->getAuthors();
        }
        if (empty($authors)) { // do we merge both, or leave as is?
            if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10 && $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090) {
                $list = $this->_xpath->query('//author');
            } else {
                $list = $this->_xpath->query('//rss:author');
            }
            foreach ($list as $authorObj) {
                $authors[] = $authorObj->nodeValue;
            }
        }
        if (empty($authors)) {
            $authors = null;
        } else {
            $authors = array_unique($authors);
        }
        $this->_data['authors'] = $authors;
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
        if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10 && $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090) {
            $copyright = $this->_xpath->evaluate('string(/rss/channel/copyright)');
        }
        if (!$copyright && !is_null($this->_dc)) {
            $copyright = $this->_dc->getCopyright();
        }
        if (!$copyright) {
            $copyright = null;
        }
        $this->_data['copyright'] = $copyright;
        return $this->_data['copyright'];
    }
    
    public function getDateModified()
    {
        // TODO: Implement this method
    }
    
    public function getDateCreated()
    {
        // TODO: Implement this method
    }

    public function getDescription()
    {
        if (isset($this->_data['description'])) {
            return $this->_data['description'];
        }
        $description = null;
        if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10 && $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090) {
            $description = $this->_xpath->evaluate('string(/rss/channel/description)');
        } else {
            $description = $this->_xpath->evaluate('string(/rdf:RDF/rss:channel/rss:description)');
        }
        if (!$description && !is_null($this->_dc)) {
            $description = $this->_dc->getDescription();
        }
        if (!$description) {
            $description = null;
        }
        $this->_data['description'] = $description;
        return $this->_data['description'];
    }
    
    public function getGenerator()
    {
        // TODO: Implement
    }
    
    public function getId()
    {
        if (isset($this->_data['id'])) {
            return $this->_data['id'];
        }
        $id = null;
        if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10 && $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090) {
            $id = $this->_xpath->evaluate('string(/rss/channel/guid)');
        }
        if (!$id && !is_null($this->_dc)) {
            $id = $this->_dc->getId();
        }
        if (!$id) {
            if ($this->getLink()) {
                $id = $this->getLink();
            } elseif ($this->getTitle()) {
                $id = $this->getTitle();
            } else {
                $id = null;
            }
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
        if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10 && $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090) {
            $language = $this->_xpath->evaluate('string(/rss/channel/language)');
        }
        if (!$language && !is_null($this->_dc)) {
            $language = $this->_dc->getLanguage();
        }
        if (!$language) {
            $language = $this->_xpath->evaluate('string(//@xml:lang[1])');
        }
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
        $link = null;
        if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10 && $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090) {
            $link = $this->_xpath->evaluate('string(/rss/channel/link)');
        } else {
            $link = $this->_xpath->evaluate('string(/rdf:RDF/rss:channel/rss:link)');
        }
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
        $title = null;
        if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10 && $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090) {
            $title = $this->_xpath->evaluate('string(/rss/channel/title)');
        } else {
            $title = $this->_xpath->evaluate('string(/rdf:RDF/rss:channel/rss:title)');
        }
        if (!$title && !is_null($this->_dc)) {
            $title = $this->_dc->getTitle();
        }
        if (!$title) {
            $title = null;
        }
        $this->_data['title'] = $title;
        return $this->_data['title'];
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
        $this->_xpath->registerNamespace('atom03', Zend_Feed_Reader::NAMESPACE_ATOM_03);
        $this->_xpath->registerNamespace('atom10', Zend_Feed_Reader::NAMESPACE_ATOM_10);
        $this->_xpath->registerNamespace('content', Zend_Feed_Reader::NAMESPACE_CONTENT_10);
    }

    protected function _indexEntries()
    {
        $entries = array();
        if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10 && $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090) {
            $entries = $this->_xpath->evaluate('//item');
        } else {
            $entries = $this->_xpath->evaluate('//rss:item');
        }
        foreach($entries as $index=>$entry) {
            $this->_entries[$index] = $entry;
        }
    }

}