<?php

require_once 'vendor/autoload.php';

$test = new \Lum\Test();
$test->plan(2);

$test->ok(false, 'ok(false)');
$test->fail('fail()');

echo $test->tap();
return $test;
