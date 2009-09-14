<?php

class Zend_Application_Module_Configurator
{

    public function __construct(Zend_Application_Bootstrap_Bootstrapper $bootstrap,
    Zend_Config $config)
    {
        $this->_bootstrap = $bootstrap;
        $this->_config = $config;
    }

    public function run()
    {
        $resources = array_keys($this->_config->resources->toArray());
        foreach ($resources as $resourceName) {
            $options = $this->_config->resources->$resourceName;
            $resourceClass = 'Zend_Application_Module_Resource_' . ucfirst($resourceName);
            $resource = new $resourceClass($options);
            $resource->setBootstrap($this->_bootstrap);
            $resource->init();
        }
    }

}
