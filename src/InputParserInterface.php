<?php

namespace HappyHippyHippo\WSV;

use HappyHippyHippo\WSV\Exception\EndOfLineException;

interface InputParserInterface
{
    /**
     * @param string $data
     * @return array<int, mixed>
     * @throws EndOfLineException
     */
    public function parse(string $data): array;
}
