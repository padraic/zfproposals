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
 * @package    Zend_Feed_Writer_Entry
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
 * @category   Zend
 * @package    Zend_Feed_Writer_Entry
 * @copyright  Copyright (c) 2009 Padraic Brady
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Writer_Entry
{

    protected $_data = array();

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
     * Set the entry's content
     *
     * @return string|null
     */
    public function setContent($content)
    {
        if (empty($content) || !is_string($content)) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: parameter must be a non-empty string');
        }
        $this->_data['content'] = $content;
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
     * Set the feed ID
     *
     * @return string|null
     */
    public function setId($id)
    {
        if (empty($id) || !is_string($id) || !Zend_Uri::check($id)) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: parameter must be a non-empty string and valid URI/IRI');
        }
        $this->_data['id'] = $id;
    }

    /**
     * Set a link to the HTML source of this entry
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
     * Set a permalink to the source of this entry
     *
     * @return string|null
     */
    public function setPermalink($link)
    {
        if (empty($link) || !is_string($link) || !Zend_Uri::check($link)) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: parameter must be a non-empty string and valid URI/IRI');
        }
        $this->_data['permalink'] = $link;
    }

    /**
     * Set the number of comments associated with this entry
     *
     * @return string|null
     */
    public function setCommentCount($count)
    {
        if (empty($count) || !ctype_digit($count)) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: "count" must be a non-empty integer number');
        }
        $this->_data['commentCount'] = (int) $count;
    }

    /**
     * Set a link to a HTML page containing comments associated with this entry
     *
     * @return string|null
     */
    public function setCommentLink($link)
    {
        if (empty($link) || !is_string($link) || !Zend_Uri::check($link)) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: "link" must be a non-empty string and valid URI/IRI');
        }
        $this->_data['commentLink'] = $link;
    }

    /**
     * Set a link to an XML feed for any comments associated with this entry
     *
     * @return string|null
     */
    public function setCommentFeedLink($link)
    {
        if (empty($link) || !is_string($link) || !Zend_Uri::check($link)) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: "link" must be a non-empty string and valid URI/IRI');
        }
        $this->_data['commentFeedLink'] = $link;
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
     * Get the specified author
     *
     * @param  int $index
     * @return string|null
     */
    public function getAuthor($index = 0)
    {
        if (!array_key_exists('authors', $this->_data) || !isset($this->_data['authors'][$index])) {
            return null;
        }
        return $this->_data['authors'][$index];
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
     * Get the entry content
     *
     * @return string
     */
    public function getContent()
    {
        if (!array_key_exists('content', $this->_data)) {
            return null;
        }
        return $this->_data['content'];
    }

    /**
     * Get the entry copyright information
     *
     * @return string
     */
    public function getCopyright()
    {
        if (!array_key_exists('copyright', $this->_data)) {
            return null;
        }
        return $this->_data['copyright'];
    }

    /**
     * Get the entry creation date
     *
     * @return string
     */
    public function getDateCreated()
    {
        if (!array_key_exists('dateCreated', $this->_data)) {
            return null;
        }
        return $this->_data['dateCreated'];
    }

    /**
     * Get the entry modification date
     *
     * @return string
     */
    public function getDateModified()
    {
        if (!array_key_exists('dateModified', $this->_data)) {
            return null;
        }
        return $this->_data['dateModified'];
    }

    /**
     * Get the entry description
     *
     * @return string
     */
    public function getDescription()
    {
        if (!array_key_exists('description', $this->_data)) {
            return null;
        }
        return $this->_data['description'];
    }

    /**
     * Get the entry ID
     *
     * @return string
     */
    public function getId()
    {
        if (!array_key_exists('id', $this->_data)) {
            return null;
        }
        return $this->_data['id'];
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
     * Get all links
     *
     * @return array
     */
    public function getLinks()
    {
        if (!array_key_exists('links', $this->_data)) {
            return null;
        }
        return $this->_data['links'];
    }

    /**
     * Get a permalink to the entry
     *
     * @return string
     */
    public function getPermalink()
    {
        if (!array_key_exists('permalink', $this->_data)) {
            return null;
        }
        return $this->_data['permalink'];
    }

    /**
     * Get the entry title
     *
     * @return string
     */
    public function getTitle()
    {
        if (!array_key_exists('title', $this->_data)) {
            return null;
        }
        return $this->_data['title'];
    }

    /**
     * Get the number of comments/replies for current entry
     *
     * @return integer
     */
    public function getCommentCount()
    {
        if (!array_key_exists('commentCount', $this->_data)) {
            return null;
        }
        return $this->_data['commentCount'];
    }

    /**
     * Returns a URI pointing to the HTML page where comments can be made on this entry
     *
     * @return string
     */
    public function getCommentLink()
    {
        if (!array_key_exists('commentLink', $this->_data)) {
            return null;
        }
        return $this->_data['commentLink'];
    }

    /**
     * Returns a URI pointing to a feed of all comments for this entry
     *
     * @return string
     */
    public function getCommentFeedLink()
    {
        if (!array_key_exists('commentFeedLink', $this->_data)) {
            return null;
        }
        return $this->_data['commentFeedLink'];
    }

}
