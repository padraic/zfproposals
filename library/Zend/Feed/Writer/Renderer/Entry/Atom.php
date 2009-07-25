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
 * @package    Zend_Feed_Writer_Entry_Atom
 * @copyright  Copyright (c) 2009 Padraic Brady
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'Zend/Feed/Writer.php';

require_once 'Zend/Version.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Writer_Entry_Atom
 * @copyright  Copyright (c) 2009 Padraic Brady
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Writer_Entry_Atom
{

    protected $_writer = null;

    protected $_dom = null;

    protected $_element = null;

    public function __construct (Zend_Feed_Writer_Entry $writer, DOMDocument $dom = null)
    {
        $this->_writer = $writer;
        $this->_dom = $dom;
    }

    public function getWriter()
    {
        return $this->_writer;
    }

    public function build()
    {
        if (is_null($this->_dom)) {
            //untested until later
            $this->_dom = new DOMDocument('1.0', $this->_writer->getEncoding());
            $this->_dom->formatOutput = true;
            $entry = $this->_dom->createElementNS(Zend_Feed_Writer::NAMESPACE_ATOM_10, 'entry');
        } else {
            $entry = $this->_dom->createElement('entry');
        }
        // set the title (assumed text) COMPULSORY
        $title = $this->_dom->createElement('title');
        $entry->appendChild($title);
        $title->setAttribute('type', 'text');
        $title->nodeValue = $this->_writer->getTitle();
        // set the subtitle (assumed text) OPTIONAL
        if ($this->_writer->getDescription()) {
            $subtitle = $this->_dom->createElement('subtitle');
            $entry->appendChild($subtitle);
            $subtitle->setAttribute('type', 'text');
            $subtitle->nodeValue = $this->_writer->getDescription();
        }
        // set the updated/modified date COMPULSORY
        $updated = $this->_dom->createElement('updated');
        $entry->appendChild($updated);
        $updated->nodeValue = $this->_writer->getDateModified()->get(Zend_Date::ISO_8601);
        // set the published/created date COMPULSORY
        if ($this->_writer->getDateCreated()) {
            $published = $this->_dom->createElement('published');
            $entry->appendChild($published);
            $published->nodeValue = $this->_writer->getDateCreated()->get(Zend_Date::ISO_8601);
        }
        // set HTML link to entry source COMPULSORY
        $link = $this->_dom->createElement('link');
        $entry->appendChild($link);
        $link->setAttribute('rel', 'alternate');
        $link->setAttribute('type', 'text/html');
        $link->setAttribute('href', $this->_writer->getLink());
        // set the entry id
        if (!$this->_writer->getId()) {
            $this->_writer->setId($this->_writer->getLink());
        }
        $id = $this->_dom->createElement('id');
        $entry->appendChild($id);
        $id->nodeValue = $this->_writer->getId();
        // add all authors
        if ($this->_writer->getAuthors()) {
            $authors = $this->_writer->getAuthors();
            foreach ($authors as $data) {
                $author = $this->_dom->createElement('author');
                $name = $this->_dom->createElement('name');
                $author->appendChild($name);
                $entry->appendChild($author);
                $name->nodeValue = $data['name'];
                if (array_key_exists('email', $data)) {
                    $email = $this->_dom->createElement('email');
                    $author->appendChild($email);
                    $email->nodeValue = $data['email'];
                }
                if (array_key_exists('uri', $data)) {
                    $uri = $this->_dom->createElement('uri');
                    $author->appendChild($uri);
                    $uri->nodeValue = $data['uri'];
                }
            }
        }
        // add content (assumed html) - OPTIONAL (Alternatives to implement)
        $content = $this->_dom->createElement('content');
        $entry->appendChild($content);
        $content->setAttribute('type', 'text/html');
        $content->nodeValue = $this->_writer->getContent();
        // set var
        $this->_element = $entry;
    }

    public function getElement()
    {
        return $this->_element;
    }

}
