<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'Zend/Feed/Reader.php';

class Zend_Feed_Reader_Feed_RssTest extends PHPUnit_Framework_TestCase
{

    protected $_feedSamplePath = null;

    public function setup()
    {
        $this->_feedSamplePath = dirname(__FILE__) . '/_files/Rss';
    }

    /**
     * Get Title (Unencoded Text)
     */
    public function testGetsTitleFromRss20()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/rss20.xml')
        );
        $this->assertEquals('My Title', $feed->getTitle());
    }

    public function testGetsTitleFromRss094()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/rss094.xml')
        );
        $this->assertEquals('My Title', $feed->getTitle());
    }

    public function testGetsTitleFromRss093()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/rss093.xml')
        );
        $this->assertEquals('My Title', $feed->getTitle());
    }

    public function testGetsTitleFromRss092()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/rss092.xml')
        );
        $this->assertEquals('My Title', $feed->getTitle());
    }

    public function testGetsTitleFromRss091()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/rss091.xml')
        );
        $this->assertEquals('My Title', $feed->getTitle());
    }

    public function testGetsTitleFromRss10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/rss10.xml')
        );
        $this->assertEquals('My Title', $feed->getTitle());
    }

    public function testGetsTitleFromRss090()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/rss090.xml')
        );
        $this->assertEquals('My Title', $feed->getTitle());
    }

    // DC 1.0

    public function testGetsTitleFromRss20_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc10/rss20.xml')
        );
        $this->assertEquals('My Title', $feed->getTitle());
    }

    public function testGetsTitleFromRss094_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc10/rss094.xml')
        );
        $this->assertEquals('My Title', $feed->getTitle());
    }

    public function testGetsTitleFromRss093_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc10/rss093.xml')
        );
        $this->assertEquals('My Title', $feed->getTitle());
    }

    public function testGetsTitleFromRss092_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc10/rss092.xml')
        );
        $this->assertEquals('My Title', $feed->getTitle());
    }

    public function testGetsTitleFromRss091_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc10/rss091.xml')
        );
        $this->assertEquals('My Title', $feed->getTitle());
    }

    public function testGetsTitleFromRss10_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc10/rss10.xml')
        );
        $this->assertEquals('My Title', $feed->getTitle());
    }

    public function testGetsTitleFromRss090_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc10/rss090.xml')
        );
        $this->assertEquals('My Title', $feed->getTitle());
    }

    // DC 1.1

    public function testGetsTitleFromRss20_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc11/rss20.xml')
        );
        $this->assertEquals('My Title', $feed->getTitle());
    }

    public function testGetsTitleFromRss094_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc11/rss094.xml')
        );
        $this->assertEquals('My Title', $feed->getTitle());
    }

    public function testGetsTitleFromRss093_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc11/rss093.xml')
        );
        $this->assertEquals('My Title', $feed->getTitle());
    }

    public function testGetsTitleFromRss092_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc11/rss092.xml')
        );
        $this->assertEquals('My Title', $feed->getTitle());
    }

    public function testGetsTitleFromRss091_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc11/rss091.xml')
        );
        $this->assertEquals('My Title', $feed->getTitle());
    }

    public function testGetsTitleFromRss10_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc11/rss10.xml')
        );
        $this->assertEquals('My Title', $feed->getTitle());
    }

    public function testGetsTitleFromRss090_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc11/rss090.xml')
        );
        $this->assertEquals('My Title', $feed->getTitle());
    }

    // Missing Title

    public function testGetsTitleFromRss20_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/none/rss20.xml')
        );
        $this->assertEquals(null, $feed->getTitle());
    }

    public function testGetsTitleFromRss094_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/none/rss094.xml')
        );
        $this->assertEquals(null, $feed->getTitle());
    }

    public function testGetsTitleFromRss093_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/none/rss093.xml')
        );
        $this->assertEquals(null, $feed->getTitle());
    }

    public function testGetsTitleFromRss092_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/none/rss092.xml')
        );
        $this->assertEquals(null, $feed->getTitle());
    }

    public function testGetsTitleFromRss091_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/none/rss091.xml')
        );
        $this->assertEquals(null, $feed->getTitle());
    }

    public function testGetsTitleFromRss10_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/none/rss10.xml')
        );
        $this->assertEquals(null, $feed->getTitle());
    }

    public function testGetsTitleFromRss090_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/none/rss090.xml')
        );
        $this->assertEquals(null, $feed->getTitle());
    }

}