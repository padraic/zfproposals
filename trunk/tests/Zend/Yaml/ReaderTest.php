<?php

require_once 'Zend/Yaml/Parser/Reader.php';
require_once 'PHPUnit/Framework/TestCase.php';

class Zend_Yaml_ReaderTest extends PHPUnit_Framework_TestCase 
{

    public function testRead()
    {
        $reader = new Zend_Yaml_Parser_Reader('abcde');
        $this->assertEquals('a', $reader->read());
        $this->assertEquals('b', $reader->read());
        $this->assertEquals('c', $reader->read());
        $this->assertEquals('d', $reader->read());
        $this->assertEquals('e', $reader->read());
        $this->assertEquals(-1, $reader->read());
    }

    public function testUnread()
    {
        $reader = new Zend_Yaml_Parser_Reader('abcde');
        $this->assertEquals('a', $reader->read());
        $reader->unread();
        $this->assertEquals('a', $reader->read());
        $reader->unread();
        $reader->unread();
        $this->assertEquals('a', $reader->read());
    }

    public function testCurrent()
    {
        $reader = new Zend_Yaml_Parser_Reader('abcde');
        $this->assertEquals('a', $reader->current());
        $reader->read();
        $this->assertEquals('a', $reader->current());
    }

    public function testPrevious()
    {
        $reader = new Zend_Yaml_Parser_Reader('abcde');
        $reader->read(); // a
        $reader->read(); // b
        $this->assertEquals('a', $reader->previous());
        $reader->unread();
        $this->assertEquals(false, $reader->previous());
    }

    public function testMark()
    {
        $reader = new Zend_Yaml_Parser_Reader('abcde');
        $reader->read();
        $reader->mark();
        $reader->read();
        $reader->read();
        $this->assertEquals('bc', $reader->toString());
    }

    public function testReset()
    {
        $reader = new Zend_Yaml_Parser_Reader('abcde');
        $reader->mark(); // index 0
        $reader->read();
        $reader->read();
        $this->assertEquals('ab', $reader->toString());
        $reader->reset();
        $this->assertEquals('', $reader->toString());
        $this->assertEquals('a', $reader->read());
    }

    public function testToString()
    {
        $reader = new Zend_Yaml_Parser_Reader('abcde');
        $reader->mark();
        $reader->read();
        $reader->read();
        $this->assertEquals('ab', (string) $reader);
    }

}