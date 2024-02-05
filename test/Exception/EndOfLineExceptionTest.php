<?php

namespace HappyHippyHippo\WSV\tests\Exception;

use Exception;
use HappyHippyHippo\WSV\Exception\EndOfLineException;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \HappyHippyHippo\WSV\Exception\EndOfLineException
 */
class EndOfLineExceptionTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::__construct
     */
    public function testConstructor(): void
    {
        $code = 123;
        $reason = new Exception();

        $exception = new EndOfLineException($code, $reason);

        $this->assertEquals('Unexpected end of line', $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertEquals($reason, $exception->getPrevious());
    }
}
