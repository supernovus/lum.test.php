<?php

namespace Lum;

class Test
{
  const TRACE_NONE = 0;
  const TRACE_ONE  = 1;
  const TRACE_FULL = 2;

  public bool $debug = false;

  protected int $tapVersion = 12;
  protected int $ran = 0;
  protected int $failed = 0;
  protected int $skipped = 0;
  protected int $todo = 0;
  protected int $planned = 0;
  protected int $stackTrace = 0;
  protected array $logs = [];

  public int $traceLevel = 0;

  /**
   * Build a Test object.
   *
   * @param array $opts  Options for the Test instance:
   *
   *  'plan'     (int)   The number of tests we have planned.
   *                     Default: 0 (unplanned)
   *
   *  'trace'    (int)   The level of stack trace details we should return.
   *                     Default: 0 (no stack trace)
   *
   *  'version'  (int)   The TAP version.
   *                     Default: 12 (currently only supported version).
   *
   */
  public function __construct (array $opts=[])
  {
    if (isset($opts['plan']))
    {
      $this->plan($opts['plan']);
    }
    if (isset($opts['trace']))
    {
      $this->trace($opts['trace']);
    }
    if (isset($opts['version']))
    {
      $this->version($opts['version']);
    }
  }

  /**
   * Get the number of tests planned.
   */
  public function planned (): int
  {
    return $this->planned;
  }

  /**
   * Get the number of tests ran.
   */
  public function ran (): int
  {
    return $this->ran;
  }

  /**
   * Get the number of tests that failed.
   */
  public function failed (): int
  {
    return $this->failed;
  }

  /**
   * Get the number of tests that were skipped.
   */
  public function skipped (): int
  {
    return $this->skipped;
  }

  /**
   * Get the number of tests that are TODO.
   */
  public function areTodo (): int
  {
    return $this->todo;
  }

