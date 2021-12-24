<?php

namespace Lum\Test;

require 'test/inc/dies.php';

$test = new \Lum\Test();
$test->plan($plan);

foreach ($dies_tests as $dies)
{
  call_user_func_array([$test, 'dies'], $dies);
}

echo $test->tap();
return $test;
