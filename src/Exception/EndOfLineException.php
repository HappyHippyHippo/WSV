<?php

namespace HappyHippyHippo\WSV\Exception;

use Throwable;

class EndOfLineException extends Exception
{
    public function __construct(int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct('Unexpected end of line', $code, $previous);
    }
}
