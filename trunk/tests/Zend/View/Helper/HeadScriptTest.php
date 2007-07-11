<?php
require_once 'Zend/View.php';
require_once 'Zend/View/Helper/HeadScript.php';
require_once 'PHPUnit/Framework/TestCase.php';

class Zend_View_Helper_HeadScriptTest extends PHPUnit_Framework_TestCase
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

    public function testHeadScript()
    {
        $this->view->headScript('js1.js', 'javascript', 1);
        $this->view->headScript('js0.js', null, 0);

        // check existence
        $this->assertTrue($this->view->headScript()->has());
        $this->assertTrue($this->view->headScript()->has(0));
        $this->assertTrue($this->view->headScript()->has(1));
        $this->assertTrue($this->view->headScript()->has(null, 'js1.js'));
        $this->assertTrue($this->view->headScript()->has(null, 'js0.js', 'javascript'));
        $this->assertFalse($this->view->headScript()->has(null, 'js2.js'));
        $this->assertFalse($this->view->headScript()->has(null, 'js1.js', 'vbscript'));

        // check output forms
        
        $this->assertEquals(array(array('js0.js', 'javascript')), $this->view->headScript()->get(0));
        $this->assertEquals(array(array('js1.js', 'javascript')), $this->view->headScript()->get(1));
        $this->assertEquals('<script type="text/javascript" src="js0.js"></script>', $this->view->headScript()->toString(0));
        $this->assertEquals('<script type="text/javascript" src="js1.js"></script>', $this->view->headScript()->toString(1));
        $this->assertEquals('<script type="text/javascript" src="js0.js"></script>' . "\n" . '<script type="text/javascript" src="js1.js"></script>', $this->view->headScript()->toString());
        $this->assertEquals('<script type="text/javascript" src="js0.js"></script>' . "\n" . '<script type="text/javascript" src="js1.js"></script>', (string) $this->view->headScript());

        // check actual script blocks - should add CDATA later

        $this->view->headScript()->appendScript('var foo = "bar";', 'javascript');
        $this->assertEquals('<script type="text/javascript" src="js0.js"></script>' . "\n" . '<script type="text/javascript" src="js1.js"></script>' . "\n" . '<script type="text/javascript">' . "\n" . 'var foo = "bar";' . "\n" . '</script>', (string) $this->view->headScript());
    }

}