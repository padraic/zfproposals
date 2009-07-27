<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to padraic dot brady at yahoo dot com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Feed_Writer
 * @copyright  Copyright (c) 2009 Padraic Brady
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Date
 */
require_once 'Zend/Date.php';

/**
 * @see Zend_Date
 */
require_once 'Zend/Uri.php';

/**
 * @see Zend_Feed_Writer_Entry
 */
require_once 'Zend/Feed/Writer/Entry.php';

/**
 * @see Zend_Feed_Writer_Renderer_Feed_Atom
 */
require_once 'Zend/Feed/Writer/Renderer/Feed/Atom.php';

/**
 * @see Zend_Feed_Writer_Renderer_Feed_Rss
 */
//require_once 'Zend/Feed/Writer/Renderer/Feed/Rss.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Writer
 * @copyright  Copyright (c) 2009 Padraic Brady
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Writer implements Iterator, Countable
{
	/**
	 * Namespace constants
	 */
	const NAMESPACE_ATOM_03  = 'http://purl.org/atom/ns#';
    const NAMESPACE_ATOM_10  = 'http://www.w3.org/2005/Atom';
    const NAMESPACE_RDF      = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
    const NAMESPACE_RSS_090  = 'http://my.netscape.com/rdf/simple/0.9/';
    const NAMESPACE_RSS_10   = 'http://purl.org/rss/1.0/';

    /**
	 * Feed type constants
	 */
	const TYPE_ANY              = 'any';
	const TYPE_ATOM_03          = 'atom-03';
    const TYPE_ATOM_10          = 'atom-10';
    const TYPE_ATOM_ANY         = 'atom';
    const TYPE_RSS_090          = 'rss-090';
    const TYPE_RSS_091          = 'rss-091';
    const TYPE_RSS_091_NETSCAPE = 'rss-091n';
    const TYPE_RSS_091_USERLAND = 'rss-091u';
    const TYPE_RSS_092          = 'rss-092';
    const TYPE_RSS_093          = 'rss-093';
    const TYPE_RSS_094          = 'rss-094';
    const TYPE_RSS_10           = 'rss-10';
    const TYPE_RSS_20           = 'rss-20';
    const TYPE_RSS_ANY          = 'rss';

    /**
     * Contains all Feed level date to append in feed output
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Contains all entry objects
     *
     * @var array
     */
    protected $_entries = array();

    /**
     * A pointer for the iterator to keep track of the entries array
     *
     * @var int
     */
    protected $_entriesKey = 0;

    /**
     * Set a single author
     *
     * @param  int $index
     * @return string|null
     */
    public function addAuthor($name, $email = null, $uri = null)
    {
        $author = array();
        if (is_array($name)) {
            if (!array_key_exists('name', $name) || empty($name['name']) || !is_string($name['name'])) {
                require_once 'Zend/Feed/Exception.php';
                throw new Zend_Feed_Exception('Invalid parameter: author array must include a "name" key with a non-empty string value');
            }
            $author['name'] = $name['name'];
            if (isset($name['email'])) {
                if (empty($name['email']) || !is_string($name['email'])) {
                    require_once 'Zend/Feed/Exception.php';
                    throw new Zend_Feed_Exception('Invalid parameter: "email" array value must be a non-empty string');
                }
                $author['email'] = $name['email'];
            }
            if (isset($name['uri'])) {
                if (empty($name['uri']) || !is_string($name['uri']) || !Zend_Uri::check($name['uri'])) {
                    require_once 'Zend/Feed/Exception.php';
                    throw new Zend_Feed_Exception('Invalid parameter: "uri" array value must be a non-empty string and valid URI/IRI');
                }
                $author['uri'] = $name['uri'];
            }
        } else {
            if (empty($name['name']) || !is_string($name['name'])) {
                require_once 'Zend/Feed/Exception.php';
                throw new Zend_Feed_Exception('Invalid parameter: "name" must be a non-empty string value');
            }
            $author['name'] = $name;
            if (isset($email)) {
                if (empty($email) || !is_string($email)) {
                    require_once 'Zend/Feed/Exception.php';
                    throw new Zend_Feed_Exception('Invalid parameter: "email" value must be a non-empty string');
                }
                $author['email'] = $email;
            }
            if (isset($uri)) {
                if (empty($uri) || !is_string($uri) || !Zend_Uri::check($uri)) {
                    require_once 'Zend/Feed/Exception.php';
                    throw new Zend_Feed_Exception('Invalid parameter: "uri" value must be a non-empty string and valid URI/IRI');
                }
                $author['uri'] = $uri;
            }
        }
        $this->_data['authors'][] = $author;
    }

    /**
     * Set an array with feed authors
     *
     * @return array
     */
    public function addAuthors(array $authors)
    {
        foreach($authors as $author) {
            $this->addAuthor($author);
        }
    }

    /**
     * Set the copyright entry
     *
     * @return string|null
     */
    public function setCopyright($copyright)
    {
        if (empty($copyright) || !is_string($copyright)) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: parameter must be a non-empty string');
        }
        $this->_data['copyright'] = $copyright;
    }

    /**
     * Set the feed creation date
     *
     * @return string|null
     */
    public function setDateCreated($date = null)
    {
        $zdate = null;
        if (is_null($date)) {
            $zdate = new Zend_Date;
        } elseif (ctype_digit($date) && strlen($date) == 10) {
            $zdate = new Zend_Date($date, Zend_Date::TIMESTAMP);
        } elseif ($date instanceof Zend_Date) {
            $zdate = $date;
        } else {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid Zend_Date object or UNIX Timestamp passed as parameter');
        }
        $this->_data['dateCreated'] = $zdate;
    }

    /**
     * Set the feed modification date
     *
     * @return string|null
     */
    public function setDateModified($date = null)
    {
        $zdate = null;
        if (is_null($date)) {
            $zdate = new Zend_Date;
        } elseif (ctype_digit($date) && strlen($date) == 10) {
            $zdate = new Zend_Date($date, Zend_Date::TIMESTAMP);
        } elseif ($date instanceof Zend_Date) {
            $zdate = $date;
        } else {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid Zend_Date object or UNIX Timestamp passed as parameter');
        }
        $this->_data['dateModified'] = $zdate;
    }

    /**
     * Set the feed description
     *
     * @return string|null
     */
    public function setDescription($description)
    {
        if (empty($description) || !is_string($description)) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: parameter must be a non-empty string');
        }
        $this->_data['description'] = $description;
    }

    /**
     * Set the feed generator entry
     *
     * @return string|null
     */
    public function setGenerator($name, $version = null, $uri = null)
    {
        if (empty($name) || !is_string($name)) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: "name" must be a non-empty string');
        }
        $generator = array('name' => $name);
        if (isset($version)) {
            if (empty($version) || !is_string($version)) {
                require_once 'Zend/Feed/Exception.php';
                throw new Zend_Feed_Exception('Invalid parameter: "version" must be a non-empty string');
            }
            $generator['version'] = $version;
        }
        if (isset($uri)) {
            if (empty($uri) || !is_string($uri) || !Zend_Uri::check($uri)) {
                require_once 'Zend/Feed/Exception.php';
                throw new Zend_Feed_Exception('Invalid parameter: "uri" must be a non-empty string and a valid URI/IRI');
            }
            $generator['uri'] = $uri;
        }
        $this->_data['generator'] = $generator;
    }

    /**
     * Set the feed ID - URI or URN (via PCRE pattern) supported
     *
     * @return string|null
     */
    public function setId($id)
    {
        if ((empty($id) || !is_string($id) || !Zend_Uri::check($id)) &&
        !preg_match("#^urn:[a-zA-Z0-9][a-zA-Z0-9\-]{1,31}:([a-zA-Z0-9\(\)\+\,\.\:\=\@\;\$\_\!\*\-]|%[0-9a-fA-F]{2})*#", $id)) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: parameter must be a non-empty string and valid URI/IRI');
        }
        $this->_data['id'] = $id;
    }

    /**
     * Set the feed language
     *
     * @return string|null
     */
    public function setLanguage($language)
    {
        if (empty($language) || !is_string($language)) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: parameter must be a non-empty string');
        }
        $this->_data['language'] = $language;
    }

    /**
     * Set a link to the HTML source
     *
     * @return string|null
     */
    public function setLink($link)
    {
        if (empty($link) || !is_string($link) || !Zend_Uri::check($link)) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: parameter must be a non-empty string and valid URI/IRI');
        }
        $this->_data['link'] = $link;
    }

    /**
     * Set a link to an XML feed for any feed type/version
     *
     * @return string|null
     */
    public function setFeedLink($link, $type)
    {
        if (empty($link) || !is_string($link) || !Zend_Uri::check($link)) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: "link"" must be a non-empty string and valid URI/IRI');
        }
        if (!in_array(strtolower($type), array('rss', 'rdf', 'atom'))) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: "type"; You must declare the type of feed the link points to, i.e. RSS, RDF or Atom');
        }
        $this->_data['feedLinks'][strtolower($type)] = $link;
    }

    /**
     * Set the feed title
     *
     * @return string|null
     */
    public function setTitle($title)
    {
        if (empty($title) || !is_string($title)) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: parameter must be a non-empty string');
        }
        $this->_data['title'] = $title;
    }

    /**
     * Set the feed character encoding
     *
     * @return string|null
     */
    public function setEncoding($encoding)
    {
        if (empty($encoding) || !is_string($encoding)) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: parameter must be a non-empty string');
        }
        $this->_data['encoding'] = $encoding;
    }

    /**
     * Get a single author
     *
     * @param  int $index
     * @return string|null
     */
    public function getAuthor($index = 0)
    {
        if (isset($this->_data['authors'][$index])) {
            return $this->_data['authors'][$index];
        } else {
            return null;
        }
    }

    /**
     * Get an array with feed authors
     *
     * @return array
     */
    public function getAuthors()
    {
        if (!array_key_exists('authors', $this->_data)) {
            return null;
        }
        return $this->_data['authors'];
    }

    /**
     * Get the copyright entry
     *
     * @return string|null
     */
    public function getCopyright()
    {
        if (!array_key_exists('copyright', $this->_data)) {
            return null;
        }
        return $this->_data['copyright'];
    }

    /**
     * Get the feed creation date
     *
     * @return string|null
     */
    public function getDateCreated()
    {
        if (!array_key_exists('dateCreated', $this->_data)) {
            return null;
        }
        return $this->_data['dateCreated'];
    }

    /**
     * Get the feed modification date
     *
     * @return string|null
     */
    public function getDateModified()
    {
        if (!array_key_exists('dateModified', $this->_data)) {
            return null;
        }
        return $this->_data['dateModified'];
    }

    /**
     * Get the feed description
     *
     * @return string|null
     */
    public function getDescription()
    {
        if (!array_key_exists('description', $this->_data)) {
            return null;
        }
        return $this->_data['description'];
    }

    /**
     * Get the feed generator entry
     *
     * @return string|null
     */
    public function getGenerator()
    {
        if (!array_key_exists('generator', $this->_data)) {
            return null;
        }
        return $this->_data['generator'];
    }

    /**
     * Get the feed ID
     *
     * @return string|null
     */
    public function getId()
    {
        if (!array_key_exists('id', $this->_data)) {
            return null;
        }
        return $this->_data['id'];
    }

    /**
     * Get the feed language
     *
     * @return string|null
     */
    public function getLanguage()
    {
        if (!array_key_exists('language', $this->_data)) {
            return null;
        }
        return $this->_data['language'];
    }

    /**
     * Get a link to the HTML source
     *
     * @return string|null
     */
    public function getLink()
    {
        if (!array_key_exists('link', $this->_data)) {
            return null;
        }
        return $this->_data['link'];
    }

    /**
     * Get a link to the XML feed
     *
     * @return string|null
     */
    public function getFeedLinks()
    {
        if (!array_key_exists('feedLinks', $this->_data)) {
            return null;
        }
        return $this->_data['feedLinks'];
    }

    /**
     * Get the feed title
     *
     * @return string|null
     */
    public function getTitle()
    {
        if (!array_key_exists('title', $this->_data)) {
            return null;
        }
        return $this->_data['title'];
    }

    /**
     * Get the feed character encoding
     *
     * @return string|null
     */
    public function getEncoding()
    {
        if (!array_key_exists('encoding', $this->_data)) {
            return null;
        }
        return $this->_data['encoding'];
    }

    /**
     * Resets the instance and deletes all data
     *
     */
    public function reset()
    {
        $this->_data = array();
    }

    public function createEntry()
    {
        $entry = new Zend_Feed_Writer_Entry;
        if ($this->getEncoding()) {
            $entry->setEncoding($this->getEncoding());
        }
        return $entry;
    }

    public function addEntry(Zend_Feed_Writer_Entry $entry)
    {
        $this->_entries[] = $entry;
    }

    public function removeEntry($index)
    {
        if (isset($this->_entries[$index])) {
            unset($this->_entries[$index]);
        }
        require_once 'Zend/Feed/Exception.php';
        throw new Zend_Feed_Exception('Undefined index: ' . $index . '. Entry does not exist.');
    }

    public function getEntry($index = 0)
    {
        if (isset($this->_entries[$index])) {
            return $this->_entries[$index];
        }
        require_once 'Zend/Feed/Exception.php';
        throw new Zend_Feed_Exception('Undefined index: ' . $index . '. Entry does not exist.');
    }

    public function orderByDate() // partially tested - created v modified? Check prevailing practice
    {
        // maybe not the most efficient way - but it works until something better comes along
        $timestamp = time();
        $entries = array();
        foreach ($this->_entries as $entry) {
            if ($entry->getDateModified()) {
                $timestamp = (int) $entry->getDateModified()->get(Zend_Date::TIMESTAMP);
            } elseif ($entry->getDateCreated()) {
                $timestamp = (int) $entry->getDateCreated()->get(Zend_Date::TIMESTAMP);
            }
            $entries[$timestamp] = $entry;
        }
        krsort($entries, SORT_NUMERIC);
        $this->_entries = array_values($entries);
    }

    /**
     * Get the number of feed entries.
     * Required by the Iterator interface.
     *
     * @return int
     */
    public function count()
    {
        return count($this->_entries);
    }

	/**
     * Return the current entry
     *
     * @return Zend_Feed_Reader_Entry_Interface
     */
    public function current()
    {
        return $this->_entries[$this->key()];
    }

    /**
     * Return the current feed key
     *
     * @return unknown
     */
    public function key()
    {
        return $this->_entriesKey;
    }

	/**
     * Move the feed pointer forward
     *
     */
    public function next()
    {
        ++$this->_entriesKey;
    }

    /**
     * Reset the pointer in the feed object
     *
     */
    public function rewind()
    {
        $this->_entriesKey = 0;
    }

    /**
     * Check to see if the iterator is still valid
     *
     * @return boolean
     */
    public function valid()
    {
        return 0 <= $this->_entriesKey && $this->_entriesKey < $this->count();
    }

    /**
     * Attempt to build and return the feed resulting from the data set
     *
     * @param $type The feed type "rss" or "atom" to export as
     * @return string
     */
    public function export($type, $ignoreExceptions = false)
    {
        $type = ucfirst(strtolower($type));
        if ($type !== 'Rss' && $type !== 'Atom') {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid feed type specified: ' . $type . '.'
            . ' Should be one of "rss" or "atom".');
        }
        $renderClass = 'Zend_Feed_Writer_Renderer_Feed_' . $type;
        $renderer = new $renderClass($this);
        if ($ignoreExceptions) {
            $renderer->ignoreExceptions();
        }
        return $renderer->render()->saveXml();
    }


}
