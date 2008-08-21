<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'Zend/Feed/Reader/Author.php';

class Zend_Feed_Reader_AuthorTest extends PHPUnit_Framework_TestCase
{
    protected $_author = null;

    public function setup() 
    {
        $this->_author = new Zend_Feed_Reader_Author('John', 'john@doe.com', 'http://www.example.com');
    }
    
    public function testAuthorName()
    {
        $this->assertEquals($this->_author->getAuthor(), 'John');
    }
    
    public function testAuthorEmail()
    {
        $this->assertEquals($this->_author->getEmail(), 'john@doe.com');
    }
    
    public function testAuthorUri()
    {
        $this->assertEquals($this->_author->getUri(), 'http://www.example.com');
    }
}