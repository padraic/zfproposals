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
 * @package    Zend_Feed_Writer_Feed_Atom
 * @copyright  Copyright (c) 2009 Padraic Brady
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'Zend/Feed/Writer.php';

require_once 'Zend/Version.php';

require_once 'Zend/Feed/Writer/Entry/Atom.php';

/**
 * @category   Zend
 * @package    Zend_Feed_Writer_Feed_Atom
 * @copyright  Copyright (c) 2009 Padraic Brady
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Writer_Feed_Atom
{

    protected $_writer = null;

    protected $_dom = null;

    public function __construct (Zend_Feed_Writer $writer)
    {
        $this->_writer = $writer;
    }

    public function getWriter()
    {
        return $this->_writer;
    }

    public function build()
    {
        if (!$this->_writer->getEncoding()) {
            $this->_writer->setEncoding('utf-8');
        }
        $this->_dom = new DOMDocument('1.0', $this->_writer->getEncoding());
        $this->_dom->formatOutput = true;
        $ns = Zend_Feed_Writer::NAMESPACE_ATOM_10;
        // create the root element COMPULSORY
        $root = $this->_dom->createElementNS($ns, 'feed');
        $this->_dom->appendChild($root);
        // set the language OPTIONAL
        if ($this->_writer->getLanguage()) {
            $root->setAttribute('xml:lang', $this->_writer->getLanguage());
        }
        // set the title (assumed text) COMPULSORY
        $title = $this->_dom->createElement('title');
        $root->appendChild($title);
        $title->setAttribute('type', 'text');
        $title->nodeValue = $this->_writer->getTitle();
        // set the subtitle (assumed text) OPTIONAL
        if ($this->_writer->getDescription()) {
            $subtitle = $this->_dom->createElement('subtitle');
            $root->appendChild($subtitle);
            $subtitle->setAttribute('type', 'text');
            $subtitle->nodeValue = $this->_writer->getDescription();
        }
        // set the updated/modified date COMPULSORY
        $updated = $this->_dom->createElement('updated');
        $root->appendChild($updated);
        $updated->nodeValue = $this->_writer->getDateModified()->get(Zend_Date::ISO_8601);
        // set the generator OPTIONAL
        if (!$this->_writer->getGenerator()) {
            $this->_writer->setGenerator('Zend_Feed_Writer', Zend_Version::VERSION, 'http://framework.zend.com');
        }
        $gdata = $this->_writer->getGenerator();
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
        $link->setAttribute('href', $this->_writer->getLink());
        // set XML link to retrieve this XML feed file OPTIONAL
        if ($this->_writer->getFeedLinks()) {
            $flinks = $this->_writer->getFeedLinks();
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
        if (!$this->_writer->getId()) {
            $this->_writer->setId($this->_writer->getLink());
        }
        $id = $this->_dom->createElement('id');
        $root->appendChild($id);
        $id->nodeValue = $this->_writer->getId();
        // add all authors
        if ($this->_writer->getAuthors()) {
            $authors = $this->_writer->getAuthors();
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
        // append the entries we need!
        foreach ($this->_writer as $entry) {
            $builder = new Zend_Feed_Writer_Entry_Atom($entry, $this->_dom);
            $builder->build();
            $root->appendChild($builder->getElement());
        }
    }

    public function saveXml()
    {
        return $this->_dom->saveXml();
    }

}
