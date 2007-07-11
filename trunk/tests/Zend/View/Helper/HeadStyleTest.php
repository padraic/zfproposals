<?php
require_once 'Zend/View.php';
require_once 'Zend/View/Helper/HeadStyle.php';
require_once 'PHPUnit/Framework/TestCase.php';

class Zend_View_Helper_HeadStyleTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $view = new Zend_View();
        $view->strictVars(true);
        $this->view = $view;
    }

    public function tearDown()
    {
        unset($this->view);
    }

    public function testHeadStyle()
    {
        $this->view->headStyle('js1.js', 'javascript', 1);
        $this->view->headStyle('js0.js', null, 0);

        // check existence
        $this->assertTrue($this->view->headStyle()->has());
        $this->assertTrue($this->view->headStyle()->has(0));
        $this->assertTrue($this->view->headStyle()->has(1));
        $this->assertTrue($this->view->headStyle()->has(null, 'js1.js'));
        $this->assertTrue($this->view->headStyle()->has(null, 'js0.js', 'javascript'));
        $this->assertFalse($this->view->headStyle()->has(null, 'js2.js'));
        $this->assertFalse($this->view->headStyle()->has(null, 'js1.js', 'vbscript'));
    }

}