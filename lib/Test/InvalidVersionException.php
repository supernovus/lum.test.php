<?php

namespace Lum\Test;

class InvalidVersionException extends \Exception
{
  protected $message = 'Invalid TAP version, must be 12 or 13';
}