<?php

namespace Lum\Test;

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
  public $debug = false;
  protected $testSuite;
  protected $testFiles = [];
  protected $testOutput = [];
  protected $testInstances = [];

  /**
   * Build a new Test Harness.
   *
   * Initializes an internal Test instance that will be used to
   * keep track of whether the tests we run succeeded or failed.
   *
   * The constructor takes no parameters.
   */
  public function __construct ()
  {
    $this->testSuite = new \Lum\Test();
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
      $this->tests[] = $file;
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
          $this->tests[] = join(DIRECTORY_SEPARATOR, [$dir, $file]);
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
      $this->testSuite->plan(count($this->tests));
    }
    foreach ($this->tests as $testfile)
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
  public function runTest ($__test_file)
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
    if (isset($returnVal) && $returnVal instanceof \Lum\Test)
    { // Use the Test instance to determine success.
      $testsFailed = $returnVal->failed() - $returnVal->areTodo();
      $this->testSuite->is($testsFailed, 0, $__test_file);
    }
    elseif (isset($outputVal) && trim($outputVal) != '')
    {
      $parsed = $this->parseTAP($outputVal);
      if (isset($parsed))
      {
        $testsFailed = $parsed->failed - $parsed->todo;
        $this->testSuite->is($testsFailed, 0, $__test_file);
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
   * Get a summary of the test results.
   *
   * @return string  TAP output from our test suite.
   */
  public function summary ()
  {
    return $this->testSuite->tap();
  }

  /**
   * Did all of the tests pass, and did the number of tests ran match
   * the plan if it was passed.
   *
   * @return bool
   */
  public function success ()
  {
    return ($this->testSuite->failed() == 0
      && ($this->testSuite->planned() == 0 
      || $this->testSuite->planned() == $this->testSuite->ran())
   );
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
   * Parse TAP output into a simple object.
   *
   * @param string $tap  A full TAP output string to parse.
   * @return object  A simple object with the following public properties:
   *
   *  $planned     (int)  Number of tests planned (0 if no tests plan found.)
   *  $ran         (int)  Number of tests that were actually ran.
   *  $failed      (int)  Number of tests that failed.
   *  $skipped     (int)  Number of tests that were skipped.
   *  $todo        (int)  Number of tests that were TODO.
   *
   * To determine if a TAP suite was successful:
   *
   *  ($object->failed - $object->todo == 0)
   */
  public function parseTAP ($tap)
  {
    $obj = new \StdClass();
    $obj->planned = 0;
    $obj->ran     = 0;
    $obj->failed  = 0;
    $obj->skipped = 0;
    $obj->todo    = 0;

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

    if ($this->debug)
    {
      error_log("# tapObject: ".json_encode($obj));
    }

    return $obj;
  }
}
