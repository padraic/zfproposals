<?php

require_once 'Zend/Application/Resource/ResourceAbstract.php';

class Zend_Application_Module_Configurator_Layout
    extends Zend_Application_Resource_ResourceAbstract
{

    public function init()
    {
        $bootstrap = $this->getBootstrap();
        if (!$bootstrap->hasResource('Layout')) {
            $options = array(
                'resources' => array(
                    'layout' => $this->getOptions()
                )
            );
            $bootstrap->setOptions($options);
            $bootstrap->bootstrap('Layout');
        } else {
            $layout = $bootstrap->getResource('Layout');
            $layout->setOptions($this->getOptions());
        }
    }

}
