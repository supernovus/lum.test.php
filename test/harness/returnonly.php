<?php

require_once 'vendor/autoload.php';

$test = new \Lum\Test();
$test->plan(2);
$test->ok(true, 'ok()');
$test->pass('pass()');

return $test;
