<?php

namespace HappyHippyHippo\WSV;

use HappyHippyHippo\TextIO\Exception\FileWriteException;

interface OutputStreamInterface
{
    /**
     * @param array<int|string, mixed> $record
     * @return OutputStreamInterface
     * @throws FileWriteException
     */
    public function write(array $record): OutputStreamInterface;
}
