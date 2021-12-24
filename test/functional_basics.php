<?php

namespace Lum\Test;

require 'test/inc/basics.php';

$ns = '\Lum\Test';

Functional::start();
plan($plan);

ok($ok_test, 'ok()');
pass('pass()');

foreach ($is_tests as $ist)
{
  $type = get_debug_type($ist);
  is($ist, $ist, "is($type,$type)");
}

foreach ($isnt_tests as $isnt)
{
  $t1 = json_encode($isnt[0]);
  $t2 = json_encode($isnt[1]);
  isnt($isnt[0], $isnt[1], "isnt($t1,$t2)");
}

foreach ($cmp_tests as $cmp)
{
  $desc = 'cmp('.json_encode($cmp).')';
  $cmp[] = $desc;
  call_user_func_array($ns.'\cmp_ok', $cmp);
}

echo get_tap();
return test_instance();

