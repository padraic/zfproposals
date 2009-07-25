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
 * @package    Zend_Feed_Writer_Renderer_Feed_Atom
 * @copyright  Copyright (c) 2009 Padraic Brady
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'Zend/Feed/Writer.php';

require_once 'Zend/Version.php';

require_once 'Zend/Feed/Writer/RendererInterface.php';

require_once 'Zend/Feed/Writer/Renderer/Entry/Atom.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Writer_Renderer_Feed_Atom
 * @copyright  Copyright (c) 2009 Padraic Brady
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Writer_Renderer_Feed_Atom implements Zend_Feed_Writer_RendererInterface
{

    protected $_container = null;

    protected $_dom = null;

    protected $_ignoreExceptions = false;

    protected $_exceptions = array();

    public function __construct (Zend_Feed_Writer $container)
    {
        $this->_container = $container;
    }

    public function render() // refactor later into many methods with error checks, standards checks and escaping
    {
        if (!$this->_container->getEncoding()) {
            $this->_container->setEncoding('utf-8');
        }
        $this->_dom = new DOMDocument('1.0', $this->_container->getEncoding());
        $this->_dom->formatOutput = true;
        // create the root element COMPULSORY
        $root = $this->_dom->createElementNS(Zend_Feed_Writer::NAMESPACE_ATOM_10, 'feed');
        $this->_dom->appendChild($root);
        // set the language OPTIONAL
        if ($this->_container->getLanguage()) {
            $root->setAttribute('xml:lang', $this->_container->getLanguage());
        }
        // set the title (assumed text) COMPULSORY
        $title = $this->_dom->createElement('title');
        $root->appendChild($title);
        $title->setAttribute('type', 'text');
        $title->nodeValue = $this->_container->getTitle();
        // set the subtitle (assumed text) OPTIONAL
        if ($this->_container->getDescription()) {
            $subtitle = $this->_dom->createElement('subtitle');
            $root->appendChild($subtitle);
            $subtitle->setAttribute('type', 'text');
            $subtitle->nodeValue = $this->_container->getDescription();
        }
        // set the updated/modified date COMPULSORY
        $updated = $this->_dom->createElement('updated');
        $root->appendChild($updated);
        $updated->nodeValue = $this->_container->getDateModified()->get(Zend_Date::ISO_8601);
        // set the generator OPTIONAL
        if (!$this->_container->getGenerator()) {
            $this->_container->setGenerator('Zend_Feed_Writer', Zend_Version::VERSION, 'http://framework.zend.com');
        }
        $gdata = $this->_container->getGenerator();
        $generator = $this->_dom->createElement('generator');
        $root->appendChild($generator);
        $generator->nodeValue = $gdata['name'];
        if (array_key_exists('uri', $gdata)) {
            $generator->setAttribute('uri', $gdata['uri']);
        }
        if (array_key_exists('version', $gdata)) {
            $generator->setAttribute('version', $gdata['version']);
        }
        // set HTML link to feed source COMPULSORY
        $link = $this->_dom->createElement('link');
        $root->appendChild($link);
        $link->setAttribute('rel', 'alternate');
        $link->setAttribute('type', 'text/html');
        $link->setAttribute('href', $this->_container->getLink());
        // set XML link to retrieve this XML feed file OPTIONAL
        if ($this->_container->getFeedLinks()) {
            $flinks = $this->_container->getFeedLinks();
            foreach ($flinks as $type => $href) {
                $mime = 'application/' . strtolower($type) . '+xml';
                $flink = $this->_dom->createElement('link');
                $root->appendChild($flink);
                $flink->setAttribute('rel', 'self');
                $flink->setAttribute('type', $mime);
                $flink->setAttribute('href', $href);
            }
        }
        // set the feed id
        if (!$this->_container->getId()) {
            $this->_container->setId($this->_container->getLink());
        }
        $id = $this->_dom->createElement('id');
        $root->appendChild($id);
        $id->nodeValue = $this->_container->getId();
        // add all authors
        if ($this->_container->getAuthors()) {
            $authors = $this->_container->getAuthors();
            foreach ($authors as $data) {
                $author = $this->_dom->createElement('author');
                $name = $this->_dom->createElement('name');
                $author->appendChild($name);
                $root->appendChild($author);
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
        foreach ($this->_container as $entry) {
            if ($this->getDataContainer()->getEncoding()) {
                $entry->setEncoding($this->getDataContainer()->getEncoding());
            }
            $renderer = new Zend_Feed_Writer_Renderer_Entry_Atom($entry);
            if ($this->_ignoreExceptions === true) {
                $renderer->ignoreExceptions();
            }
            $renderer->render();
            $element = $renderer->getElement();
            $imported = $this->_dom->importNode($element, true);
            $root->appendChild($imported);
        }
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
