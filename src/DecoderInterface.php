<?php

namespace HappyHippyHippo\WSV;

use Generator;
use HappyHippyHippo\TextIO\Exception\FileNotFoundException;
use HappyHippyHippo\TextIO\Exception\FileNotReadableException;
use HappyHippyHippo\TextIO\Exception\FileOpenException;
use HappyHippyHippo\TextIO\Exception\FileReadException;

interface DecoderInterface
{
    /**
     * @return MetadataInterface
     */
    public function metadata(): MetadataInterface;

    /**
     * @return Generator
     * @throws Exception\EndOfLineException
     * @throws FileNotFoundException
     * @throws FileNotReadableException
     * @throws FileOpenException
     * @throws FileReadException
     */
    public function read(): Generator;
}
