<?php
require_once 'Zend/View.php';
require_once 'Zend/View/Helper/Controller.php';
require_once 'PHPUnit/Framework/TestCase.php';

class Zend_View_Helper_PartialTest extends PHPUnit_Framework_TestCase 
{

    public function testPartialDefault() {
        $view = new Zend_View();
        Zend_View_Abstract::getFactory()->setModuleDirectory(dirname(dirname(__FILE__)) . '/_templates');
    	$view->setScriptPath(dirname(dirname(__FILE__)) . '/_templates/default/views/scripts');
        $this->assertEquals("<html>\n<body>\nHello World!</body>\n</html>", $view->render('index.phtml'));
    }

    public function testPartialModule() {
        $view = new Zend_View();
        Zend_View_Abstract::getFactory()->setModuleDirectory(dirname(dirname(__FILE__)) . '/_templates');
    	$view->setScriptPath(dirname(dirname(__FILE__)) . '/_templates/default/views/scripts');
        $this->assertEquals("<html>\n<body>\nHello World!</body>\n</html>", $view->render('index2.phtml'));
    }

}