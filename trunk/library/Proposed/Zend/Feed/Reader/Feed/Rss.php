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
        } else {
            $authors = array_unique($authors);
        }
        $this->_data['authors'] = $authors;
        return $this->_data['authors'];
    }

    public function getAuthor()
    {
        $authors = $this->getAuthors();
        if (isset($authors[0])) {
            return $authors[0];
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
            if (!$copyright) {
                $copyright = $this->_xpath->evaluate('string(/rss/channel/dc11:rights)');
            }
            if (!$copyright) {
                $copyright = $this->_xpath->evaluate('string(/rss/channel/dc10:rights)');
            }
        } else {
            $copyright = $this->_xpath->evaluate('string(/rdf:RDF/rss:channel/dc11:rights)');
            if (!$copyright) {
                $copyright = $this->_xpath->evaluate('string(/rdf:RDF/rss:channel/dc10:rights)');
            }
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
        if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10 && $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090) {
            $description = $this->_xpath->evaluate('string(/rss/channel/description)');
            if (!$description) {
                $description = $this->_xpath->evaluate('string(/rss/channel/dc11:description)');
            }
            if (!$description) {
                $description = $this->_xpath->evaluate('string(/rss/channel/dc10:description)');
            }
        } else {
            $description = $this->_xpath->evaluate('string(/rdf:RDF/rss:channel/rss:description)');
            if (!$description) {
                $description = $this->_xpath->evaluate('string(/rdf:RDF/rss:channel/dc11:description)');
            }
            if (!$description) {
                $description = $this->_xpath->evaluate('string(/rdf:RDF/rss:channel/dc10:description)');
            }
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
        $language = null;
        if ($this->getType() !== Zend_Feed_Reader::TYPE_RSS_10 && $this->getType() !== Zend_Feed_Reader::TYPE_RSS_090) {
            $language = $this->_xpath->evaluate('string(/rss/channel/language)');
            if (!$language) {
                $language = $this->_xpath->evaluate('string(/rss/channel/dc11:language)');
            }
            if (!$language) {
                $language = $this->_xpath->evaluate('string(/rss/channel/dc10:language)');
            }
        } else {
            if (!$language) {
                $language = $this->_xpath->evaluate('string(/rdf:RDF/rss:channel/dc11:language)');
            }
            if (!$language) {
                $language = $this->_xpath->evaluate('string(/rdf:RDF/rss:channel/dc10:language)');
            }
            if (!$language) {
                $language = $this->_xpath->evaluate('string(//@xml:lang[1])');
            }
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
        $this->_xpath->registerNamespace('atom', Zend_Feed_Reader::NAMESPACE_ATOM_10);
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