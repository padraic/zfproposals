<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'Zend/Feed/Writer/Feed/Atom.php';

require_once 'Zend/Feed/Reader.php';
require_once 'Zend/Version.php';

class Zend_Feed_Writer_Entry_AtomTest extends PHPUnit_Framework_TestCase
{

    protected $_validWriter = null;
    protected $_validEntry = null;

    public function setUp()
    {
        $this->_validWriter = new Zend_Feed_Writer;
        $this->_validWriter->setTitle('This is a test feed.');
        $this->_validWriter->setDescription('This is a test description.');
        $this->_validWriter->setDateModified(1234567890);
        $this->_validWriter->setLink('http://www.example.com');
        $this->_validWriter->setFeedLink('http://www.example.com/atom', 'atom');
        $this->_validWriter->addAuthor('Joe', 'joe@example.com', 'http://www.example.com/joe');
        $this->_validEntry = $this->_validWriter->addEntry();
        $this->_validEntry->setTitle('This is a test entry.');
        $this->_validEntry->setDescription('This is a test entry description.');
        $this->_validEntry->setDateModified(1234567890);
        $this->_validEntry->setDateCreated(1234567000);
        $this->_validEntry->setLink('http://www.example.com/1');
        $this->_validEntry->addAuthor('Jane', 'jane@example.com', 'http://www.example.com/jane');
        $this->_validEntry->setContent('This is test entry content.');
    }

    public function tearDown()
    {
        $this->_validWriter = null;
        $this->_validEntry = null;
    }

    // Tests standard Atom Feed elements

    public function testBuildMethodRunsMinimalWriterContainerProperlyBeforeICheckAtomCompliance()
    {
        $feed = new Zend_Feed_Writer_Feed_Atom($this->_validWriter);
        try {
            $feed->build();
        } catch (Zend_Feed_Exception $e) {
            $this->fail('Valid Writer object caused an exception when building which should never happen');
        }
    }

    public function testFeedTitleHasBeenSet()
    {
        $atomFeed = new Zend_Feed_Writer_Feed_Atom($this->_validWriter);
        $atomFeed->build();
        echo $atomFeed->saveXml(); exit;
        $feed = Zend_Feed_Reader::importString($atomFeed->saveXml());
        $entry = $feed->current();
        $this->assertEquals('This is a test entry.', $entry->getTitle());
    }

}
