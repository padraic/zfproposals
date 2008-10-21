<?php

require_once 'Zend/Feed/Reader.php';

require_once 'Zend/Feed/Reader/Entry/Interface.php';

require_once 'Zend/Feed/Reader/Author.php';

require_once 'Zend/Feed/Reader/Entry/Dc.php';

class Zend_Feed_Reader_Entry_Rss implements Zend_Feed_Reader_Entry_Interface
{

    protected $_entry = null;

    protected $_entryKey = 0;

    protected $_xpathQueryRss = '';

    protected $_xpathQueryRdf = '';

    protected $_data = array();

    protected $_xpath = null;

    protected $_domDocument = null;

    protected $_dc = null;

    public function __construct(Zend_Feed_Entry_Abstract $entry, $entryKey, $type = null)
    {
        $this->_entry = $entry;
        $this->_entryKey = $entryKey;
        $this->_xpathQueryRss = '//item[' . ($this->_entryKey+1) . ']';
        $this->_xpathQueryRdf = '//rss:item[' . ($this->_entryKey+1) . ']';
        $this->_domDocument = $this->_entry->getDOM()->ownerDocument;
        if (!is_null($type)) {
            $this->_data['type'] = $type;
        } else {
            $this->_data['type'] = Zend_Feed_Reader::detectType($feed);
        }
        $this->_dc = new Zend_Feed_Reader_Entry_Dc($entry, $entryKey, $type);
    }

    public function setXpath(DOMXPath $xpath)
    {
        $this->_xpath = $xpath;
        $this->_dc->setXpath($this->_xpath);
    }

    // TODO: Create Zend_Feed_Reader_Author objects
    public function getAuthors()
    {
        if (isset($this->_data['authors'])) {
            return $this->_data['authors'];
        }
        $authors = array();
        // @todo: create a list from all potential sources rather than from alternatives
        if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10 && $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090) {
            $list = $this->_xpath->evaluate($this->_xpathQueryRss.'//author');
        } else {
            $list = $this->_xpath->evaluate($this->_xpathQueryRdf.'//rss:author');
        }
        if ($list->length) {
            foreach ($list as $author) {
                if ($this->getType() == Zend_Feed_Reader::TYPE_RSS_20
                    && preg_match("/\(([^\)]+)\)/", $author->nodeValue, $matches, PREG_OFFSET_CAPTURE)) {
                    // source name from RSS 2.0 <author>
                    // format "joe@example.com (Joe Bloggs)"
                    $authors[] = $matches[1][0];
                } else {
                    $authors[] = $author->nodeValue;
                }
            }
            $authors = array_unique($authors);
        }
        if (empty($authors)) {
            $authors = $this->_dc->getAuthors();
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
        if (isset($this->_data['content'])) {
            return $this->_data['content'];
        }
        $content = null;
        if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10 && $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090) {
            $content = $this->_xpath->evaluate('string('.$this->_xpathQueryRss.'/content:encoded)');
        } else {
            $content = $this->_xpath->evaluate('string('.$this->_xpathQueryRdf.'/content:encoded)');
        }
        if (!$content) {
            $content = $this->getDescription();
        }
        $this->_data['content'] = $content;
        return $this->_data['content'];
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
        if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10 && $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090) {
            $description = $this->_xpath->evaluate('string('.$this->_xpathQueryRss.'/description)');
        } else {
            $description = $this->_xpath->evaluate('string('.$this->_xpathQueryRdf.'/rss:description)');
        }
        if (!$description) {
            $description = $this->_dc->getDescription();
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
        if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10 && $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090) {
            $id = $this->_xpath->evaluate('string('.$this->_xpathQueryRss.'/guid)');
        }
        if (!$id) {
            $id = $this->_dc->getId();
        }
        if (!$id) {
            if ($this->getPermalink()) {
                $id = $this->getPermalink();
            } elseif ($this->getTitle()) {
                $id = $this->getTitle();
            } else {
                $id = null;
            }
        }
        $this->_data['id'] = $id;
        return $this->_data['id'];
    }

    public function getLink($index = 0)
    {
        if (!isset($this->_data['links'])) {
            $this->getLinks();
        }
        if (isset($this->_data['links'][$index])) {
            return $this->_data['links'][$index];
        }
        return null;
    }

    public function getLinks() 
    {
        if (isset($this->_data['links'])) {
            return $this->_data['links'];
        }
        $links = array();
        if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10 && $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090) {
            $list = $this->_xpath->query($this->_xpathQueryRss.'//link');
        } else {
            $list = $this->_xpath->query($this->_xpathQueryRdf.'//rss:link');
        }
        foreach ($list as $link) {
            $links[] = $link->nodeValue;
        }
        $this->_data['links'] = $links;
        return $this->_data['links'];
    }

    public function getPermalink()
    {
        return $this->getLink(0);
    }

    public function getTitle()
    {
        if (isset($this->_data['title'])) {
            return $this->_data['title'];
        }
        $title = null;
        if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10 && $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090) {
            $title = $this->_xpath->evaluate('string('.$this->_xpathQueryRss.'/title)');
        } else {
            $title = $this->_xpath->evaluate('string('.$this->_xpathQueryRdf.'/rss:title)');
        }
        if (!$title) {
            $title = $this->_dc->getTitle();
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

}