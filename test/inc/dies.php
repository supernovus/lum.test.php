<?php

namespace Lum\Test;

require_once 'test/inc/common.php';

$ns = '\Lum\Test';

$test_object = new TestObject;

$dies_tests =
[ // Tests of dies() for catching Exceptions and Errors.
  [function() { throw new Exception; }, 'dies(exception_function_closure)'],
  [fn() => throw new Exception, 'dies(exception_arrow_closure)'],
  [$ns.'\make_exception', 'dies(exception_string_function_callable)'],
  [$ns.'\TestObject::throwException', 'dies(exception_string_static_callable)'],
  [[$test_object, 'throwException'], 'dies(exception_array_instance_callable)'],
  [[$ns.'\TestObject', 'throwException'], 'dies(exception_array_static_callable)'],

  // Next up, catching Errors.
  [function() { wrong(); }, 'dies(error_function_closure)'],
  [fn() => wrong(), 'dies(error_arrow_closure)'],
  [$ns.'\make_error', 'dies(error_string_function_callable)'],
  [$ns.'\TestObject::throwError', 'dies(error_string_static_callable)'],
  [[$test_object, 'throwError'], 'dies(error_array_instance_callable)'],
  [[$ns.'\TestObject', 'throwError'], 'dies(error_array_static_callable)'],

];

$plan = count($dies_tests);
