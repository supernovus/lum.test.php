<?php

namespace Lum\Test;

use \Lum\Test as T;

/**
 * Test Harness.
 *
 * This can be used to run a bunch of test files and return
 * an aggregated report of passes and failures.
 *
 * It can parse TAP output or it can use a returned Test instance directly.
 */
class Harness
{
  protected $testSuite;
  protected $testFiles = [];
  protected $testOutput = [];
  protected $testInstances = [];
  protected $testOpts;

  /**
   * Build a new Test Harness.
   *
   * Initializes an internal Test instance that will be used to
   * keep track of whether the tests we run succeeded or failed.
   *
   * @param array $opts  (Optional) Constructor options for the Test Suite.
   *
   *   By default this only applies to the internal Test suite, which is
   *   created by the Harness to keep track of which test files passed and 
   *   which failed. However the `$setDefaults` parameter can change that.
   *
   *   {@see \Lum\Test::__construct()}
   *
   * @param bool|array $setDefaults  (Optional, default: `false`)
   *
   *   If `true`, the `$opts` applies to all tests.
   *
   *   If an `array` it's the options to apply to tests instead of `$opts`,
   *   in this case `$opts` will only be used for the internal Test suite.
   *
   *   Regardless of if its `true` or an `array`, only the 
   *   `verbose`, `trace`, and `version` options have any affect on default 
   *   values, anything else will be ignored.
   *
   *   If `false` the defaults are left alone entirely.
   *
   */
  public function __construct (array $opts=[], bool|array $setDefaults=false)
  {
    if ($setDefaults === true)
    { // Use the $opts to get defaults.
      $setDefaults = $opts;
    }
    if (is_array($setDefaults))
    { // Set default values.
      if (isset($setDefaults['trace']) && is_int($setDefaults['trace']))
        $this->setStackTrace($setDefaults['trace']);
      if (isset($setDefaults['verbose']) && is_int($setDefaults['verbose']))
        $this->setVerbosity($setDefaults['verbose']);
      if (isset($setDefaults['version']) && is_int($setDefaults['version']))
        $this->setVersion($setDefaults['version']);
    }

    $this->testOpts = $opts; // Save for later.
    $this->testSuite = new T($opts);
  }

  /** Get the default verbosity level for tests. */
  public function getVerbosity(): int
  {
    return T::$VERBOSITY;
  }

  /** Change the default verbosity level for tests. */
  public static function setVerbosity (int $level)
  {
    T::$VERBOSITY = $level;
  }

  /** Change the default stack trace type for all tests. */
  public function setStackTrace (int $level)
  {
    T::$STACK_TRACE = $level;
  }

  /** Change the default TAP version for all tests. */
  public function setVersion (int $version)
  {
    if ($version === 12 || $version === 13)
    {
      T::$TAP_VERSION = $version;
    }
    else
    {
      throw new InvalidVersionException;
    }
  }

  /**
   * Add an individual test file to be ran when run() is called.
   *
   * @param string $file  The path to the test file.
   * @return Harness  Returns $this.
   */
  public function addTest ($file)
  {
    if (file_exists($file))
    {
      $this->testFiles[] = $file;
    }
    return $this;
  }

  /**
   * Add a directory of tests to be ran when run() is called.
   *
   * @param string $dir  The directory we are adding.
   * @param string $ext  (Optional, default 'php') Extension of test files.
   *                     Don't specify the leading dot, it's implicit.
   *                     You may specify more than one separated by | character.
   *
   * @return Harness  Returns $this.
   */
  public function addDir ($dir, $ext='php')
  {
    if (file_exists($dir) && is_dir($dir))
    {
      $files = scandir($dir);
      foreach ($files as $file)
      {
        if ($file == '.' || $file == '..') continue;
        if (preg_match("/\.(?:$ext)$/", $file))
        { // It matches our file extension.
          $this->testFiles[] = join(DIRECTORY_SEPARATOR, [$dir, $file]);
        }
      }
    }
    return $this;
  }

  /**
   * Run all of the registered tests.
   *
   * @return Harness  Returns $this.
   */
  public function run ($plan=true)
  {
    if ($plan)
    {
      $this->testSuite->plan(count($this->testFiles));
    }
    foreach ($this->testFiles as $testfile)
    {
      $this->runTest($testfile);
    }
    return $this;
  }

