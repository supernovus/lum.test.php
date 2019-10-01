<?php

require_once 'vendor/autoload.php';

$test = new \Lum\Test();
$test->plan(3);

$test->ok(false, 'ok(false)');
$test->fail('fail()');
$test->todo('todo()');

echo $test->tap();
