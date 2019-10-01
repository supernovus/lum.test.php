<?php

require_once 'vendor/autoload.php';

$tests = new \Lum\Test\Harness();
$tests->addDir('test/harness');
$tests->run();

echo $tests->summary();
return $tests->testSuite();