  /**
   * Get the logs.
   */
  public function getLogs (): array
  {
    return $this->logs;
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
  public function trace (int $level=self::TRACE_ONE): static
  {
    $this->stackTrace = $level;
    return $this;
  }

  /**
   * Change number of tests planned.
   *
   * @param int $count  Number of tests planned.
   *
   *   A value less than 1 means we don't care about the number of tests,
   *   and won't report the test plan up front.
   *
   * @return Test  Returns $this.
   */
  public function plan (int $count): static
  {
    $this->planned = $count;
    return $this;
  }

  /**
   * Change or get the TAP version.
   *
   * @param int $ver (Optional) If specified, set the TAP version.
   *
   * The TAP version can be 12 or 13. Those are the only two supported
   * versions. If you specify something other than one of those, an
   * Exception will be thrown.
   *
   * Support for version 13 is being planned, and will require the
   * YAML PHP extension to be available.
   *
   * @return int|Test  If $ver was null, returns the current version.
   *                   Otherwise, returns $this.
   */
  public function version (?int $ver=null): int|static
  {
    if (isset($ver) && is_int($ver))
    {
      if ($ver == 12 || $ver == 13)
      {
        $this->tapVersion = $ver;
      }
      else
      {
        throw new \Exception("Invalid TAP version, must be 12 or 13.");
      }
    }
    else
    {
      return $this->tapVersion;
    }
  }

  /**
   * Does a test pass?
   *
   * Every other test method calls this behind the scenes.
   *
   * @param bool $test  The evaluated test value.
   * @param ?string $desc  (Optional) Description of test.
   * @param mixed $directive (Optional) Added details for test.
   *
   * @return Test_Log  The log entry for this test.
   */
  public function ok (bool $test, ?string $desc=null, mixed $directive=null): Test_Log
  {
    $log = new Test_Log();
    $this->ran++;
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
   * @param ?string $desc  (Optional) Description of test.
   * @param mixed $directive (Optional) Added details for test.
   *
   * @return Test_Log  The log entry for this test.
   */
  public function fail (?string $desc=null, mixed $directive=null): Test_Log
  {
    $this->traceLevel++;
    $log = $this->ok(false, $desc, $directive);
    $this->traceLevel--;
    return $log;
  }

  /**
   * Pass a test.
   *
   * @param ?string $desc  (Optional) Description of test.
   * @param mixed $directive (Optional) Added details for test.
   *
   * @return Test_Log  The log entry for this test.
   */
  public function pass (?string $desc=null, mixed $directive=null)
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
   * @param ?string $desc  (Optional) Description of test.
   *
   * @return Test_Log  The log entry for this test.
   */
  public function dies (callable $test, ?string $desc=null)
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
   * Test two values with a specified comparitor.
   *
   * @param mixed $got  Value we got from the test.
   * @param mixed $want  Value we wanted/expected.
   * @param string $comparitor  A comparitor to test with:
   *
   *   - '===', 'is',
   *   - '!==', 'isnt',
   *   - '==',  'eq',
   *   - '!=',  'ne',
   *   - '>',   'gt',
   *   - '<',   'lt',
   *   - '>='   'ge',  'gte'
   *   - '<=',  'le',  'lte'
   *    
   * @param string $desc (Optional) Description of test.
   * @param bool $stringify (Optional, default true) JSONify output?
   *
   * @param Test_Log  The log entry for this test.
   */
  public function cmp (
    mixed $got, 
    mixed $want, 
    string $comp, 
    ?string $desc=null, 
    bool $stringify=true): Test_Log
  {
    switch ($comp)
    {
      case 'is':
      case '===':
        $test = ($got === $want);
        break;

      case 'isnt':
      case '!==':
        $test = ($got !== $want);
        break;

      case 'eq':
      case '==':
        $test = ($got == $want);
        break;

      case 'ne':
      case '!=':
        $test = ($got != $want);
        break;

      case 'lt':
      case '<':
        $test = ($got < $want);
        break;

      case 'gt':
      case '>':
        $test = ($got > $want);
        break;

      case 'le':
      case 'lte':
      case '<=':
        $test = ($got <= $want);
        break;

      case 'ge':
      case 'gte':
      case '>=':
        $test = ($got >= $want);
        break;

      default:
        $test = false;
    }
    $this->traceLevel++;
    $log = $this->ok($test, $desc);
    $this->traceLevel--;
    if (!$test)
    {
      $log->details['got'] = $got;
      $log->details['wanted'] = $want;
      $log->details['comparitor'] = $comp;
      $log->details['stringify'] = $stringify;
    }
    return $log;
  }

  /**
   * See if something is a 'whatever'.
   *
   * TODO: document this, and add a test file for it.
   */
  public function isa ($got, $want, $desc=null, $stringify=true)
  {
    $ok = false;
    if (is_object($got))
    {
      if (is_string($want) && strtolower($want) === 'object')
      {
        $ok = true;
      }
      elseif ($got instanceof $what)
      {
        $ok = true;
      }
    }
    elseif (is_string($want))
    {
      $want = strtolower($want);
      $type = strtolower(get_debug_type($got));
      if ($what === $type)
      {
        $ok = true;
      }
      else
      {
        $type = strtolower(gettype($got));
        if ($what === $type)
        {
          $ok = true;
        }
      }
    }

    $this->traceLevel++;
    $log = $this->ok($ok, $desc);
    $this->traceLevel--;

    // TODO: more details?
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
    $this->traceLevel++;
    $log = $this->cmp($got, $want, '===', $desc, $stringify);
    $this->traceLevel--;
    return $log;
  }

  /**
   * Test to see if two values are NOT equal (using !== comparison).
   *
   * @param mixed $got  Value we got from the test.
   * @param mixed $want Value we wanted/expected.
   * @param string $desc (Optional) Description of test.
   * @param bool $stringify (Optional, default true) JSONify output?
   * @return Test_Log  The log entry for this test.
   */
  public function isnt ($got, $want, $desc=null, $stringify=true)
  {
    $this->traceLevel++;
    $log = $this->cmp($got, $want, '!==', $desc, $stringify);
    $this->traceLevel--;
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
    $log->skipped = true;
    if (isset($reason) && is_string($reason))
    {
      $log->reason = $reason;
    }
    $this->skipped++;
    return $log;
  }

  /**
   * Mark a test as TODO.
   *
   * @param string $reason  (Optional) What needs to be done to pass the test.
   * @param string $desc    (Optional) Description of test.
   * @return Test_Log  The log entry for this test.
   */
  public function todo ($reason=null, $desc=null)
  {
    $this->traceLevel++;
    $log = $this->ok(false, $desc);
    $this->traceLevel--;
    $log->todo = true;
    if (isset($reason) && is_string($reason))
    {
      $log->reason = $reason;
    }
    $this->todo++;
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

    if ($this->planned > 0 && $this->planned != $this->ran)
    {
      $out .= "# Looks like you planned '{$this->planned}' but ran '{$this->ran}' tests\n";
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
  public bool $ok = false;

  /**
   * The test was skipped.
   */
  public bool $skipped = false;

  /**
   * The test is TODO.
   */
  public bool $todo = false;

  /**
   * The reason the test was skipped.
   */
  public string $reason = '';

  /**
   * Description of the test.
   */
  public ?string $desc = null;

  /**
   * Extra directive message, or a Throwable that was thrown.
   */
  public mixed $directive = null;

  public array $details = [];
  public int $traceLevel = 0;
  public ?array $stackTrace = null;
  public bool $fullTrace = false;

  public function tap (int $num)
  {
    $out;
    if ($this->ok)
      $out = 'ok ';
    else
      $out = 'not ok ';

    $out .= $num;

    if (is_string($this->desc))
      $out .= ' - ' . $this->desc;

    if (is_string($this->directive))
      $out .= ' # ' . $this->directive;
    elseif ($this->directive instanceof \Throwable)
      $out .= ' # ' . get_class($this->directive)
      . ': ' . $this->directive->getMessage();
    elseif (!is_null($this->directive))
      $out .= ' # ' . json_encode($this->directive);
    elseif ($this->skipped)
      $out .= ' # SKIP ' . $this->reason;
    elseif ($this->todo)
      $out .= ' # TODO ' . $this->reason;

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
      $out .= "#  expected: $want\n";
      $out .= "#       got: $got\n";
      if (isset($this->details['comparitor']))
      {
        $out .= "#        op: {$this->details['comparitor']}\n";
      }
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
