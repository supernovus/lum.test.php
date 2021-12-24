<?php

namespace Lum\Test;

require 'test/inc/basics.php';

$test = new \Lum\Test();
$test->plan($plan);

$test->ok($ok_test, 'ok()');
$test->pass('pass()');

foreach ($is_tests as $is)
{
  $t = json_encode($is);
  $test->is($is, $is, "is($t,$t)");
}

foreach ($isnt_tests as $isnt)
{
  $t1 = json_encode($isnt[0]);
  $t2 = json_encode($isnt[1]);
  $test->isnt($isnt[0], $isnt[1], "isnt($t1,$t2)");
}

foreach ($cmp_tests as $cmp)
{
  $desc = 'cmp('.json_encode($cmp).')';
  $cmp[] = $desc;
  call_user_func_array([$test, 'cmp'], $cmp);
}

echo $test->tap();
return $test;
