<?php

require_once 'Zend/Feed/Reader/Feed/Abstract.php';

require_once 'Zend/Feed/Reader/Author.php';

require_once 'Zend/Feed/Reader/Feed/Interface.php';

/**
 * Interpretive class for Zend_Feed which interprets incoming
 * Zend_Feed_Abstract objects and presents a common unified API for all RSS
 * and Atom versions.
 *
 * @copyright 2008 Jurriën Stutterheim
 */
class Zend_Feed_Reader_Feed_Atom extends Zend_Feed_Reader_Feed_Abstract implements Zend_Feed_Reader_Feed_Interface 
{

    protected $_xpathPrefix = '';

    public function __construct(Zend_Feed_Abstract $feed, $type = null, DOMXPath $xpath = null) 
    {
        parent::__construct($feed, $type, $xpath);
        $this->setXpathPrefix('/atom:feed');
    }

    public function setXpathPrefix($prefix) 
    {
        $this->_xpathPrefix = $prefix;
    }

    public function getXpathPrefix() 
    {
        return $this->_xpathPrefix;
    }

    /**
     * Enter description here...
     *
     * @return array
     */
    public function getAuthors()
    {
        if (isset($this->_data['authors'])) {
            return $this->_data['authors'];
        }
        
        $authors = $this->_xpath->query($this->getXpathPrefix() . '/atom:author');
        $contributors = $this->_xpath->query($this->getXpathPrefix() . '/atom:contributor');
        
        $people = array();
        
        if ($authors->length) {
            foreach ($authors as $author) {
                $people[] = $this->_getAuthor($author);
            }
        }
        
        if ($contributors->length) {
            foreach ($contributors as $contributor) {
                $people[] = $this->_getAuthor($contributor);
            }
        }

        $this->_data['authors'] = $people;
        return $this->_data['authors'];
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $index
     * @return Zend_Feed_Reader_Author
     */
    public function getAuthor($index = 0)
    {
        $authors = $this->getAuthors();
        if (isset($authors[$index])) {
            return $authors[$index];
        }
        return null;
    }
    
    /**
     * Enter description here...
     *
     * @param DOMElement $element
     * @return Zend_Feed_Reader_Author
     */
    protected function _getAuthor(DOMElement $element)
    {
        return new Zend_Feed_Reader_Author($element->getElementsByTagName('name')->item(0)->nodeValue,
                                           $element->getElementsByTagName('email')->item(0)->nodeValue,
                                           $element->getElementsByTagName('uri')->item(0)->nodeValue);
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getCopyright()
    {
        if (isset($this->_data['copyright'])) {
            return $this->_data['copyright'];
        }

        $copyright = null;

        if ($this->getType() === Zend_Feed_Reader::TYPE_ATOM_03) {
            $copyright = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:copyright)');
        } else {
            $copyright = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:rights)');
        }

        if (!$copyright) {
            $copyright = null;
        }

        $this->_data['copyright'] = $copyright;
        return $this->_data['copyright'];
    }
    
    /**
     * Enter description here...
     *
     * @return unknown
     */
    public function getDateCreated()
    {
        if (isset($this->_data['datecreated'])) {
            return $this->_data['datecreated'];
        }

        if ($this->getType() === Zend_Feed_Reader::TYPE_ATOM_03) {
            $dateCreated = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:created)');
        } else {
            $dateCreated = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:published)');
        }
        
        if (!$dateCreated) {
            $dateCreated = null;
        }
        // TODO: Make the date a Zend_Date object?
        $this->_data['datecreated'] = $dateCreated;
        return $this->_data['datecreated'];
    }
    
    /**
     * Enter description here...
     *
     * @return unknown
     */
    public function getDateModified()
    {
        if (isset($this->_data['datemodified'])) {
            return $this->_data['datemodified'];
        }

        $dateModified = null;

        if ($this->getType() === Zend_Feed_Reader::TYPE_ATOM_03) {
            $dateModified = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:modified)');
        } else {
            $dateModified = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:updated)');
        }
        
        // TODO: Make the date a Zend_Date object?
        if (!$dateModified) {
            $dateModified = null;
        }

        $this->_data['datemodified'] = $dateModified;
        return $this->_data['datemodified'];
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getDescription()
    {
        if (isset($this->_data['description'])) {
            return $this->_data['description'];
        }

        $description = null;

        if ($this->getType() === Zend_Feed_Reader::TYPE_ATOM_03) {
            $description = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:tagline)'); // TODO: Is this the same as subtitle?
        } else {
            $description = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:subtitle)');
        }

        if (!$description) {
            $description = null;
        }

        $this->_data['description'] = $description;
        return $this->_data['description'];
    }
    
    /**
     * Enter description here...
     *
     * @return string
     */
    public function getGenerator()
    {
        if (isset($this->_data['generator'])) {
            return $this->_data['generator'];
        }
        // TODO: Add uri support
        $generator = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:generator)');

        if (!$generator) {
            $generator = null;
        }

        $this->_data['generator'] = $generator;
        return $this->_data['generator'];
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getLanguage()
    {
        if (isset($this->_data['language'])) {
            return $this->_data['language'];
        }

        $language = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:lang)');

        if (!$language) {
            $language = null;
        }

        $this->_data['language'] = $language;
        return $this->_data['language'];
    }
    
    /**
     * Enter description here...
     *
     * @return string
     */
    public function getId()
    {
        if (isset($this->_data['id'])) {
            return $this->_data['id'];
        }

        $id = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:id)');

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

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getLink()
    {
        if (isset($this->_data['link'])) {
            return $this->_data['link'];
        }

        $link = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:link/@href)');

        if (!$link) {
            $link = null;
        }

        $this->_data['link'] = $link;
        return $this->_data['link'];
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getTitle()
    {
        if (isset($this->_data['title'])) {
            return $this->_data['title'];
        }

        $title = $this->_xpath->evaluate('string(' . $this->getXpathPrefix() . '/atom:title)');

        if (!$title) {
            $title = null;
        }

        $this->_data['title'] = $title;
        return $this->_data['title'];
    }
    
    /**
     * Enter description here...
     *
     */
    protected function _registerDefaultNamespaces()
    {
        switch ($this->_data['type']) {
            case Zend_Feed_Reader::TYPE_ATOM_10:
                $this->_xpath->registerNamespace('atom', Zend_Feed_Reader::NAMESPACE_ATOM_10);
                break;
            case Zend_Feed_Reader::TYPE_ATOM_03:
                $this->_xpath->registerNamespace('atom', Zend_Feed_Reader::NAMESPACE_ATOM_03);
                break;
            default:
                $this->_xpath->registerNamespace('atom', Zend_Feed_Reader::NAMESPACE_ATOM_10);
        }
    }

    /**
     * Enter description here...
     *
     */
    protected function _indexEntries()
    {
        if ($this->getType() == Zend_Feed_Reader::NAMESPACE_ATOM_10 || $this->getType() == Zend_Feed_Reader::NAMESPACE_ATOM_03) {
            $entries = array();
            $entries = $this->_xpath->evaluate('//atom:entry');
            foreach($entries as $index=>$entry) {
                $this->_entries[$index] = $entry;
            }
        }
    }
}