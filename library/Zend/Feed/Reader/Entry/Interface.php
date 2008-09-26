<?php

/**
 * @copyright 2008 Jurriën Stutterheim
 */

interface Zend_Feed_Reader_Entry_Interface
{
    public function __construct(Zend_Feed_Entry_Abstract $entry, $entryKey, $type = null);

    public function setXpath(DOMXPath $xpath);

    public function getAuthors();

    public function getAuthor($index = 0);

    public function getContent();
    
    public function getDateCreated();
    
    public function getDateModified();
    
    public function getDescription();

    public function getId();

    public function getLink($index = 0);

    public function getLinks();
    
    public function getPermalink();

    public function getTitle();

    public function getType();

    public function toArray();

    public function getDomDocument();
}