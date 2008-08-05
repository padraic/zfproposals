<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'Zend/Feed/Reader.php';

class Zend_Feed_Reader_Entry_RssTest extends PHPUnit_Framework_TestCase
{

    protected $_feedSamplePath = null;

    public function setup()
    {
        $this->_feedSamplePath = dirname(__FILE__) . '/_files/Rss';
    }

    /**
     * Get Id (Unencoded Text)
     */
    public function testGetsIdFromRss20()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/rss20.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('http://www.example.com/1', $entry->getId());
    }

    public function testGetsIdFromRss094()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/rss094.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getId());
    }

    public function testGetsIdFromRss093()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/rss093.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getId());
    }

    public function testGetsIdFromRss092()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/rss092.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getId());
    }

    public function testGetsIdFromRss091()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/rss091.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getId());
    }

    public function testGetsIdFromRss10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/rss10.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getId());
    }

    public function testGetsIdFromRss090()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/rss090.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getId());
    }

    // DC 1.0

    public function testGetsIdFromRss20_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/dc10/rss20.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('http://www.example.com/1', $entry->getId());
    }

    public function testGetsIdFromRss094_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/dc10/rss094.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('http://www.example.com/1', $entry->getId());
    }

    public function testGetsIdFromRss093_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/dc10/rss093.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('http://www.example.com/1', $entry->getId());
    }

    public function testGetsIdFromRss092_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/dc10/rss092.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('http://www.example.com/1', $entry->getId());
    }

    public function testGetsIdFromRss091_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/dc10/rss091.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('http://www.example.com/1', $entry->getId());
    }

    public function testGetsIdFromRss10_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/dc10/rss10.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('http://www.example.com/1', $entry->getId());
    }

    public function testGetsIdFromRss090_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/dc10/rss090.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('http://www.example.com/1', $entry->getId());
    }

    // DC 1.1

    public function testGetsIdFromRss20_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/dc11/rss20.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('http://www.example.com/1', $entry->getId());
    }

    public function testGetsIdFromRss094_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/dc11/rss094.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('http://www.example.com/1', $entry->getId());
    }

    public function testGetsIdFromRss093_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/dc11/rss093.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('http://www.example.com/1', $entry->getId());
    }

    public function testGetsIdFromRss092_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/dc11/rss092.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('http://www.example.com/1', $entry->getId());
    }

    public function testGetsIdFromRss091_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/dc11/rss091.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('http://www.example.com/1', $entry->getId());
    }

    public function testGetsIdFromRss10_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/dc11/rss10.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('http://www.example.com/1', $entry->getId());
    }

    public function testGetsIdFromRss090_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/dc11/rss090.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('http://www.example.com/1', $entry->getId());
    }

    // Missing Id

    public function testGetsIdFromRss20_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/none/rss20.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getId());
    }

    public function testGetsIdFromRss094_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/none/rss094.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getId());
    }

    public function testGetsIdFromRss093_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/none/rss093.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getId());
    }

    public function testGetsIdFromRss092_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/none/rss092.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getId());
    }

    public function testGetsIdFromRss091_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/none/rss091.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getId());
    }

    public function testGetsIdFromRss10_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/none/rss10.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getId());
    }

    public function testGetsIdFromRss090_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/id/plain/none/rss090.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getId());
    }

    /**
     * Get Title (Unencoded Text)
     */
    public function testGetsTitleFromRss20()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/rss20.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Entry Title', $entry->getTitle());
    }

    public function testGetsTitleFromRss094()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/rss094.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Entry Title', $entry->getTitle());
    }

    public function testGetsTitleFromRss093()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/rss093.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Entry Title', $entry->getTitle());
    }

    public function testGetsTitleFromRss092()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/rss092.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Entry Title', $entry->getTitle());
    }

    public function testGetsTitleFromRss091()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/rss091.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Entry Title', $entry->getTitle());
    }

    public function testGetsTitleFromRss10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/rss10.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Entry Title', $entry->getTitle());
    }

    public function testGetsTitleFromRss090()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/rss090.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Entry Title', $entry->getTitle());
    }

    // DC 1.0

    public function testGetsTitleFromRss20_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc10/rss20.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Entry Title', $entry->getTitle());
    }

    public function testGetsTitleFromRss094_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc10/rss094.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Entry Title', $entry->getTitle());
    }

    public function testGetsTitleFromRss093_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc10/rss093.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Entry Title', $entry->getTitle());
    }

    public function testGetsTitleFromRss092_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc10/rss092.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Entry Title', $entry->getTitle());
    }

    public function testGetsTitleFromRss091_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc10/rss091.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Entry Title', $entry->getTitle());
    }

    public function testGetsTitleFromRss10_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc10/rss10.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Entry Title', $entry->getTitle());
    }

    public function testGetsTitleFromRss090_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc10/rss090.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Entry Title', $entry->getTitle());
    }

    // DC 1.1

    public function testGetsTitleFromRss20_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc11/rss20.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Entry Title', $entry->getTitle());
    }

    public function testGetsTitleFromRss094_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc11/rss094.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Entry Title', $entry->getTitle());
    }

    public function testGetsTitleFromRss093_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc11/rss093.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Entry Title', $entry->getTitle());
    }

    public function testGetsTitleFromRss092_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc11/rss092.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Entry Title', $entry->getTitle());
    }

    public function testGetsTitleFromRss091_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc11/rss091.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Entry Title', $entry->getTitle());
    }

    public function testGetsTitleFromRss10_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc11/rss10.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Entry Title', $entry->getTitle());
    }

    public function testGetsTitleFromRss090_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/dc11/rss090.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Entry Title', $entry->getTitle());
    }

    // Missing Id

    public function testGetsTitleFromRss20_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/none/rss20.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getTitle());
    }

    public function testGetsTitleFromRss094_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/none/rss094.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getTitle());
    }

    public function testGetsTitleFromRss093_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/none/rss093.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getTitle());
    }

    public function testGetsTitleFromRss092_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/none/rss092.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getTitle());
    }

    public function testGetsTitleFromRss091_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/none/rss091.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getTitle());
    }

    public function testGetsTitleFromRss10_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/none/rss10.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getTitle());
    }

    public function testGetsTitleFromRss090_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/plain/none/rss090.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getTitle());
    }

    /**
     * Get Authors (Unencoded Text)
     */
    public function testGetsAuthorsFromRss20()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/rss20.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array('Joe Bloggs','Jane Bloggs'), $entry->getAuthors());
    }

    public function testGetsAuthorsFromRss094()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/rss094.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array(), $entry->getAuthors());
    }

    public function testGetsAuthorsFromRss093()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/rss093.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array(), $entry->getAuthors());
    }

    public function testGetsAuthorsFromRss092()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/rss092.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array(), $entry->getAuthors());
    }

    public function testGetsAuthorsFromRss091()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/rss091.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array(), $entry->getAuthors());
    }

    public function testGetsAuthorsFromRss10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/rss10.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array(), $entry->getAuthors());
    }

    public function testGetsAuthorsFromRss090()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/rss090.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array(), $entry->getAuthors());
    }

    // DC 1.0

    public function testGetsAuthorsFromRss20_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc10/rss20.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array('Joe Bloggs','Jane Bloggs'), $entry->getAuthors());
    }

    public function testGetsAuthorsFromRss094_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc10/rss094.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array('Joe Bloggs','Jane Bloggs'), $entry->getAuthors());
    }

    public function testGetsAuthorsFromRss093_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc10/rss093.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array('Joe Bloggs','Jane Bloggs'), $entry->getAuthors());
    }

    public function testGetsAuthorsFromRss092_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc10/rss092.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array('Joe Bloggs','Jane Bloggs'), $entry->getAuthors());
    }

    public function testGetsAuthorsFromRss091_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc10/rss091.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array('Joe Bloggs','Jane Bloggs'), $entry->getAuthors());
    }

    public function testGetsAuthorsFromRss10_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc10/rss10.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array('Joe Bloggs','Jane Bloggs'), $entry->getAuthors());
    }

    public function testGetsAuthorsFromRss090_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc10/rss090.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array('Joe Bloggs','Jane Bloggs'), $entry->getAuthors());
    }

    // DC 1.1

    public function testGetsAuthorsFromRss20_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc11/rss20.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array('Joe Bloggs','Jane Bloggs'), $entry->getAuthors());
    }

    public function testGetsAuthorsFromRss094_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc11/rss094.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array('Joe Bloggs','Jane Bloggs'), $entry->getAuthors());
    }

    public function testGetsAuthorsFromRss093_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc11/rss093.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array('Joe Bloggs','Jane Bloggs'), $entry->getAuthors());
    }

    public function testGetsAuthorsFromRss092_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc11/rss092.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array('Joe Bloggs','Jane Bloggs'), $entry->getAuthors());
    }

    public function testGetsAuthorsFromRss091_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc11/rss091.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array('Joe Bloggs','Jane Bloggs'), $entry->getAuthors());
    }

    public function testGetsAuthorsFromRss10_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc11/rss10.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array('Joe Bloggs','Jane Bloggs'), $entry->getAuthors());
    }

    public function testGetsAuthorsFromRss090_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc11/rss090.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array('Joe Bloggs','Jane Bloggs'), $entry->getAuthors());
    }

    // Missing Id

    public function testGetsAuthorsFromRss20_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/none/rss20.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array(), $entry->getAuthors());
    }

    public function testGetsAuthorsFromRss094_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/none/rss094.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array(), $entry->getAuthors());
    }

    public function testGetsAuthorsFromRss093_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/none/rss093.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array(), $entry->getAuthors());
    }

    public function testGetsAuthorsFromRss092_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/none/rss092.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array(), $entry->getAuthors());
    }

    public function testGetsAuthorsFromRss091_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/none/rss091.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array(), $entry->getAuthors());
    }

    public function testGetsAuthorsFromRss10_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/none/rss10.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array(), $entry->getAuthors());
    }

    public function testGetsAuthorsFromRss090_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/none/rss090.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(array(), $entry->getAuthors());
    }


    /**
     * Get Author (Unencoded Text)
     */
    public function testGetsAuthorFromRss20()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/rss20.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Joe Bloggs', $entry->getAuthor());
    }

    public function testGetsAuthorFromRss094()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/rss094.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getAuthor());
    }

    public function testGetsAuthorFromRss093()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/rss093.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getAuthor());
    }

    public function testGetsAuthorFromRss092()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/rss092.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getAuthor());
    }

    public function testGetsAuthorFromRss091()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/rss091.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getAuthor());
    }

    public function testGetsAuthorFromRss10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/rss10.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getAuthor());
    }

    public function testGetsAuthorFromRss090()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/rss090.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getAuthor());
    }

    // DC 1.0

    public function testGetsAuthorFromRss20_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc10/rss20.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Joe Bloggs', $entry->getAuthor());
    }

    public function testGetsAuthorFromRss094_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc10/rss094.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Jane Bloggs', $entry->getAuthor(1));
    }

    public function testGetsAuthorFromRss093_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc10/rss093.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Joe Bloggs', $entry->getAuthor());
    }

    public function testGetsAuthorFromRss092_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc10/rss092.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Jane Bloggs', $entry->getAuthor(1));
    }

    public function testGetsAuthorFromRss091_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc10/rss091.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Joe Bloggs', $entry->getAuthor());
    }

    public function testGetsAuthorFromRss10_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc10/rss10.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Jane Bloggs', $entry->getAuthor(1));
    }

    public function testGetsAuthorFromRss090_Dc10()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc10/rss090.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Joe Bloggs', $entry->getAuthor());
    }

    // DC 1.1

    public function testGetsAuthorFromRss20_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc11/rss20.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Jane Bloggs', $entry->getAuthor(1));
    }

    public function testGetsAuthorFromRss094_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc11/rss094.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Joe Bloggs', $entry->getAuthor());
    }

    public function testGetsAuthorFromRss093_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc11/rss093.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Jane Bloggs', $entry->getAuthor(1));
    }

    public function testGetsAuthorFromRss092_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc11/rss092.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Joe Bloggs', $entry->getAuthor());
    }

    public function testGetsAuthorFromRss091_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc11/rss091.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Jane Bloggs', $entry->getAuthor(1));
    }

    public function testGetsAuthorFromRss10_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc11/rss10.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Joe Bloggs', $entry->getAuthor());
    }

    public function testGetsAuthorFromRss090_Dc11()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/dc11/rss090.xml')
        );
        $entry = $feed->current();
        $this->assertEquals('Jane Bloggs', $entry->getAuthor(1));
    }

    // Missing Id

    public function testGetsAuthorFromRss20_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/none/rss20.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getAuthor());
    }

    public function testGetsAuthorFromRss094_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/none/rss094.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getAuthor());
    }

    public function testGetsAuthorFromRss093_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/none/rss093.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getAuthor());
    }

    public function testGetsAuthorFromRss092_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/none/rss092.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getAuthor());
    }

    public function testGetsAuthorFromRss091_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/none/rss091.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getAuthor());
    }

    public function testGetsAuthorFromRss10_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/none/rss10.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getAuthor());
    }

    public function testGetsAuthorFromRss090_None()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/author/plain/none/rss090.xml')
        );
        $entry = $feed->current();
        $this->assertEquals(null, $entry->getAuthor());
    }

}