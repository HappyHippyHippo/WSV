<?php

namespace HappyHippyHippo\WSV;

use Generator;
use HappyHippyHippo\TextIO\Exception\FileNotFoundException;
use HappyHippyHippo\TextIO\Exception\FileNotReadableException;
use HappyHippyHippo\TextIO\Exception\FileOpenException;
use HappyHippyHippo\TextIO\Exception\FileReadException;
use HappyHippyHippo\TextIO\InputStream as TextInputStream;
use HappyHippyHippo\WSV\Exception\EndOfLineException;

class InputStream implements InputStreamInterface
{
    /** @var array<int|string, mixed>|null */
    protected ?array $header = null;

    /**
     * @param string $file
     * @param MetadataInterface $metadata
     * @return InputStreamInterface
     */
    public static function make(string $file, MetadataInterface $metadata = new Metadata()): InputStreamInterface
    {
        return new self($file, $metadata);
    }

    /**
     * @param string $file
     * @param MetadataInterface $metadata
     */
    public function __construct(protected string $file, protected MetadataInterface $metadata = new Metadata())
    {
    }

    /**
     * @return Generator
     * @throws FileNotFoundException
     * @throws FileNotReadableException
     * @throws FileOpenException
     * @throws FileReadException
     * @throws EndOfLineException
     */
    public function read(): Generator
    {
        $input = TextInputStream::make($this->file);
        $parser = new InputParser();
        foreach ($input->lines() as $line) {
            // read record
            $record = $parser->parse($line);
            if (count($record) === 0) {
                continue;
            }
            // combine header into keys
            if ($this->metadata->header()) {
                if ($this->header === null) {
                    $this->header = $record;
                    continue;
                }
                if (count($this->header) === count($record)) {
                    $record = array_combine($this->header, $record);
                }
            }
            // transform record fields
            foreach ($record as $key => $value) {
                $record[$key] = $this->metadata->field($key)->transform($value);
            }
            yield $record;
        }
    }
}
