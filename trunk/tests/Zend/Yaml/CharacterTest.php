
<?php

require_once 'Zend/Yaml/Character.php';
require_once 'PHPUnit/Framework/TestCase.php';

class Zend_Yaml_CharacterTest extends PHPUnit_Framework_TestCase 
{

    public function testIsPrintable()
    {
        $char = new Zend_Yaml_Character;
        $this->assertEquals(true, $char->isPrintable(chr(0x20)));
        $this->assertEquals(true, $char->isPrintable(chr(0x7e)));
        $this->assertEquals(true, $char->isPrintable("\t"));
        $this->assertEquals(true, $char->isPrintable("\n"));
        $this->assertEquals(true, $char->isPrintable("\r"));
        $this->assertEquals(true, $char->isPrintable(chr(0x85)));
    }

    public function testIsLine()
    {
        $char = new Zend_Yaml_Character;
        $this->assertEquals(false, $char->isLine("\t"));
        $this->assertEquals(false, $char->isLine("\n"));
        $this->assertEquals(false, $char->isLine("\r"));
        $this->assertEquals(false, $char->isLine(chr(0x20)));
        $this->assertEquals(false, $char->isLine(chr(0x85)));
    }

    public function testIsLineSpace()
    {
        $char = new Zend_Yaml_Character;
        $this->assertEquals(true, $char->isLineSpace(chr(10)));
        $this->assertEquals(true, $char->isLineSpace(chr(13)));
        $this->assertEquals(true, $char->isLineSpace(chr(0x85)));
    }

    public function testIsWord()
    {
        $char = new Zend_Yaml_Character;

        $pair1a = chr(0x41);
        $pair1b = chr(0x5a);
        $this->assertEquals(true, $char->isWord($pair1a));
        $this->assertEquals(true, $char->isWord($pair1b));

        $pair2a = chr(0x61);
        $pair2b = chr(0x7a);
        $this->assertEquals(true, $char->isWord($pair2a));
        $this->assertEquals(true, $char->isWord($pair2b));

        $pair3a = chr(0x30);
        $pair3b = chr(0x39);
        $this->assertEquals(true, $char->isWord($pair2a));
        $this->assertEquals(true, $char->isWord($pair2b));
        
        $this->assertEquals(true, $char->isWord('-'));
    }

    public function testIsSpace()
    {
        $char = new Zend_Yaml_Character;
        $this->assertEquals(true, $char->isSpace(" "));
        $this->assertEquals(true, $char->isSpace("\t"));
    }

    public function testIsLineBreak()
    {
        $char = new Zend_Yaml_Character;
        $this->assertEquals(true, $char->isLineBreak("\n"));
        $this->assertEquals(true, $char->isLineBreak("\r"));
    }

    public function testIsIndent()
    {
        $char = new Zend_Yaml_Character;
        $this->assertEquals(true, $char->isIndent(" "));
    }

    public function testIsIndicator()
    {
        $char = new Zend_Yaml_Character;
        $this->assertEquals(true, $char->isIndicator("-"));
        $this->assertEquals(true, $char->isIndicator(":"));
        $this->assertEquals(true, $char->isIndicator("{"));
        $this->assertEquals(true, $char->isIndicator("}"));
        $this->assertEquals(true, $char->isIndicator("["));
        $this->assertEquals(true, $char->isIndicator("]"));
        $this->assertEquals(true, $char->isIndicator(","));
        $this->assertEquals(true, $char->isIndicator("?"));
        $this->assertEquals(true, $char->isIndicator("*"));
        $this->assertEquals(true, $char->isIndicator("&"));
        $this->assertEquals(true, $char->isIndicator("!"));
        $this->assertEquals(true, $char->isIndicator("|"));
        $this->assertEquals(true, $char->isIndicator("#"));
        $this->assertEquals(true, $char->isIndicator("@"));
        $this->assertEquals(true, $char->isIndicator("%"));
        $this->assertEquals(true, $char->isIndicator("^"));
        $this->assertEquals(true, $char->isIndicator("'"));
        $this->assertEquals(true, $char->isIndicator("\""));

        $this->assertEquals(false, $char->isIndicator('+'));
    }

    public function testIsIndicatorSpace()
    {
        $char = new Zend_Yaml_Character;
        $this->assertEquals(true, $char->isIndicatorSpace("-"));
        $this->assertEquals(true, $char->isIndicatorSpace(":"));

        $this->assertEquals(false, $char->isIndicatorSpace('+'));
    }

    public function testIsIndicatorInline()
    {
        $char = new Zend_Yaml_Character;
        $this->assertEquals(true, $char->isIndicatorInline(","));
        $this->assertEquals(true, $char->isIndicatorInline("{"));
        $this->assertEquals(true, $char->isIndicatorInline("}"));
        $this->assertEquals(true, $char->isIndicatorInline("["));
        $this->assertEquals(true, $char->isIndicatorInline("]"));

        $this->assertEquals(false, $char->isIndicatorInline('+'));
    }

    public function testIsIndicatorNonSpace()
    {
        $char = new Zend_Yaml_Character;
        $this->assertEquals(true, $char->isIndicatorNonSpace("?"));
        $this->assertEquals(true, $char->isIndicatorNonSpace("*"));
        $this->assertEquals(true, $char->isIndicatorNonSpace("&"));
        $this->assertEquals(true, $char->isIndicatorNonSpace("!"));
        $this->assertEquals(true, $char->isIndicatorNonSpace("]"));
        $this->assertEquals(true, $char->isIndicatorNonSpace("|"));
        $this->assertEquals(true, $char->isIndicatorNonSpace("#"));
        $this->assertEquals(true, $char->isIndicatorNonSpace("@"));
        $this->assertEquals(true, $char->isIndicatorNonSpace("%"));
        $this->assertEquals(true, $char->isIndicatorNonSpace("^"));
        $this->assertEquals(true, $char->isIndicatorNonSpace("'"));
        $this->assertEquals(true, $char->isIndicatorNonSpace("\""));

        $this->assertEquals(false, $char->isIndicatorNonSpace('+'));
    }

    public function testIsIndicatorSimple()
    {
        $char = new Zend_Yaml_Character;
        $this->assertEquals(true, $char->isIndicatorSimple(","));
        $this->assertEquals(true, $char->isIndicatorSimple("{"));
        $this->assertEquals(true, $char->isIndicatorSimple("}"));
        $this->assertEquals(true, $char->isIndicatorSimple("["));
        $this->assertEquals(true, $char->isIndicatorSimple("]"));
        $this->assertEquals(true, $char->isIndicatorSimple(":"));

        $this->assertEquals(false, $char->isIndicatorSimple('+'));
    }

    public function testIsIndicatorLooseSimple()
    {
        $char = new Zend_Yaml_Character;
        $this->assertEquals(true, $char->isIndicatorLooseSimple(","));
        $this->assertEquals(true, $char->isIndicatorLooseSimple("{"));
        $this->assertEquals(true, $char->isIndicatorLooseSimple("}"));
        $this->assertEquals(true, $char->isIndicatorLooseSimple("["));
        $this->assertEquals(true, $char->isIndicatorLooseSimple("]"));

        $this->assertEquals(false, $char->isIndicatorLooseSimple('+'));
    }

}