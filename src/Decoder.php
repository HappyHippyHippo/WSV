<?php

namespace HappyHippyHippo\WSV;

use Generator;
use HappyHippyHippo\TextIO\Exception\FileNotFoundException;
use HappyHippyHippo\TextIO\Exception\FileNotReadableException;
use HappyHippyHippo\TextIO\Exception\FileOpenException;
use HappyHippyHippo\TextIO\Exception\FileReadException;

class Decoder implements DecoderInterface
{
    /** @var MetadataInterface */
    protected MetadataInterface $metadata;

    /**
     * @param string $file
     * @return DecoderInterface
     */
    public static function make(string $file): DecoderInterface
    {
        return new self($file);
    }

    /**
     * @param string $file
     */
    public function __construct(protected string $file)
    {
        $this->metadata = new Metadata();
    }

    /**
     * @return MetadataInterface
     */
    public function metadata(): MetadataInterface
    {
        return $this->metadata;
    }

    /**
     * @return Generator
     * @throws Exception\EndOfLineException
     * @throws FileNotFoundException
     * @throws FileNotReadableException
     * @throws FileOpenException
     * @throws FileReadException
     */
    public function read(): Generator
    {
        $stream = new InputStream($this->file, $this->metadata);
        foreach ($stream->read() as $record) {
            yield $record;
        }
        $stream = null;
    }
}
