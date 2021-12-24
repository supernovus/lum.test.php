<?php

namespace Lum\Test;

require 'test/inc/dies.php';

Functional::start();
plan($plan);

foreach ($dies_tests as $dies)
{
  call_user_func_array($ns.'\dies', $dies);
}

echo get_tap();
return test_instance();

