<?php
/**
 * Zend Framework
 *
 *
 * @package    Zend_View
 * @subpackage Helpers
 * @copyright  Copyright (c) 2007 Pádraic Brady (http://blog.astrumfutura.com)
 * @version    $Id$
 * @license    New BSD
 */

/** Zend_Registry */
require_once 'Zend/Registry.php';

/**
 * Helper for passing data between otherwise segregated Views. In essence this
 * is just a proxy to a specific centralised Registry. It's called Placeholder to
 * make its typical usage obvious, but can be used just as easily for non-Placeholder
 * things. That said, the support for this is only guaranteed to effect Layouts.
 * 
 * @package    Zend_View
 * @subpackage Helpers
 * @copyright  Copyright (c) 2007 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    New BSD
 */
class Zend_View_Helper_Controller {
    
    /**
     * Constructor;
     *
     * @return void
     */
    public function __construct()
    { 
    }

    /**
     * Dispatch a request via the Controller and fetch the resulting rendered
     * View to return
     *
     * @param string $action
     * @param string $controller
     * @param string $module
     * @param array $params
     * @returns string
     * @todo Breaks with the ViewRenderer enabled
     */
    public function controller($action, $controller = null, $module = null, array $params = null)
    {
        $front = Zend_Controller_Front::getInstance();
        $request = clone $front->getRequest();
        $request->setActionName($action);
        if (isset($controller)) {
            $request->setControllerName($controller);
        }
        if (isset($module)) {
            $request->setModuleName($module);
        }
        if (isset($params)) {
            $request->setParams($params);
        }
        $response = new Zend_Controller_Response_Http();
        $front->getDispatcher()->dispatch($request, $response);
        return $response->getBody();
    }

}