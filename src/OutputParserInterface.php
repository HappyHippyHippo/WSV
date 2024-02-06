<?php

namespace HappyHippyHippo\WSV;

interface OutputParserInterface
{
    /**
     * @param array<int|string, mixed> $data
     * @return array<int|string, mixed>
     */
    public function parse(array $data): array;
}
