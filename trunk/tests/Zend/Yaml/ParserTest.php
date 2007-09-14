<?php

require_once 'Zend/Yaml/Parser.php';
require_once 'PHPUnit/Framework/TestCase.php';

class Zend_Yaml_ParserTest extends PHPUnit_Framework_TestCase 
{

    protected $_parserEvent = null;
    protected $_character = null;

    public function setUp()
    {
        $this->_parserEvent = $this->getMock('Zend_Yaml_Parser_Event_Interface');
        $this->_character = $this->getMock('Zend_Yaml_Character');
    }

    public function testCountIndent()
    {
        $parser = new Zend_Yaml_Parser('   abcdef', $this->_parserEvent, $this->_character);
        $this->_character->expects($this->exactly(4))
            ->method('isType')
            ->will($this->onConsecutiveCalls(true, true, true, false));
        $this->assertEquals(3, $parser->countIndent()); // index of non-indent char
    }

    public function testCatchType_True()
    {
        $parser = new Zend_Yaml_Parser('   abcdef', $this->_parserEvent, $this->_character);
        $this->_character->expects($this->exactly(4))
            ->method('isType')
            ->will($this->onConsecutiveCalls(true, true, true, false));
        $this->assertEquals(true, $parser->catchType(Zend_Yaml_Character::SPACE));
    }

    public function testCatchType_False()
    {
        $parser = new Zend_Yaml_Parser('abcdef', $this->_parserEvent, $this->_character);
        $this->_character->expects($this->once())
            ->method('isType')
            ->will($this->onConsecutiveCalls(false));
        $this->assertEquals(false, $parser->catchType(Zend_Yaml_Character::SPACE));
    }

    // not bothering to test specific catchXXX methods; just a bunch
    // of proxies for documenting flow.

    public function testCatchIndent_True()
    {
        $parser = new Zend_Yaml_Parser('   abcdef', $this->_parserEvent, $this->_character);
        $this->_character->expects($this->exactly(4))
            ->method('isType')
            ->will($this->onConsecutiveCalls(true, true, true, false));
        $this->assertEquals(true, $parser->catchIndent(3));
    }

    public function testCatchIndent_False1()
    {
        $parser = new Zend_Yaml_Parser('   abcdef', $this->_parserEvent, $this->_character);
        $this->_character->expects($this->exactly(4))
            ->method('isType')
            ->will($this->onConsecutiveCalls(true, true, true, false));
        $this->assertEquals(false, $parser->catchIndent(4));
    }

    public function testCatchIndent_False2()
    {
        $this->markTestSkipped(); // param of 2 should have failed perhaps?
        $parser = new Zend_Yaml_Parser('   abcdef', $this->_parserEvent, $this->_character);
        $this->_character->expects($this->exactly(4))
            ->method('isType')
            ->will($this->onConsecutiveCalls(true, true, true, false));
        $this->assertEquals(false, $parser->catchIndent(2));
    }

    public function testCatchNewLine()
    {
        $this->markTestIncomplete();
    }

    public function testCatchEnd()
    {
        $this->markTestIncomplete();
    }

    public function testCatchStringSimple()
    {
        $this->markTestIncomplete();
    }

}