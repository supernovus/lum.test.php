<?php

require_once 'vendor/autoload.php';

$failureTest = new \Lum\Test();
$failureTest->plan(1);

$tests = new \Lum\Test\Harness();
$tests->addDir('test/harness/failure');
$tests->run();

$testSuite = $tests->testSuite();
$planned = $testSuite->planned();
$failed  = $testSuite->failed();
$failureTest->is($failed, $planned, 'test failures');

echo $failureTest->tap();
return $failureTest;