  /**
   * Run an individual test file.
   *
   * If the test returns output to STDOUT, it's assumed to be the TAP output.
   * If it returns a Test instance via the 'return' statement, we save that
   * and use it directly to calculate the results rather than parsing the TAP.
   *
   * @param string $file  The test file to run.
   * @return array [$output, $instance];
   */
  public function runTest ($__test_file, bool $noTodo=true)
  {
    ob_start(); // Start output buffering.
    $returnVal = null;
    $threw = false;
    try
    {
      $returnVal = include $__test_file;
    }
    catch (\Throwable $e)
    {
      $this->testSuite->fail($__test_file, $e->getMessage());
      $threw = true;
    }
    $outputVal = ob_get_contents();
    @ob_end_clean(); // End output buffering.
    $this->testOutput[$__test_file] = $outputVal;
    $this->testInstances[$__test_file] = $returnVal;
    if ($threw)
    {
      return [$outputVal, $returnVal];
    }
    if (isset($returnVal) 
      && ($returnVal instanceof \Lum\Test) 
      || ($returnVal instanceof \Lum\Test\Harness)
      || ($returnVal instanceof \Lum\Test\Mock))
    { // Use the Test instance to determine success.
      //$testsFailed = $returnVal->failed() - $returnVal->areTodo();
      //$this->testSuite->is($testsFailed, 0, $__test_file);
      $this->testSuite->ok($returnVal->success($noTodo), $__test_file);
    }
    elseif (isset($outputVal) && trim($outputVal) != '')
    {
      $parsed = $this->parseTAP($outputVal);
      if (isset($parsed))
      {
        //$testsFailed = $parsed->failed - $parsed->todo;
        //$this->testSuite->is($testsFailed, 0, $__test_file);
        $this->testSuite->ok($parsed->success($noTodo), $__test_file);
      }
      else
      {
        $this->testSuite->fail($__test_file, "could not parse TAP output");
      }
    }
    else
    {
      $this->testSuite->fail($__test_file, "nothing returned from test");
    }
    return [$outputVal, $returnVal];
  }

  /**
   * Return the test suite Test instance.
   *
   * Can be used to make nested test suites.
   */
  public function testSuite ()
  {
    return $this->testSuite;
  }

  /**
   * Return the raw TAP output from all tests that were run.
   */
  public function testOutput ()
  {
    return $this->testOutput;
  }

  /**
   * Return the Test instances that were returned from all tests that were run.
   */
  public function testInstances()
  {
    return $this->testInstances;
  }

  /** 
   * Make a sub-test using the options we have saved.
   */
  public function subTest(?array $topts=null): T
  {
    if (!isset($topts))
      $topts = $this->testOpts;
    unset($topts['plan']); // We don't want a plan ahead of time.
    return new T($opts);
  }

  /**
   * Parse TAP output into a simple object.
   *
   * @param string $tap  A full TAP output string to parse.
   * @return Mock  An object with the following public properties:
   *
   *  $planned     (int)  Number of tests planned (0 if no tests plan found.)
   *  $ran         (int)  Number of tests that were actually ran.
   *  $failed      (int)  Number of tests that failed.
   *  $skipped     (int)  Number of tests that were skipped.
   *  $todo        (int)  Number of tests that were TODO.
   *
   * As well as a `success()` method with the same API as Test.
   *
   */
  public function parseTAP (string $tap): Mock
  {
    $obj = new Mock;

    preg_match("/^1..(\d+)$/m", $tap, $plan);
    preg_match_all("/^not\s+ok.*?$/m", $tap, $failures);
    preg_match_all("/^ok.*?$/m", $tap, $successes);

    if (isset($plan, $plan[1]))
    {
      $obj->planned = (int)$plan[1];
    }

    if (isset($failures, $failures[0]) && is_array($failures[0]))
    {
      foreach ($failures[0] as $failure)
      {
        $obj->ran++;
        $obj->failed++;
        if (preg_match("/#\s+TODO/i", $failure))
        {
          $obj->todo++;
        }
      }
    }

    if (isset($successes, $successes[0]) && is_array($successes[0]))
    {
      foreach ($successes[0] as $success)
      {
        $obj->ran++;
        if (preg_match("/#\s+SKIP/i", $success))
        {
          $obj->skipped++;
        }
      }
    }

    if ($this->testSuite->verbose >= T::SHOW_DEBUG)
    {
      error_log("# tapObject: ".json_encode($obj));
    }

    return $obj;
  }

  /**
   * Look for any unknown methods in our internal Test instance.
   */
  public function __call ($name, $args)
  {
    $cb = [$this->testSuite, $name];
    if (is_callable($cb))
    {
      return call_user_func_array($cb, $args);
    }
    else
    {
      throw new \Exception("No such method: '$name' in ".get_class($this));
    }
  }

}
