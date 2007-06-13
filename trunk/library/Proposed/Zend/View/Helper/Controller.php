<?php
/**
 * Zend Framework
 *
 *
 * @package    Zend_View
 * @subpackage Helpers
 * @version    $Id$
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Helper for returning the output from dispatching a new Request to the
 * Controller using the provided Action name, and optional Controller/Module
 * names and other parameters.
 * 
 * @package    Zend_View
 * @subpackage Helpers
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_Controller {

    /**
     * Dispatch a request via the Controller and fetch the resulting rendered
     * View to return
     *
     * @param string $action
     * @param string $controller
     * @param string $module
     * @param array $params
     * @returns string
     */
    public function controller($action, $controller = null, $module = null, array $params = null)
    {
        $front = Zend_Controller_Front::getInstance();
        if (!$front->getParam('noViewRenderer') && Zend_Controller_Action_HelperBroker::hasHelper('viewRenderer')) {
            $enableViewRendererReset = true;
        }
        $front = Zend_Controller_Front::getInstance();
        $request = clone $front->getRequest();
        $request->setActionName($action);
        if (!is_null($controller)) {
            $request->setControllerName($controller);
        }
        if (!is_null($module) && !is_null($controller)) {
            $request->setModuleName($module);
        }
        if (!is_null($params)) {
            $request->setParams($params);
        }
        $response = new Zend_Controller_Response_Http();
        if (isset($enableViewRendererReset)) {
            $viewRenderer = Zend_Controller_Action_HelperBroker::getExistingHelper('viewRenderer');
            $__originalRequest = $viewRenderer->getRequest();
            $__originalResponse = $viewRenderer->getResponse();
            $viewRenderer->setRequest($request);
            $viewRenderer->setResponse($response);
        }
        $front->getDispatcher()->dispatch($request, $response);
        // reset original objects back on ViewRenderer
        if (isset($enableViewRendererReset)) {
            $viewRenderer->setRequest($__originalRequest);
            $viewRenderer->setResponse($__originalResponse);
        }
        return $response->getBody();
    }

}