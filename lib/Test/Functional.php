<?php
/**
 * A Functional interface to the Test library.
 *
 * Your test file MUST be in the Lum\Test namespace for this to work.
 * For each of the functions defined below, see the Lum\Test method of the
 * same name for information on how it works and what parameters you can pass.
 *
 * You MUST call Functional::start() before using any of the functions.
 */

namespace Lum\Test;

/**
 * Get the current Test instance.
 *
 * @return Lum\Test  The current Test instance.
 */
function test_instance ()
{
  return Functional::$instance;
}

/**
 * Change number of tests planned.
 */
function plan ($c)
{
  return Functional::$instance->plan($c);
}

/**
 * Change stack trace level.
 */
function trace ($l=1)
{
  return Functional::$instance->trace($l);
}

/**
 * Get or set the TAP version.
 */
function version ($v=null)
{
  return Functional::$instance->version($v);
}

/**
 * Does a test pass?
 */
function ok ($t, $de=null, $di=null)
{
  return Functional::$instance->ok($t,$de,$di);
}

/**
 * Fail a test.
 */
function fail ($de=null, $di=null)
{
  return Functional::$instance->fail($de,$di);
}

/**
 * Pass a test.
 */
function pass ($de=null, $di=null)
{
  return Functional::$instance->pass($de,$di);
}

/**
 * Test to see if a closure throws an exception/error.
 */
function dies ($t, $d=null)
{
  return Functional::$instance->dies($t,$d);
}

/**
 * Test two values with a specified comparitor.
 */
function cmp_ok ($g, $w, $c, $d=null, $s=true)
{
  return Functional::$instance->cmp($g,$w,$c,$d,$s);
}

/**
 * Test to see if two values are equal (using === comparison).
 */
function is ($g, $w, $d=null, $s=true)
{
  return Functional::$instance->is($g,$w,$d,$s);
}

/**
 * Test to see if two values are NOT equal (using !== comparison).
 */
function isnt ($g, $w, $d=null, $s=true)
{
  return Functional::$instance->isnt($g,$w,$d,$s);
}

/**
 * Test to see if two structures are the same using JSON.
 */
function is_json ($g,$w,$d=null)
{
  return Functional::$instance->isJSON($g,$w,$d);
}

/**
 * Test to see if two structures are the same using Serialize.
 */
function is_serialized ($g,$w,$d=null,$r=false)
{
  return Functional::$instance->isSerialized($g,$w,$d,$r);
}

/**
 * Skip a test.
 */
function skip ($r=null, $d=null)
{
  return Functional::$instance->skip($r,$d);
}

/**
 * Mark a test as TODO.
 */
function todo ($r=null, $d=null)
{
  return Functional::$instance->todo($r,$d);
}

/**
 * Add a diagnostic message to the logs.
 */
function diag ($m)
{
  return Functional::$instance->diag($m);
}

/**
 * Get the TAP output from the test.
 */
function get_tap ()
{
  return Functional::$instance->tap();
}

/**
 * Static class for registering the functional instance.
 *
 * Usage in your Test script:
 *
 * namespace Lum\Test;
 *
 * Functional::start();
 * plan(1);
 * ok("test passed");
 * return test_instance();
 *
 */
class Functional
{
  /**
   * The current Test instance.
   */
  public static $instance;

  /**
   * Start a test instance.
   */
  public static function start ($opts=[])
  {
    self::$instance = new \Lum\Test($opts);
    self::$instance->traceLevel++; // Everything will be one level higher.
    return self::$instance;
  }
}