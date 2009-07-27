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
class Zend_Feed_Writer_Renderer_Entry_Atom implements Zend_Feed_Writer_RendererInterface
{

    protected $_container = null;

    protected $_dom = null;

    protected $_ignoreExceptions = false;

    protected $_exceptions = array();

    public function __construct (Zend_Feed_Writer_Entry $container)
    {
        $this->_container = $container;
    }

    public function render()
    {
        $this->_dom = new DOMDocument('1.0', $this->_container->getEncoding());
        $this->_dom->formatOutput = true;
        $entry = $this->_dom->createElementNS(Zend_Feed_Writer::NAMESPACE_ATOM_10, 'entry');
        $this->_dom->appendChild($entry);
        // set the title (assumed text) COMPULSORY
        $title = $this->_dom->createElement('title');
        $entry->appendChild($title);
        $title->setAttribute('type', 'text');
        $title->nodeValue = $this->_container->getTitle();
        // set the summary(assumed text) OPTIONAL
        if ($this->_container->getDescription()) {
            $summary = $this->_dom->createElement('summary');
            $entry->appendChild($summary);
            $summary->setAttribute('type', 'text');
            $summary->nodeValue = $this->_container->getDescription();
        }
        // set the updated/modified date COMPULSORY
        $updated = $this->_dom->createElement('updated');
        $entry->appendChild($updated);
        $updated->nodeValue = $this->_container->getDateModified()->get(Zend_Date::ISO_8601);
        // set the published/created date COMPULSORY
        $published = $this->_dom->createElement('published');
        $entry->appendChild($published);
        $published->nodeValue = $this->_container->getDateCreated()->get(Zend_Date::ISO_8601);
        // set HTML link to entry source COMPULSORY
        $link = $this->_dom->createElement('link');
        $entry->appendChild($link);
        $link->setAttribute('rel', 'alternate');
        $link->setAttribute('type', 'text/html');
        $link->setAttribute('href', $this->_container->getLink());
        // set the entry id
        if (!$this->_container->getId()) {
            $this->_container->setId($this->_container->getLink());
        }
        $id = $this->_dom->createElement('id');
        $entry->appendChild($id);
        $id->nodeValue = $this->_container->getId();
        // add all authors
        if ($this->_container->getAuthors()) {
            $authors = $this->_container->getAuthors();
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
        $content->nodeValue = $this->_container->getContent();

        return $this;
    }

    public function saveXml()
    {
        return $this->_dom->saveXml();
    }

    public function getDomDocument()
    {
        return $this->_dom;
    }

    public function getElement()
    {
        return $this->_dom->documentElement;
    }

    public function getDataContainer()
    {
        return $this->_container;
    }

    public function ignoreExceptions($bool = true)
    {
        if (!is_bool($bool)) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: $bool. Should be TRUE or FALSE (defaults to TRUE if null)');
        }
        $this->_ignoreExceptions = $bool;
    }

    public function getExceptions()
    {
        return $this->_exceptions;
    }

}
