<?php
/**
 * @package    Zend_Factory
 * @subpackage UnitTests
 */

require_once dirname(dirname(__FILE__)) . '/TestHelper.php';


/** Zend_Factory */
require_once 'Zend/Factory.php';


/** PHPUnit_Framework_TestCase */
require_once 'PHPUnit/Framework/TestCase.php';

// pre included class used in tests
require_once 'Zend/Mail.php';


/**
 * @package    Zend_Factory
 * @subpackage UnitTests
 */
class FactoryTest extends PHPUnit_Framework_TestCase
{

    public function testFactoryInstantiatesZendObjectAfterIncluded() 
    {
        $mail = Zend_Factory::create('Zend_Mail');
        $this->assertTrue($mail instanceof Zend_Mail);
    }

    public function testFactoryInstantiatesZendObjectBeforeIncluded() 
    {
        $gdata = Zend_Factory::create('Zend_Gdata_Photos_AlbumEntry');
        $this->assertTrue($gdata instanceof Zend_Gdata_Photos_AlbumEntry);
    }

    public function testFactoryThrowsExceptionIfClassDoesNotExist() 
    {
        try {
           $mail = Zend_Factory::create('Zend_Foo');
           $this->fail('Did not throw an expected Exception on non-existent class request');
        } catch (Zend_Exception $e) {
        }
    }

    public function testFactoryInstantiatesUsingConstructParams() 
    {
        $registry = Zend_Factory::create('Zend_Registry', array(array('index'=>'something')));
        $this->assertEquals('something', $registry['index']);
    }

    public function testFactoryAllowsObjectReplacementForClasses() 
    {
        Zend_Factory::replaceClass('Zend_Registry', new Factory_Foo);
        $class = Zend_Factory::create('Zend_Registry');
        $this->assertTrue($class instanceof Factory_Foo);
    }

    public function testFactoryChecksOriginalClassExistenceToPreventBlindReplacement() 
    {
        Zend_Factory::replaceClass('Zend_Foo', new Factory_Foo);
        try {
            $class = Zend_Factory::create('Zend_Foo');
            $this->fail('Expected exception since replaced class never existed which means replacement could create blind errors');
        } catch (Zend_Exception $e) {
        }
    }
    
    public function after()
    {
       Zend_Factory::clearRegistry(); 
    }

}

class Factory_Foo
{
}