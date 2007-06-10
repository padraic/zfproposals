<?php
require_once 'Zend/View.php';
require_once 'Zend/View/Helper/Placeholder.php';
require_once 'PHPUnit/Framework/TestCase.php';

class Zend_View_Helper_PlaceholderTest extends PHPUnit_Framework_TestCase 
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

    public function testPlaceholder()
    {
        $this->assertTrue($this->view->placeholder() instanceof Zend_View_Helper_Placeholder);

        $this->view->placeholder()->set('varName1', 'defaultValue');
        $this->assertTrue($this->view->placeholder()->has('varName1'));
        $this->assertEquals('defaultValue', $this->view->placeholder()->get('varName1'));
        
        $this->view->placeholder()->append('varName1', 'defaultValue');
        $this->assertTrue($this->view->placeholder()->has('varName1'));
        $this->assertEquals('defaultValuedefaultValue', $this->view->placeholder()->get('varName1'));

        $this->view->placeholder()->remove('varName1');
        $this->assertFalse($this->view->placeholder()->has('varName1'));
        $this->assertEquals(null, $this->view->placeholder()->get('varName1'));
    }

}