<?php

require_once 'Zend/Feed/Reader.php';

require_once 'Zend/Feed/Reader/Author.php';

class Zend_Feed_Reader_Entry_Dc
{

    protected $_entry = null;

    protected $_entryKey = 0;

    protected $_data = array();

    protected $_xpath = null;

    protected $_domDocument = null;

    protected $_xpathPrefix = '';

    public function __construct(Zend_Feed_Entry_Abstract $entry, $entryKey, $type = null)
    {
        $this->_entry = $entry;
        $this->_entryKey = $entryKey;
        $this->_domDocument = $this->_entry->getDOM()->ownerDocument;
        if (!is_null($type)) {
            $this->_data['type'] = $type;
        } else {
            $this->_data['type'] = Zend_Feed_Reader::detectType($feed);
        }
        if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10 && $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090) {
            $this->setXpathPrefix('//item[' . ($this->_entryKey+1) . ']');
        } else {
            $this->setXpathPrefix('//rss:item[' . ($this->_entryKey+1) . ']');
        }
    }

    public function setXpathPrefix($prefix) 
    {
        $this->_xpathPrefix = $prefix;
    }

    public function getXpathPrefix() 
    {
        return $this->_xpathPrefix;
    }

    public function setXpath(DOMXPath $xpath)
    {
        $this->_xpath = $xpath;
        $this->_registerNamespaces();
    }

    public function getAuthors()
    {
        if (isset($this->_data['authors'])) {
            return $this->_data['authors'];
        }
        $authors = array();
        $list = $this->_xpath->evaluate($this->getXpathPrefix() . '//dc11:creator');
        if (!$list->length) {
            $list = $this->_xpath->evaluate($this->getXpathPrefix() . '//dc10:creator');
        }
        if ($list->length) {
            foreach ($list as $author) {
                if ($this->getType() == Zend_Feed_Reader::TYPE_RSS_20
                    && preg_match("/\(([^\)]+)\)/", $author->nodeValue, $matches, PREG_OFFSET_CAPTURE)) {
                    $authors[] = $matches[1][0];
                } else {
                    $authors[] = $author->nodeValue;
                }
            }
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

    public function getContent()
    {
        return $this->getDescription();
    }
    
    public function getDateCreated()
    {
        // TODO: Implement
    }
    
    public function getDateModified()
    {
        // TODO: Implement
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

    public function getType()
    {
        return $this->_data['type'];
    }

    public function toArray()
    {
        return $this->_data;
    }

    public function getDomDocument()
    {
        return $this->_domDocument;
    }

    protected function _registerNamespaces() 
    {
        $this->_xpath->registerNamespace('dc10', Zend_Feed_Reader::NAMESPACE_DC_10);
        $this->_xpath->registerNamespace('dc11', Zend_Feed_Reader::NAMESPACE_DC_11);
    }

}