<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'Zend/Feed/Writer/Feed/Atom.php';

require_once 'Zend/Feed/Reader.php';
require_once 'Zend/Version.php';

class Zend_Feed_Writer_Feed_AtomTest extends PHPUnit_Framework_TestCase
{

    protected $_validWriter = null;

    public function setUp()
    {
        $this->_validWriter = new Zend_Feed_Writer;
        $this->_validWriter->setTitle('This is a test feed.');
        $this->_validWriter->setDescription('This is a test description.');
        $this->_validWriter->setDateModified(1234567890);
        // I intentionally omit setGenerator, setEncoding, setLanguage
        $this->_validWriter->setLink('http://www.example.com');
        $this->_validWriter->setFeedLink('http://www.example.com/atom', 'atom');
        $this->_validWriter->addAuthor('Joe', 'joe@example.com', 'http://www.example.com/joe');
    }

    public function tearDown()
    {
        $this->_validWriter = null;
    }

    // Tests standard Atom Feed elements

    public function testSetsWriterInConstructor()
    {
        $writer = new Zend_Feed_Writer;
        $feed = new Zend_Feed_Writer_Feed_Atom($writer);
        $this->assertTrue($feed->getWriter() instanceof Zend_Feed_Writer);
    }

    public function testBuildMethodRunsMinimalWriterContainerProperlyBeforeICheckAtomCompliance()
    {
        $feed = new Zend_Feed_Writer_Feed_Atom($this->_validWriter);
        try {
            $feed->build();
        } catch (Zend_Feed_Exception $e) {
            $this->fail('Valid Writer object caused an exception when building which should never happen');
        }
    }

    public function testFeedEncodingHasBeenSet()
    {
        $this->_validWriter->setEncoding('iso-8859-1');
        $atomFeed = new Zend_Feed_Writer_Feed_Atom($this->_validWriter);
        $atomFeed->build();
        $feed = Zend_Feed_Reader::importString($atomFeed->saveXml());
        $this->assertEquals('iso-8859-1', $feed->getEncoding());
    }

    public function testFeedEncodingDefaultIsUsedIfEncodingNotSetByHand()
    {
        $atomFeed = new Zend_Feed_Writer_Feed_Atom($this->_validWriter);
        $atomFeed->build();
        $feed = Zend_Feed_Reader::importString($atomFeed->saveXml());
        $this->assertEquals('utf-8', $feed->getEncoding());
    }

    public function testFeedTitleHasBeenSet()
    {
        $atomFeed = new Zend_Feed_Writer_Feed_Atom($this->_validWriter);
        $atomFeed->build();
        $feed = Zend_Feed_Reader::importString($atomFeed->saveXml());
        $this->assertEquals('This is a test feed.', $feed->getTitle());
    }

    public function testFeedSubtitleHasBeenSet()
    {
        $atomFeed = new Zend_Feed_Writer_Feed_Atom($this->_validWriter);
        $atomFeed->build();
        $feed = Zend_Feed_Reader::importString($atomFeed->saveXml());
        $this->assertEquals('This is a test description.', $feed->getDescription());
    }

    public function testFeedUpdatedDateHasBeenSet()
    {
        $atomFeed = new Zend_Feed_Writer_Feed_Atom($this->_validWriter);
        $atomFeed->build();
        $feed = Zend_Feed_Reader::importString($atomFeed->saveXml());
        $this->assertEquals(1234567890, $feed->getDateModified()->get(Zend_Date::TIMESTAMP));
    }

    public function testFeedGeneratorHasBeenSet()
    {
        $this->_validWriter->setGenerator('FooFeedBuilder', '1.00', 'http://www.example.com');
        $atomFeed = new Zend_Feed_Writer_Feed_Atom($this->_validWriter);
        $atomFeed->build();
        $feed = Zend_Feed_Reader::importString($atomFeed->saveXml());
        $this->assertEquals('FooFeedBuilder', $feed->getGenerator());
    }

    public function testFeedGeneratorDefaultIsUsedIfGeneratorNotSetByHand()
    {
        $atomFeed = new Zend_Feed_Writer_Feed_Atom($this->_validWriter);
        $atomFeed->build();
        $feed = Zend_Feed_Reader::importString($atomFeed->saveXml());
        $this->assertEquals('Zend_Feed_Writer', $feed->getGenerator());
    }

    public function testFeedLanguageHasBeenSet()
    {
        $this->_validWriter->setLanguage('fr');
        $atomFeed = new Zend_Feed_Writer_Feed_Atom($this->_validWriter);
        $atomFeed->build();
        $feed = Zend_Feed_Reader::importString($atomFeed->saveXml());
        $this->assertEquals('fr', $feed->getLanguage());
    }

    public function testFeedLanguageDefaultIsUsedIfGeneratorNotSetByHand()
    {
        $atomFeed = new Zend_Feed_Writer_Feed_Atom($this->_validWriter);
        $atomFeed->build();
        $feed = Zend_Feed_Reader::importString($atomFeed->saveXml());
        $this->assertEquals(null, $feed->getLanguage());
    }

    public function testFeedIncludesLinkToHtmlVersionOfFeed()
    {
        $atomFeed = new Zend_Feed_Writer_Feed_Atom($this->_validWriter);
        $atomFeed->build();
        $feed = Zend_Feed_Reader::importString($atomFeed->saveXml());
        $this->assertEquals('http://www.example.com', $feed->getLink());
    }

    public function testFeedIncludesLinkToXmlAtomWhereTheFeedWillBeAvailable()
    {
        $atomFeed = new Zend_Feed_Writer_Feed_Atom($this->_validWriter);
        $atomFeed->build();
        $feed = Zend_Feed_Reader::importString($atomFeed->saveXml());
        $this->assertEquals('http://www.example.com/atom', $feed->getFeedLink());
    }

    public function testFeedHoldsAnyAuthorAdded()
    {
        $atomFeed = new Zend_Feed_Writer_Feed_Atom($this->_validWriter);
        $atomFeed->build();
        $feed = Zend_Feed_Reader::importString($atomFeed->saveXml());
        $author = $feed->getAuthor();
        $this->assertEquals('joe@example.com (Joe)', $feed->getAuthor());
    }

    public function testFeedIdHasBeenSet()
    {
        $this->_validWriter->setId('urn:uuid:60a76c80-d399-11d9-b93C-0003939e0af6');
        $atomFeed = new Zend_Feed_Writer_Feed_Atom($this->_validWriter);
        $atomFeed->build();
        $feed = Zend_Feed_Reader::importString($atomFeed->saveXml());
        $this->assertEquals('urn:uuid:60a76c80-d399-11d9-b93C-0003939e0af6', $feed->getId());
    }

    public function testFeedIdDefaultIsUsedIfGeneratorNotSetByHand()
    {
        $atomFeed = new Zend_Feed_Writer_Feed_Atom($this->_validWriter);
        $atomFeed->build();
        $feed = Zend_Feed_Reader::importString($atomFeed->saveXml());
        $this->assertEquals($feed->getLink(), $feed->getId());
    }

}
