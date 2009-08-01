<?php

require_once 'PHPUnit/Framework/TestCase.php';
require_once 'Zend/Pubsubhubbub/Storage/Filesystem.php';

class Zend_Pubsubhubbub_Storage_FilesystemTest extends PHPUnit_Framework_TestCase
{

    public function testImplementsStorageInterface()
    {
        $store = new Zend_Pubsubhubbub_Storage_Filesystem;
        $this->assertTrue($store instanceof Zend_Pubsubhubbub_StorageInterface);
    }

    // add the rest later - it's a really simple class. Too lazy to TDD its ass...copied from my OpenID impl.
}
