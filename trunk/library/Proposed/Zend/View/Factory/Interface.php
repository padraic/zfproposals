<?php

interface Zend_View_Factory_Interface
{

    public function createInstance($module = null, array $model = null, Zend_View_Interface $parentView = null);

}