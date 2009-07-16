<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'Zend/Feed/Writer.php';

class Zend_Feed_WriterTest extends PHPUnit_Framework_TestCase
{

    protected $_feedSamplePath = null;

    public function setup()
    {
        $this->_feedSamplePath = dirname(__FILE__) . '/Writer/_files';
    }

    public function testAddsAuthorName()
    {
        $writer = new Zend_Feed_Writer;
        $writer->addAuthor('Joe');
        $this->assertEquals(array('name'=>'Joe'), $writer->getAuthor());
    }

    public function testAddsAuthorEmail()
    {
        $writer = new Zend_Feed_Writer;
        $writer->addAuthor('Joe', 'joe@example.com');
        $this->assertEquals(array('name'=>'Joe', 'email' => 'joe@example.com'), $writer->getAuthor());
    }

    public function testAddsAuthorUri()
    {
        $writer = new Zend_Feed_Writer;
        $writer->addAuthor('Joe', null, 'http://www.example.com');
        $this->assertEquals(array('name'=>'Joe', 'uri' => 'http://www.example.com'), $writer->getAuthor());
    }

    public function testAddAuthorThrowsExceptionOnInvalidName()
    {
        $writer = new Zend_Feed_Writer;
        try {
            $writer->addAuthor('');
            $this->fail();
        } catch (Zend_Feed_Exception $e) {
        }
    }

    public function testAddAuthorThrowsExceptionOnInvalidEmail()
    {
        $writer = new Zend_Feed_Writer;
        try {
            $writer->addAuthor('Joe', '');
            $this->fail();
        } catch (Zend_Feed_Exception $e) {
        }
    }

    public function testAddAuthorThrowsExceptionOnInvalidUri()
    {
        $writer = new Zend_Feed_Writer;
        try {
            $writer->addAuthor('Joe', null, 'notauri');
            $this->fail();
        } catch (Zend_Feed_Exception $e) {
        }
    }

    public function testAddsAuthorNameFromArray()
    {
        $writer = new Zend_Feed_Writer;
        $writer->addAuthor(array('name'=>'Joe'));
        $this->assertEquals(array('name'=>'Joe'), $writer->getAuthor());
    }

    public function testAddsAuthorEmailFromArray()
    {
        $writer = new Zend_Feed_Writer;
        $writer->addAuthor(array('name'=>'Joe','email'=>'joe@example.com'));
        $this->assertEquals(array('name'=>'Joe', 'email' => 'joe@example.com'), $writer->getAuthor());
    }

    public function testAddsAuthorUriFromArray()
    {
        $writer = new Zend_Feed_Writer;
        $writer->addAuthor(array('name'=>'Joe','uri'=>'http://www.example.com'));
        $this->assertEquals(array('name'=>'Joe', 'uri' => 'http://www.example.com'), $writer->getAuthor());
    }

    public function testAddAuthorThrowsExceptionOnInvalidNameFromArray()
    {
        $writer = new Zend_Feed_Writer;
        try {
            $writer->addAuthor(array('name'=>''));
            $this->fail();
        } catch (Zend_Feed_Exception $e) {
        }
    }

    public function testAddAuthorThrowsExceptionOnInvalidEmailFromArray()
    {
        $writer = new Zend_Feed_Writer;
        try {
            $writer->addAuthor(array('name'=>'Joe','email'=>''));
            $this->fail();
        } catch (Zend_Feed_Exception $e) {
        }
    }

    public function testAddAuthorThrowsExceptionOnInvalidUriFromArray()
    {
        $writer = new Zend_Feed_Writer;
        try {
            $writer->addAuthor(array('name'=>'Joe','uri'=>'notauri'));
            $this->fail();
        } catch (Zend_Feed_Exception $e) {
        }
    }

    public function testAddAuthorThrowsExceptionIfNameOmittedFromArray()
    {
        $writer = new Zend_Feed_Writer;
        try {
            $writer->addAuthor(array('uri'=>'notauri'));
            $this->fail();
        } catch (Zend_Feed_Exception $e) {
        }
    }

    public function testAddsAuthorsFromArrayOfAuthors()
    {
        $writer = new Zend_Feed_Writer;
        $writer->addAuthors(array(
            array('name'=>'Joe','uri'=>'http://www.example.com'),
            array('name'=>'Jane','uri'=>'http://www.example.com')
        ));
        $this->assertEquals(array('name'=>'Jane', 'uri' => 'http://www.example.com'), $writer->getAuthor(1));
    }

    public function testSetsCopyright()
    {
        $writer = new Zend_Feed_Writer;
        $writer->setCopyright('Copyright (c) 2009 Paddy Brady');
        $this->assertEquals('Copyright (c) 2009 Paddy Brady', $writer->getCopyright());
    }

    public function testSetCopyrightThrowsExceptionOnInvalidParam()
    {
        $writer = new Zend_Feed_Writer;
        try {
            $writer->setCopyright('');
            $this->fail();
        } catch (Zend_Feed_Exception $e) {
        }
    }

