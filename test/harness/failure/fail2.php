<?php

require_once 'vendor/autoload.php';

$test = new \Lum\Test();
$test->plan(2);

$test->cmp(2, 4, '>', '2 > 4');
$test->dies(function ()
{
  return true;
}, "dies(successful_function)");

echo $test->tap();
return $test;
