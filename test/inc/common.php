<?php
/**
 * An include file used by other include files for some generic stuff.
 */

namespace Lum\Test;

require_once 'vendor/autoload.php';

// A test exception.
class Exception extends \Exception
{
  protected $message = 'Exception message';
}

// A simplistic class for some extra tests.
class TestObject
{
  static function throwException()
  {
    throw new Exception;
  }

  static function throwError()
  { // Double whammy, no $this in static functions plus no such function.
    return $this->wrong();
  }
}

function make_exception()
{
  throw new Exception;
}

function make_error()
{
  wrong();
}