    public function testSetDateCreatedDefaultsToCurrentTime()
    {
        $writer = new Zend_Feed_Writer;
        $writer->setDateCreated();
        $dateNow = new Zend_Date;
        $this->assertTrue($dateNow->isLater($writer->getDateCreated()) || $dateNow->equals($writer->getDateCreated()));
    }

    public function testSetDateCreatedUsesGivenUnixTimestamp()
    {
        $writer = new Zend_Feed_Writer;
        $writer->setDateCreated(1234567890);
        $myDate = new Zend_Date('1234567890', Zend_Date::TIMESTAMP);
        $this->assertTrue($myDate->equals($writer->getDateCreated()));
    }

    public function testSetDateCreatedUsesZendDateObject()
    {
        $writer = new Zend_Feed_Writer;
        $writer->setDateCreated(new Zend_Date('1234567890', Zend_Date::TIMESTAMP));
        $myDate = new Zend_Date('1234567890', Zend_Date::TIMESTAMP);
        $this->assertTrue($myDate->equals($writer->getDateCreated()));
    }

    public function testSetDateModifiedDefaultsToCurrentTime()
    {
        $writer = new Zend_Feed_Writer;
        $writer->setDateModified();
        $dateNow = new Zend_Date;
        $this->assertTrue($dateNow->isLater($writer->getDateModified()) || $dateNow->equals($writer->getDateModified()));
    }

    public function testSetDateModifiedUsesGivenUnixTimestamp()
    {
        $writer = new Zend_Feed_Writer;
        $writer->setDateModified(1234567890);
        $myDate = new Zend_Date('1234567890', Zend_Date::TIMESTAMP);
        $this->assertTrue($myDate->equals($writer->getDateModified()));
    }

    public function testSetDateModifiedUsesZendDateObject()
    {
        $writer = new Zend_Feed_Writer;
        $writer->setDateModified(new Zend_Date('1234567890', Zend_Date::TIMESTAMP));
        $myDate = new Zend_Date('1234567890', Zend_Date::TIMESTAMP);
        $this->assertTrue($myDate->equals($writer->getDateModified()));
    }

    public function testSetDateCreatedThrowsExceptionOnInvalidParameter()
    {
        $writer = new Zend_Feed_Writer;
        try {
            $writer->setDateCreated('abc');
            $this->fail();
        } catch (Zend_Feed_Exception $e) {
        }
    }

    public function testSetDateModifiedThrowsExceptionOnInvalidParameter()
    {
        $writer = new Zend_Feed_Writer;
        try {
            $writer->setDateModified('abc');
            $this->fail();
        } catch (Zend_Feed_Exception $e) {
        }
    }

    public function testGetDateCreatedReturnsNullIfDateNotSet()
    {
        $writer = new Zend_Feed_Writer;
        $this->assertTrue(is_null($writer->getDateCreated()));
    }

    public function testGetDateModifiedReturnsNullIfDateNotSet()
    {
        $writer = new Zend_Feed_Writer;
        $this->assertTrue(is_null($writer->getDateModified()));
    }

    public function testGetCopyrightReturnsNullIfDateNotSet()
    {
        $writer = new Zend_Feed_Writer;
        $this->assertTrue(is_null($writer->getCopyright()));
    }

    public function testSetsDescription()
    {
        $writer = new Zend_Feed_Writer;
        $writer->setDescription('abc');
        $this->assertEquals('abc', $writer->getDescription());
    }

    public function testSetDescriptionThrowsExceptionOnInvalidParameter()
    {
        $writer = new Zend_Feed_Writer;
        try {
            $writer->setDescription('');
            $this->fail();
        } catch (Zend_Feed_Exception $e) {
        }
    }

    public function testGetDescriptionReturnsNullIfDateNotSet()
    {
        $writer = new Zend_Feed_Writer;
        $this->assertTrue(is_null($writer->getDescription()));
    }

    public function testSetsId()
    {
        $writer = new Zend_Feed_Writer;
        $writer->setId('abc');
        $this->assertEquals('abc', $writer->getId());
    }

    public function testSetIdThrowsExceptionOnInvalidParameter()
    {
        $writer = new Zend_Feed_Writer;
        try {
            $writer->setId('');
            $this->fail();
        } catch (Zend_Feed_Exception $e) {
        }
    }

    public function testGetIdReturnsNullIfDateNotSet()
    {
        $writer = new Zend_Feed_Writer;
        $this->assertTrue(is_null($writer->getId()));
    }

    public function testSetsLanguage()
    {
        $writer = new Zend_Feed_Writer;
        $writer->setLanguage('abc');
        $this->assertEquals('abc', $writer->getLanguage());
    }

    public function testSetLanguageThrowsExceptionOnInvalidParameter()
    {
        $writer = new Zend_Feed_Writer;
        try {
            $writer->setLanguage('');
            $this->fail();
        } catch (Zend_Feed_Exception $e) {
        }
    }

    public function testGetLanguageReturnsNullIfDateNotSet()
    {
        $writer = new Zend_Feed_Writer;
        $this->assertTrue(is_null($writer->getLanguage()));
    }

