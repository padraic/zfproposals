<?php
require_once 'Zend/View.php';
require_once 'Zend/View/Helper/HeadLink.php';
require_once 'PHPUnit/Framework/TestCase.php';

class Zend_View_Helper_HeadLinkTest extends PHPUnit_Framework_TestCase
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

    public function testHeadLink()
    {
        $this->view->headLink(array('rel'=>'contents', 'href'=>'contents.html'));
        $this->view->headLink(array('rel'=>'stylesheet', 'type'=>'text/css', 'href'=>'style.css'));

        // check existence

        $this->assertTrue($this->view->headLink()->has());
        $this->assertTrue($this->view->headLink()->has(0));
        $this->assertTrue($this->view->headLink()->has(1));
        $this->assertFalse($this->view->headLink()->has(2));
        $this->assertTrue($this->view->headLink()->has(null, array('rel'=>'contents', 'href'=>'contents.html')));
        $this->assertTrue($this->view->headLink()->has(null, array('rel'=>'stylesheet', 'type'=>'text/css', 'href'=>'style.css')));

        // check output form

        $this->assertEquals('<link rel="contents" href="contents.html" />', $this->view->headLink()->toString(0));
        $this->assertEquals('<link rel="stylesheet" type="text/css" href="style.css" />', $this->view->headLink()->toString(1));
        $this->assertEquals('<link rel="contents" href="contents.html" />' . "\n" . '<link rel="stylesheet" type="text/css" href="style.css" />', $this->view->headLink()->toString());
        $this->assertEquals('<link rel="contents" href="contents.html" />' . "\n" . '<link rel="stylesheet" type="text/css" href="style.css" />', (string) $this->view->headLink());

        // check a removal

        $this->view->headLink()->remove(0);
        $this->assertEquals('<link rel="stylesheet" type="text/css" href="style.css" />', (string) $this->view->headLink());

        // check for exception

        try {
            $this->view->headLink(array('foo'=>'bar'));
            $this->fail();
        } catch (Zend_View_Exception $e) {}
    }

}