<?php

namespace HappyHippyHippo\WSV;

use Generator;
use HappyHippyHippo\TextIO\Exception\FileNotFoundException;
use HappyHippyHippo\TextIO\Exception\FileNotReadableException;
use HappyHippyHippo\TextIO\Exception\FileOpenException;
use HappyHippyHippo\TextIO\Exception\FileReadException;
use HappyHippyHippo\WSV\Exception\EndOfLineException;

interface InputStreamInterface
{
    /**
     * @return Generator
     * @throws FileNotFoundException
     * @throws FileNotReadableException
     * @throws FileOpenException
     * @throws FileReadException
     * @throws EndOfLineException
     */
    public function read(): Generator;
}
