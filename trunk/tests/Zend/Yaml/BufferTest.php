<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/** Zend_Yaml_Buffer */
require_once 'Zend/Yaml/Buffer.php';

/** PHPUnit test case */
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * @package 	Zend_Service
 * @subpackage  UnitTests
 */
class Zend_Yaml_BufferTest extends PHPUnit_Framework_TestCase
{

    private $_yamlString = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function testConstructorWithAsciiString()
    {
        try {
            $buffer = new Zend_Yaml_Buffer($this->_yamlString);
        } catch (Zend_Yaml_Exception $e) {
            $this->fail('Unexpected Zend_Yaml_Exception while constructing a Zend_Yaml_Buffer object using a String.');
        }
    }

    public function testConstructorWithAsciiFile()
    {
        try {
            $buffer = new Zend_Yaml_Buffer(fopen('_files/asciiString', 'rb'));
        } catch (Zend_Yaml_Exception $e) {
            $this->fail('Unexpected Zend_Yaml_Exception while constructing a Zend_Yaml_Buffer object using a String.');
        }
    }

    public function testPeekString()
    {
        $buffer = new Zend_Yaml_Buffer($this->_yamlString);
        $this->assertEquals('A', $buffer->peek());
        $this->assertEquals('P', $buffer->peek(15));
    }

    public function testPeekFileStream()
    {
        $buffer = new Zend_Yaml_Buffer(fopen('_files/asciiString', 'rb'));
        $this->assertEquals('A', $buffer->peek());
        $this->assertEquals('P', $buffer->peek(2043));
    }

    public function testPrefixString()
    {
        $buffer = new Zend_Yaml_Buffer($this->_yamlString);
        $this->assertEquals('A', $buffer->prefix());
        $this->assertEquals('ABCD', $buffer->prefix(4));
    }

    public function testPrefixFileStream()
    {
        $buffer = new Zend_Yaml_Buffer(fopen('_files/asciiString', 'rb'));
        $this->assertEquals('A', $buffer->prefix());
        $this->assertEquals(file_get_contents('_files/asciiFileStreamPrefixResult'), $buffer->prefix(1028));
    }

    public function testForwardString()
    {
        $buffer = new Zend_Yaml_Buffer($this->_yamlString);
        $buffer->forward();
        $this->assertEquals('B', $buffer->peek());
        $buffer->forward(15);
        $this->assertEquals('Q', $buffer->peek());
    }

    public function testForwardFileStream()
    {
        $buffer = new Zend_Yaml_Buffer(fopen('_files/asciiString', 'rb'));
        $buffer->forward();
        $this->assertEquals('B', $buffer->peek());
        $buffer->forward(2042);
        $this->assertEquals('Q', $buffer->peek());
    }

}