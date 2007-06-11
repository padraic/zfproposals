<?php

/** Zend_View_Factory_Interface */
require_once 'Zend/View/Factory/Interface.php';

/** Zend_View */
require_once 'Zend/View.php';

/**
 * Creates an instance of Zend_View based on a configuration sourced from
 * a relevant instance of Zend_Config.
 *
 */
class Zend_View_Factory implements Zend_View_Factory_Interface
{
    /**
     * View object basePath
     * @var string
     */
    protected $_viewBasePathSpec = ':moduleDir/:module/views';
    
    /**
     * Options array for configuring view objects
     */
    protected $_options = null;

    protected $_moduleDirectory = null;

    /** 
     * Constructor
     *
     * Optionally set options.
     * 
     * @param  array $options 
     * @return void
     */
    public function __construct($options = null)
    {

        if (isset($options) && !empty($options)) {
            // normalise to array
            if ($options instanceof Zend_Config) {
                $options = $options->toArray();
            }
            if (is_array($options)) {
                $this->_setOptions($options);
                $this->_options = $options;
            } else {
                require_once 'Zend/View/Exception.php';
                throw new Zend_View_Exception('expected $options array is not an array or Zend_Config object');
            }
        }
    }

    public function createInstance($module = 'default', array $model = null, Zend_View_Interface $parentView = null)
    {
        $view = new Zend_View;

        if (!$this->_options) {
            $basePath = $this->_getBasePath($module);
            $view->addBasePath($basePath);
            //$this->_assignModel($view, $model);
            return $view;
        }

        if(isset($this->_options['strictvars'])) {
            $view->strictVars((bool) $this->_options['strictvars']);
        }
        if (isset($this->_options['escape'])) {
            $view->setEscape($this->_options['escape']);
        }
        if (isset($this->_options['encoding'])) {
            $view->setEncoding($this->_options['encoding']);
        }
        
        /**
         * @todo Need to check each if set before using
         */
        if (isset($this->_options['module'])) {
            $basePaths = (array) $this->_options['module'][$module]['basePath'];
            $scriptPaths = (array) $this->_options['module'][$module]['scriptPath'];
            $filterPaths = (array) $this->_options['module'][$module]['filterPath'];
            $helperPaths = (array) $this->_options['module'][$module]['helperPath'];
            $helperClassPrefix = $this->_options['module'][$module]['helperClassPrefix'];
            $filterClassPrefix = $this->_options['module'][$module]['filterClassPrefix'];
        }
 
        if (isset($basePaths)) {
            foreach($basePaths as $bp) {
                $view->addBasePath($bp);
            }
        }

        if (isset($scriptPaths)) {
            foreach($scriptPaths as $sp) {
                $view->addScriptPath($sp);
            }
        }

        if (isset($helperPaths)) {
            foreach($helperPaths as $hp) {
                if (isset($helperClassPrefix)) {
                    $view->addHelperPath($hp, $helperClassPrefix);
                } else {
                    $view->addHelperPath($hp);
                }
            }
        }

        if (isset($filterPaths)) {
            foreach($filterPaths as $fp) {
                if (isset($filterClassPrefix)) {
                    $view->addFilterPath($fp, $filterClassPrefix);
                } else {
                    $view->addFilterPath($fp);
                }
            }
        }

        return $view;
        
    }

    /**
     * Set view basePath specification
     *
     * Specification can contain one or more of the following:
     * - :moduleDir - parent directory of all modules
     * - :module - name of current module in the request
     * 
     * @param  string $path 
     * @return Zend_View_Factory
     */
    public function setViewBasePathSpec($path)
    {
        $this->_viewBasePathSpec = (string) $path;
        return $this;
    }

    /**
     * Retrieve the current view basePath specification string
     * 
     * @return string
     */
    public function getViewBasePathSpec()
    {
        return $this->_viewBasePathSpec;
    }

    /**
     * Set view script suffix 
     * 
     * @param  string $suffix 
     * @return Zend_View_Factory
     */
    public function setModuleDirectory($path)
    {
        $this->_moduleDirectory = rtrim($path, '\\/');
        return $this;
    }

    /**
     * Get view script suffix 
     * 
     * @return string
     */
    public function getModuleDirectory()
    {
        return $this->_moduleDirectory;
    }

    /**
     * Set options
     * 
     * @param  array $options 
     * @return Zend_View_Factory
     */
    protected function _setOptions($options)
    {
        foreach ($options as $key => $value)
        {
            switch ($key) {
                case 'viewbasepathspec':
                    $property = '_' . $key;
                    $this->{$property} = (string) $value;
                    break;
                case 'moduledir':
                    $property = '_' . $key;
                    $this->{$property} = rtrim($value, '\\/');
                    break;
                default:
                    break;
            }
        }

        return $this;
    }

    protected function _getBasePath($module)
    {
        $basePath = $this->_translateSpec($this->getViewBasePathSpec(), $module);
        return $basePath;
    }

    /**
     * Inject values into a spec string
     *
     * Allowed variables are:
     * - :moduleDir - parent directory of all modules
     * - :module - current module name
     * 
     * @param  string $spec 
     * @param  array $vars 
     * @return string
     */
    protected function _translateSpec($spec, $module, array $vars = array())
    {
        $moduleDir  = $this->getModuleDirectory();
        if (null === $moduleDir) {
            require_once 'Zend/View/Exception.php';
            throw new Zend_View_Exception('Factory cannot locate module directory');
        }
        $moduleDir = dirname($moduleDir);

        foreach ($vars as $key => $value) {
            switch ($key) {
                case 'module':
                case 'moduleDir':
                    $$key = (string) $value;
                    break;
                default:
                    break;
            }
        }

        $replacements = array(
            ':moduleDir'  => $moduleDir,
            ':module'     => str_replace(array('.','-'), '-', $module)
        );
        $value = str_replace(array_keys($replacements), array_values($replacements), $spec);
        return $value;
    }

}