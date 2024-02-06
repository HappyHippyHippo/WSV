<?php

namespace HappyHippyHippo\WSV;

use HappyHippyHippo\TextIO\Exception\FileNotWritableException;
use HappyHippyHippo\TextIO\Exception\FileOpenException;
use HappyHippyHippo\TextIO\Exception\FileWriteException;

class Encoder implements EncoderInterface
{
    /** @var MetadataInterface */
    protected MetadataInterface $metadata;

    /** @var OutputStreamInterface */
    protected OutputStreamInterface $stream;

    /**
     * @param string $file
     * @return EncoderInterface
     * @throws FileNotWritableException
     * @throws FileOpenException
     * @throws FileWriteException
     */
    public static function make(string $file): EncoderInterface
    {
        return new self($file);
    }

    /**
     * @param string $file
     * @throws FileWriteException
     * @throws FileNotWritableException
     * @throws FileOpenException
     */
    public function __construct(protected string $file)
    {
        $this->metadata = new Metadata();
        $this->stream = new OutputStream($this->file, $this->metadata);
    }

    /**
     * @return MetadataInterface
     */
    public function metadata(): MetadataInterface
    {
        return $this->metadata;
    }

    /**
     * @param array<int|string, mixed> $record
     * @return EncoderInterface
     * @throws FileWriteException
     */
    public function write(array $record): EncoderInterface
    {
        $this->stream->write($record);
        return $this;
    }
}
