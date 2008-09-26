<?php

/**
 * @copyright 2008 Jurriën Stutterheim
 */

interface Zend_Feed_Reader_Feed_Interface
{
    public function getAuthors();

    public function getAuthor($index = 0);

    public function getCopyright();

    public function getDateCreated();
    
    public function getDateModified();
    
    public function getDescription();

    public function getGenerator();
    
    public function getId();
    
    public function getLanguage();

    public function getLink();

    public function getTitle();
}