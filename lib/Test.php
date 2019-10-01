<?php

namespace Lum;

class Test
{
  protected $failed = 0;
  protected $skipped = 0;
  protected $planned = 0;
  protected $stackTrace = 0;
  protected $traceLevel = 0;
  protected $logs = [];

  /**
   * Build a Test object.
   *
   * @param int $plan  (optional) Number of tests planned.
   * @param int $trace (optional) Stack trace level (default 0).
   */
  public function __construct ($plan=null, $trace=null)
  {
    if (isset($plan))
    {
      $this->plan($plan);
    }
    if (isset($trace))
    {
      $this->trace($trace);
    }
  }

  /**
   * Change stack trave level.
   *
   * @param int $level (default 1) Level of detail for stack traces.
   *                               0 = No stack traces.
   *                               1 = Show only test that failed.
   *                               2 = Show full stack trace.
   *
   * @return Test  Returns $this.
   */
  public function trace ($level=1)
  {
    if (is_int($level))
    {
      $this->stackTrace = $level;
    }
    else
    {
      $this->stackTrace = 0;
    }

    return $this;
  }

  /**
   * Change number of tests planned.
   *
   * @param int $count  Number of tests planned.
   *
   * @return Test  Returns $this.
   */
  public function plan ($count)
  {
    if (is_int($count))
    {
      $this->planned = $count;
    }
    else
    {
      $this->planned = 0;
    }

    return $this;
  }

  /**
   * Does a test pass?
   *
   * @param bool $test  The evaluated test value.
   * @param string $desc  (Optional) Description of test.
   * @param string $directive (Optional) Added details for test.
   * @return Test_Log  The log entry for this test.
   */
  public function ok ($test, $desc=null, $directive=null)
  {
    $log = new Test_Log();
    if ($test)
    {
      $log->ok = true;
    }
    else
    {
      $this->failed++;
      if ($this->stackTrace)
      {
        $log->traceLevel = $this->traceLevel;
        $log->stackTrace = debug_backtrace();
        if ($this->stackTrace > 1)
        {
          $log->fullTrace = true;
        }
      }
    }

    if (isset($desc))
    {
      $log->desc = $desc;
    }

    if (isset($directive))
    {
      $log->directive = $directive;
    }

    $this->logs[] = $log;

    return $log;
  }

  /**
   * Fail a test.
   *
   * @param string $desc  (Optional) Description of test.
   * @param string $directive (Optional) Added details for test.
   * @return Test_Log  The log entry for this test.
   */
  public function fail ($desc=null, $directive=null)
  {
    $this->traceLevel++;
    $log = $this->ok(false, $desc, $directive);
    $this->traceLevel--;
    return $log;
  }

  /**
   * Pass a test.
   *
   * @param string $desc  (Optional) Description of test.
   * @param string $directive (Optional) Added details for test.
   * @return Test_Log  The log entry for this test.
   */
  public function pass ($desc=null, $directive=null)
  {
    $this->traceLevel++;
    $log = $this->ok(true, $desc, $directive);
    $this->traceLevel--;
    return $log;
  }

  /**
   * Test to see if a closure throws an exception/error.
   *
   * @param Callable $test  The Closure or Callable to test.
   * @param string $desc  (Optional) Description of test.
   * @return Test_Log  The log entry for this test.
   */
  public function dies ($test, $desc=null)
  {
    $this->traceLevel++;
    $ok = false;
    $err = null;
    try
    {
      $test();
    }
    catch(\Throwable $e)
    {
      $ok = true;
      $err = $e;
    }
    $log = $this->ok($ok, $desc, $err);
    $this->traceLevel--;
    return $log;
  }

  /**
   * Test to see if two values are equal (using === comparison).
   *
   * @param mixed $got  Value we got from the test.
   * @param mixed $want Value we wanted/expected.
   * @param string $desc (Optional) Description of test.
   * @param bool $stringify (Optional, default true) JSONify output?
   * @return Test_Log  The log entry for this test.
   */
  public function is ($got, $want, $desc=null, $stringify=true)
  {
    $test = ($got === $want);
    $this->traceLevel++;
    $log = $this->ok($test, $desc);
    $this->traceLevel--;
    if (!$test)
    {
      $log->details['got'] = $got;
      $log->details['wanted'] = $want;
      $log->details['stringify'] = $stringify;
    }
    return $log;
  }

  /**
   * Test to see if two structures are the same.
   *
   * It stringifies the structures to JSON then compares the two
   * JSON strings. Note that properties are not guaranteed to be
   * encoded in the same order, so be careful with this.
   *
   * @param mixed $got  Value we got from the test.
   * @param mixed $want Value we wanted/expected.
   * @param string $desc (Optional) Description of test.
   * @return Test_Log  The log entry for this test.
   */
  public function isJSON ($got, $want, $desc=null)
  {
    $got = json_encode($got);
    $want = json_encode($want);
    $this->traceLevel++;
    $log = $this->is($got, $want, $desc, false);
    $this->traceLevel--;
    return $log;
  }

