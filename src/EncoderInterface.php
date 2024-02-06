<?php

namespace HappyHippyHippo\WSV;

use HappyHippyHippo\TextIO\Exception\FileWriteException;

interface EncoderInterface
{
    /**
     * @return MetadataInterface
     */
    public function metadata(): MetadataInterface;

    /**
     * @param array<int|string, mixed> $record
     * @return EncoderInterface
     * @throws FileWriteException
     */
    public function write(array $record): EncoderInterface;
}
