<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'Zend/Application/Module/Configurator.php';

require_once 'Zend/Loader/Autoloader.php';

class Zend_Application_Module_ConfiguratorTest extends PHPUnit_Framework_TestCase
{

    public function setup()
    {
        $this->loaders = spl_autoload_functions();
        if (!is_array($this->loaders)) {
            $this->loaders = array();
        }
        Zend_Loader_Autoloader::resetInstance();
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $this->application = new Zend_Application('testing');
        $this->bootstrap = new Zend_Application_Bootstrap_Bootstrap($this->application);
        Zend_Controller_Front::getInstance()->resetInstance();
        $options = array(
            'resources' => array(
                'layout' => array(
                    'layout'     => 'layout',
                    'layoutPath' => './layouts',
                )
            )
        );
        $this->bootstrap->setOptions($options);
        $this->config = new Zend_Config(array(
            'resources' => array(
                'layout' => array(
                    'layout'     => 'mylayout',
                    'layoutPath' => '/my/layout/path',
                )
            )
        ));
        $this->badconfig = new Zend_Config(array(
            'resources' => array(
                'foo' => array(
                    'bar'     => 'mylayout',
                )
            )
        ));
    }

    public function teardown()
    {
        $loaders = spl_autoload_functions();
        foreach ($loaders as $loader) {
            spl_autoload_unregister($loader);
        }
        foreach ($this->loaders as $loader) {
            spl_autoload_register($loader);
        }
        Zend_Loader_Autoloader::resetInstance();
    }

    public function testReconfiguresResourceWhenResourceNameFoundInOptions()
    {
        $configurator = new Zend_Application_Module_Configurator($this->bootstrap, $this->config);
        $configurator->run();
        $this->assertEquals('mylayout', $this->bootstrap->getResource('Layout')->getLayout());
    }

    public function testThrowsExceptionWhenResourceNameFoundInOptionsHasNoMatchingConfigurator()
    {
        $this->setExpectedException('Zend_Application_Module_Exception');
        $configurator = new Zend_Application_Module_Configurator($this->bootstrap, $this->badconfig);
        $configurator->run();
    }

}