  /**
   * Test to see if two structures are the same.
   *
   * This uses serialize instead of JSON. For advanced use.
   * 
   * @param mixed $got  Value we got from the test.
   * @param mixed $want Value we wanted/expected.
   * @param string $desc (Optional) Description of test.
   * @param bool $rawOut (Optional, default false) Show serialized output.
   * @return Test_Log  The log entry for this test.
   */
  public function isSerialized ($got, $want, $desc=null, $rawOut=false)
  {
    $gotS = serialize($got);
    $wantS = serialize($want);
    $test = ($gotS === $wantS);
    $this->traceLevel++;
    $log = $this->ok($test, $desc);
    $this->traceLevel--;
    if (!$test)
    {
      if ($rawOut)
      {
        $log->details['got'] = $gotS;
        $log->details['want'] = $wantS;
        $log->details['stringify'] = false;
      }
      else
      {
        $log->details['got'] = $got;
        $log->details['want'] = $want;
        $log->details['stringify'] = true;
      }
    }
    return $log;
  }

  /**
   * Skip a test.
   *
   * @param string $reason  (Optional) The reason we are skipping the test.
   * @param string $desc    (Optional) Description of test.
   * @return Test_Log  The log entry for this test.
   */
  public function skip ($reason=null, $desc=null)
  {
    $this->traceLevel++;
    $log = $this->ok(true, $desc);
    $this->traceLevel--;
    if (isset($reason) && is_string($reason))
    {
      $log->skippedReason = $reason;
    }
    $this->skipped++;
    return $log;
  }

  /**
   * Add a diagnosis message to the logs.
   *
   * @param mixed $msg  The message we are adding.
   *
   * If the $msg is a string, it will be shown directly.
   * If it is anything other than a string, it will be encoded as JSON.
   *
   * @return Test  Returns $this.
   */
  public function diag ($msg)
  {
    $this->logs[] = $msg;
  }

  /**
   * Convert the test logs into TAP output and return it as a string.
   *
   * @return string  The TAP output.
   */
  public function tap ()
  {
    $out = '';
    if ($this->planned > 0)
    {
      $out .= "1..{$this->planned}\n";
    }

    $t = 1; // Test number.
    foreach ($this->logs as $log)
    {
      if ($log instanceof Test_Log)
      { // It's a log.
        $out .= $log->tap($t++);
      }
      else
      {
        $out .= (is_string($log) ? $log : json_encode($log));
      }
    }

    if ($this->skipped)
    {
      $out .= "# Skipped {$this->skipped} tests\n";
    }

    if ($this->failed)
    {
      $out .= "# Failed {$this->failed} ".($this->failed>1 ? 'tests' : 'test');
      if ($this->planned)
      {
        $out .= " out of {$this->planned}";
      }
      $out .= "\n";
    }

    $ran = $t-1;
    if ($this->planned > 0 && $this->planned != $ran)
    {
      $out .= "# Looks like you planned '{$this->planned}' but ran '$ran' tests\n";
    }

    return $out;
  }

}

/**
 * Internal child class used to represent individual test logs.
 */
class Test_Log
{
  /**
   * The test succeeded.
   */
  public $ok = false;

  /**
   * The test was skipped.
   */
  public $skipped = false;

  /**
   * The reason the test was skipped.
   */
  public $skippedReason = '';

  /**
   * Description of the test.
   */
  public $desc = null;

  /**
   * Extra directive message, or a Throwable that was thrown.
   */
  public $directive = null;
  public $details = [];
  public $traceLevel = 0;
  public $stackTrace = null;
  public $fullTrace = false;

  public function tap ($num)
  {
    $out;
    if ($this->ok)
      $out = 'ok ';
    else
      $out = 'not ok ';

    if (is_string($this->desc))
      $out .= ' - ' . $this->desc;

    if (is_string($this->directive))
      $out .= ' # ' . $this->directive;
    elseif ($this->directive instanceof \Throwable)
      $out .= ' # ' . $this->directive->getMessage();
    elseif ($this->skipped)
      $out .= ' # SKIP ' . $this->skippedReason;

    $out .= "\n";

    if (isset($this->stackTrace))
    {
      $stack = $this->stackTrace;
      if ($this->fullTrace)
      {
        $spaces = 0;
        foreach ($stack as $trace)
        {
          $this->trace_out($out, $trace, ++$spaces);
        }
      }
      else
      {
        $this->trace_out($out, $stack[$this->traceLevel]);
      }
    }

    if (isset($this->details['got'], $this->details['wanted']))
    {
      $got = $this->details['got'];
      $want = $this->details['wanted'];
      if (isset($this->details['stringify']) && $this->details['stringify'])
      {
        $got = json_encode($got);
        $want = json_encode($want);
      }
      $out .= "#       got: $got\n";
      $out .= "#  expected: $want\n";
    }

    return $out;
  }

  protected function trace_out (&$out, $trace, $spacing=1)
  { 
    $space = str_repeat('  ', $spacing);
    foreach (['file','line','function','class'] as $prop)
    {
      if (isset($trace[$prop]))
      {
        $out .= "#{$space}$prop: {$trace[$prop]}\n";
      }
    }
  }

}
