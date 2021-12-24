<?php

namespace Lum\Test;

/** 
 * A fake test.
 */
class Mock
{
  public $planned = 0;
  public $ran     = 0;
  public $failed  = 0;
  public $skipped = 0;
  public $todo    = 0;

  public function success (bool $noTodo=true): bool
  {
    $failed = $this->failed;
    if ($noTodo)
      $failed -= $this->todo;
    return ($failed == 0
      && ($this->planned == 0
      || $this->planned == $this->ran));
  }

}