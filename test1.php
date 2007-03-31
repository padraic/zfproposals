<?php

set_time_limit(5);

set_include_path(
    '.' . PATH_SEPARATOR
    . './trunk/library' . PATH_SEPARATOR
    . get_include_path()
);

require_once 'Zend/Yaml/Buffer.php';
require_once 'Zend/Yaml/Lexer.php';

$yaml = <<<YAML
name: Ulysses
author: James Joyce
category: [fiction, ireland]
isbn: 10
YAML;

$buffer = new Zend_Yaml_Buffer($yaml);
$lexer = new Zend_Yaml_Lexer($buffer);

var_dump($lexer->getToken());

exit;