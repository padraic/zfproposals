<?php
/**
 * Interpretive class for Zend_Feed which interprets incoming
 * Zend_Feed_Abstract objects and presents a common unified API for all RSS
 * and Atom versions.
 * Or will...when it's been completed ;).
 *
 * @copyright 2008 Jurriën Stutterheim
 */
class Zend_Feed_Reader_Author
{
    protected $_author = null;
    
    protected $_email = null;
    
    protected $_uri = null;
    
    public function __construct($author = null, $email = null, $uri = null)
    {
        $this->_author = $author;
        $this->_email  = $email;
        $this->_uri    = $uri;
    }
    
    public function getAuthor()
    {
        return $this->_author;
    }
    
    public function getEmail()
    {
        return $this->_email;
    }
    
    public function getUri()
    {
        return $this->_uri;
    }
}