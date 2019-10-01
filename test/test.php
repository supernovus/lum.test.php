<?php

require_once 'vendor/autoload.php';

$test = new \Lum\Test();
$test->plan(9);
$test->trace(1);

$test->ok((1==1), 'ok()');
$test->is(1,1, 'is(int,int)');
$test->is(1.1,1.1, 'is(float,float)');
$test->is('a','a', 'is(str,str)');
$test->is(true,true, 'is(true,true)');
$test->is(false,false, 'is(false,false)');
$test->is([1,2,3],[1,2,3],'is(arr,arr)');
$test->dies(function()
{
  throw new \Exception("Exception message");
}, "dies(exception_closure)");
$test->dies(function ()
{
  wrong();
}, "dies(error_closure)");

echo $test->tap();
