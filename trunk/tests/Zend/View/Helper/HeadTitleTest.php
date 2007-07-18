<?php
require_once 'Zend/View.php';
require_once 'Zend/View/Helper/HeadTitle.php';
require_once 'PHPUnit/Framework/TestCase.php';

class Zend_View_Helper_HeadTitleTest extends PHPUnit_Framework_TestCase
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

    public function testHeadTitle()
    {
        $this->view->headTitle('My Page');
        $this->assertTrue($this->view->headTitle()->has());
        $this->assertEquals('My Page', $this->view->headTitle()->get());

        // overwriting
        $this->view->headTitle('My Page 2');
        $this->assertEquals('My Page 2', $this->view->headTitle()->get());

        $this->view->headTitle()->remove();
        $this->assertFalse($this->view->headTitle()->has());
    }

    public function testPrefix()
    {
        $this->view->headTitle()->setPrefix('prefix');
        $this->assertEquals('prefix', $this->view->headTitle()->getPrefix());
    }

}