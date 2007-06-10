<?php
require_once 'Zend/View.php';
require_once 'Zend/View/Factory.php';
require_once 'Zend/Config/Ini.php';
require_once 'PHPUnit/Framework/TestCase.php';

class Zend_View_FactoryTest extends PHPUnit_Framework_TestCase 
{
    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    public function testFactoryDefault()
    {
        $factory = new Zend_View_Factory();
        $factory->setModuleDirectory('/');
        $view = $factory->createInstance();
        $this->assertTrue($view instanceof Zend_View);
    }

    public function testFactoryWithOptionsIni()
    {
        $options = new Zend_Config_Ini(dirname(__FILE__) . DIRECTORY_SEPARATOR . '_files/view.ini', null, 'view');
        $factory = new Zend_View_Factory($options->view);
        $view = $factory->createInstance();
        $this->assertTrue($view instanceof Zend_View);
        $this->assertEquals('UTF-8', $view->getEncoding());
    }

}