    public function testSetsLink()
    {
        $writer = new Zend_Feed_Writer;
        $writer->setLink('http://www.example.com/id');
        $this->assertEquals('http://www.example.com/id', $writer->getLink());
    }

    public function testSetLinkThrowsExceptionOnEmptyString()
    {
        $writer = new Zend_Feed_Writer;
        try {
            $writer->setLink('');
            $this->fail();
        } catch (Zend_Feed_Exception $e) {
        }
    }

    public function testSetLinkThrowsExceptionOnInvalidUri()
    {
        $writer = new Zend_Feed_Writer;
        try {
            $writer->setLink('http://');
            $this->fail();
        } catch (Zend_Feed_Exception $e) {
        }
    }

    public function testGetLinkReturnsNullIfDateNotSet()
    {
        $writer = new Zend_Feed_Writer;
        $this->assertTrue(is_null($writer->getLink()));
    }

    public function testSetsEncoding()
    {
        $writer = new Zend_Feed_Writer;
        $writer->setEncoding('utf-16');
        $this->assertEquals('utf-16', $writer->getEncoding());
    }

    public function testSetEncodingThrowsExceptionOnInvalidParameter()
    {
        $writer = new Zend_Feed_Writer;
        try {
            $writer->setEncoding('');
            $this->fail();
        } catch (Zend_Feed_Exception $e) {
        }
    }

    public function testGetEncodingReturnsNullIfDateNotSet()
    {
        $writer = new Zend_Feed_Writer;
        $this->assertTrue(is_null($writer->getEncoding()));
    }

    public function testSetsTitle()
    {
        $writer = new Zend_Feed_Writer;
        $writer->setTitle('abc');
        $this->assertEquals('abc', $writer->getTitle());
    }

    public function testSetTitleThrowsExceptionOnInvalidParameter()
    {
        $writer = new Zend_Feed_Writer;
        try {
            $writer->setTitle('');
            $this->fail();
        } catch (Zend_Feed_Exception $e) {
        }
    }

    public function testGetTitleReturnsNullIfDateNotSet()
    {
        $writer = new Zend_Feed_Writer;
        $this->assertTrue(is_null($writer->getTitle()));
    }

    public function testSetsGeneratorName()
    {
        $writer = new Zend_Feed_Writer;
        $writer->setGenerator('ZFW');
        $this->assertEquals(array('name'=>'ZFW'), $writer->getGenerator());
    }

    public function testSetsGeneratorVersion()
    {
        $writer = new Zend_Feed_Writer;
        $writer->setGenerator('ZFW', '1.0');
        $this->assertEquals(array('name'=>'ZFW', 'version' => '1.0'), $writer->getGenerator());
    }

    public function testSetsGeneratorUri()
    {
        $writer = new Zend_Feed_Writer;
        $writer->setGenerator('ZFW', null, 'http://www.example.com');
        $this->assertEquals(array('name'=>'ZFW', 'uri' => 'http://www.example.com'), $writer->getGenerator());
    }

    public function testSetsGeneratorThrowsExceptionOnInvalidName()
    {
        $writer = new Zend_Feed_Writer;
        try {
            $writer->setGenerator('');
            $this->fail();
        } catch (Zend_Feed_Exception $e) {
        }
    }

    public function testSetsGeneratorThrowsExceptionOnInvalidVersion()
    {
        $writer = new Zend_Feed_Writer;
        try {
            $writer->addAuthor('ZFW', '');
            $this->fail();
        } catch (Zend_Feed_Exception $e) {
        }
    }

    public function testSetsGeneratorThrowsExceptionOnInvalidUri()
    {
        $writer = new Zend_Feed_Writer;
        try {
            $writer->setGenerator('ZFW', null, 'notauri');
            $this->fail();
        } catch (Zend_Feed_Exception $e) {
        }
    }

    public function testGetGeneratorReturnsNullIfDateNotSet()
    {
        $writer = new Zend_Feed_Writer;
        $this->assertTrue(is_null($writer->getGenerator()));
    }

    public function testSetsFeedLink()
    {
        $writer = new Zend_Feed_Writer;
        $writer->setFeedLink('http://www.example.com/rss', 'RSS');
        $this->assertEquals(array('rss'=>'http://www.example.com/rss'), $writer->getFeedLinks());
    }

    public function testSetsFeedLinkThrowsExceptionOnInvalidType()
    {
        $writer = new Zend_Feed_Writer;
        try {
            $writer->setFeedLink('http://www.example.com/rss', 'abc');
            $this->fail();
        } catch (Zend_Feed_Exception $e) {
        }
    }

    public function testSetsFeedLinkThrowsExceptionOnInvalidUri()
    {
        $writer = new Zend_Feed_Writer;
        try {
            $writer->setFeedLink('http://', 'rss');
            $this->fail();
        } catch (Zend_Feed_Exception $e) {
        }
    }

    public function testGetFeedLinksReturnsNullIfDateNotSet()
    {
        $writer = new Zend_Feed_Writer;
        $this->assertTrue(is_null($writer->getFeedLinks()));
    }

}
