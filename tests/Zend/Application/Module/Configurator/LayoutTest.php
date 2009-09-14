<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'Zend/Application/Module/Configurator/Layout.php';

require_once 'Zend/Loader/Autoloader.php';

class Zend_Application_Module_Configurator_LayoutTest extends PHPUnit_Framework_TestCase
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
        $this->config = array(
            'layout'     => 'mylayout',
            'layoutPath' => '/my/layout/path',
        );
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

    public function testResetsLayoutNameFromModuleConfig()
    {
        $this->bootstrap->bootstrap('Layout');
        $configurator = new Zend_Application_Module_Configurator_Layout;
        $configurator->setOptions($this->config);
        $configurator->setBootstrap($this->bootstrap);
        $configurator->init();
        $this->assertEquals('mylayout', $this->bootstrap->getResource('Layout')->getLayout());
    }

    public function testResetsLayoutPathFromModuleConfig()
    {
        $this->bootstrap->bootstrap('Layout');
        $configurator = new Zend_Application_Module_Configurator_Layout;
        $configurator->setOptions($this->config);
        $configurator->setBootstrap($this->bootstrap);
        $configurator->init();
        $this->assertEquals('/my/layout/path', $this->bootstrap->getResource('Layout')->getLayoutPath());
    }

    public function testInitialisesResourceIfNotDoneDuringBootstrapping()
    {
        $bootstrap = new Zend_Application_Bootstrap_Bootstrap($this->application);
        $configurator = new Zend_Application_Module_Configurator_Layout;
        $configurator->setOptions($this->config);
        $configurator->setBootstrap($bootstrap);
        $configurator->init();
        $this->assertTrue($bootstrap->hasResource('Layout'));
    }

}
