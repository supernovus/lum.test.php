<?php

namespace Lum\Test;

require_once 'vendor/autoload.php';

Functional::start();
plan(10);
trace(1);

ok((1==1), 'ok()');
is(1,1, 'is(int,int)');
is(1.1,1.1, 'is(float,float)');
is('a','a', 'is(str,str)');
is(true,true, 'is(true,true)');
is(false,false, 'is(false,false)');
is([1,2,3],[1,2,3],'is(arr,arr)');
dies(function()
{
  throw new \Exception("Exception message");
}, "dies(exception_closure)");
dies(function ()
{
  wrong();
}, "dies(error_closure)");

todo('need to implement v13', 'yaml diagnostics');

echo get_tap();
return test_instance();

