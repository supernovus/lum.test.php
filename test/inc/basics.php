<?php
/**
 * This is an include file defining basic tests of the core functionality.
 * Must be loaded using require NOT require_once. We need this more than once.
 */

namespace Lum\Test;

require_once 'test/inc/common.php';

// This had better work, LOL.
$ok_test = (1==1);

// Tests to compare strict equality.
$is_tests = [1, 1.1, 'a', true, false, [1,2,3], null];

$isnt_tests = 
[ // Tests to compare strict non-equality.
  [1,     '1'],
  [1,     1.0],
  [1.1,   '1.1'],
  [true,  1],
  [false, 0],
  [false, null],
  ['',    null],
  [[1,2], [2,1]],
];

$cmp_tests =
[ // Tests to use custom comparisons and aliases.
  [1, 1,     '==='],
  [1, 1,     'is'],
  [0, 1,     '!=='],
  [0, 1,     'isnt'],
  [1, '1',   'eq'],
  [0, false, 'eq'],
  [1, true,  '=='],
  [1, false, 'ne'],
  [0, true,  '!='],
  [1, 0,     '>'],
  [1, 0,     'gt'],
  [1, 0,     '>='],
  [1, 0,     'ge'],
  [1, 0,     'gte'],
  // TODO: finish this set of tests.
];

$plan 
  = 2 // $ok_test and pass() test.
  + count($is_tests) 
  + count($isnt_tests) 
  + count($cmp_tests)
  ;
