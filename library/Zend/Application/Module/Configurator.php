<?php

class Zend_Application_Module_Configurator
{

    /**
     * @var Zend_Loader_PluginLoader_Interface
     */
    protected $_pluginLoader;

    /**
     * @var array Class-based resource plugins
     */
    protected $_pluginResources = array();

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
            $this->_executeConfigurator(strtolower($resourceName));
        }
    }

    /**
     * Set plugin loader for loading configurators
     *
     * @param  Zend_Loader_PluginLoader_Interface $loader
     * @return Zend_Application_Module_Configurator
     */
    public function setPluginLoader(Zend_Loader_PluginLoader_Interface $loader)
    {
        $this->_pluginLoader = $loader;
        return $this;
    }

    /**
     * Get the plugin loader for configurators
     *
     * @return Zend_Loader_PluginLoader_Interface
     */
    public function getPluginLoader()
    {
        if ($this->_pluginLoader === null) {
            $options = array(
                'Zend_Application_Module_Configurator' => 'Zend/Application/Module/Configurator'
            );
            $this->_pluginLoader = new Zend_Loader_PluginLoader($options);
        }
        return $this->_pluginLoader;
    }

    /**
     * Execute a configurator for the given resource assuming the plugin
     * loader can find one to instantiate. If none found matching the
     * resource name, an exception is thrown.
     *
     * @param  string $resourceName
     * @return void
     * @throws Zend_Application_Module_Exception If no matching configurator found
     */
    protected function _executeConfigurator($resourceName)
    {
        $options = $this->_config->resources->$resourceName;
        $configurator = $this->_loadConfigurator($resourceName, $options);
        $configurator->init();
    }

    /**
     * Load a plugin resource
     *
     * @param  string $resource
     * @param  Zend_Config $options
     * @return Zend_Application_Module_Configurator_ConfiguratorAbstract
     */
    protected function _loadConfigurator($resourceName, Zend_Config $options)
    {
        $className = $this->getPluginLoader()->load($resourceName, false);
        if (!$className) {
            require_once 'Zend/Application/Module/Exception.php';
            throw new Zend_Application_Module_Exception('A Configurator'
            . 'for a resource called "' . $resourceName . '" could not be'
            . 'located');
        }
        $instance = new $className($options);
        $instance->setBootstrap($this->_bootstrap);
        return $instance;
    }

}
