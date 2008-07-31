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
     * Get Title
     */
    public function testGetsTitleFromRss20()
    {
        $feed = Zend_Feed_Reader::importString(
            file_get_contents($this->_feedSamplePath.'/title/rss20-title-plain.xml')
        );
        $this->assertEquals('My Title', $feed->getTitle());
    }

}