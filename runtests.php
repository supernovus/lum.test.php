<?php

require_once 'vendor/autoload.php';

$tests = new \Lum\Test\Harness();
$tests->addDir('test');
$tests->run();

echo $tests->summary();
if ($tests->success())
{ // All tests completed successfully.
  exit(0);
}
else
{ // Some tests failed.
  exit(1);
}
