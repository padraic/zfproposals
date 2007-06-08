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

    protected $_config = null;

    protected $_moduleName = null;

    protected

    public function __construct(Zend_Config $config)
    {
        $this->_config = $config;
    }

    public function createInstance($module = null, array $model = null, Zend_View_Interface $parentView = null)
    {
        if (isset($module) && !ctype_alnum($module)) {
            require_once 'Zend/View/Exception.php';
            throw new Zend_View_Exception('Invalid module name; must only contain alphanumeric characters');
        }
        if (isset($view) && is_null($module)) {
            $subView = clone $view;
        } else {
            if (is_null($module)) {
                $module = 'default'; // assume the default module
            }
            $subView = new Zend_View();
            /**
             * This is the conventional ZF directory layout. Some configuration
             * improvements could allow more flexibility here.
             */
            $subView->setBasePath(
                Zend_Registry::get('ApplicationPath') . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'views'
            );
            if(isset($view)) {
                $subView->setParentView($view->getParentView());
            }
            $subView->setEncoding($this->_config->encoding);
            $subView->setEscape($this->_config->escape);
        }
        /**
         * Place holder for parameters set by a parent view on a sub-view which
         * can be used as inputs to certain View Helpers in the sub view.
         */
        if (isset($params)) {
            $subView->params = $params;
        }
        return $subView;
    }

    protected function _getBasePath()
    {
        $base = realpath('.');
        var_dump($base); exit;
        $path = $this->_moduleName . DIRECTORY_SEPARATOR . 'views';
    }

}