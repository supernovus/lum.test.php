<?php

require_once 'vendor/autoload.php';

$test = new \Lum\Test();
$test->plan(3);
$test->ok(true, 'ok()');
$test->pass('pass()');
$test->skip('skip()');

echo $test->tap();
