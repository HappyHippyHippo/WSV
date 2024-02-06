<?php

namespace HappyHippyHippo\WSV;

use HappyHippyHippo\TextIO\Encode;
use HappyHippyHippo\TextIO\Exception\FileNotWritableException;
use HappyHippyHippo\TextIO\Exception\FileOpenException;
use HappyHippyHippo\TextIO\Exception\FileWriteException;
use HappyHippyHippo\TextIO\OutputStream as TextOutputStream;
use HappyHippyHippo\TextIO\OutputStreamInterface as TextOutputStreamInterface;

class OutputStream implements OutputStreamInterface
{
    /** @var TextOutputStreamInterface */
    protected TextOutputStreamInterface $stream;

    /** @var OutputParserInterface */
    protected OutputParserInterface $parser;

    /**
     * @param string $file
     * @param Encode $encode
     * @param MetadataInterface $metadata
     * @return OutputStreamInterface
     * @throws FileNotWritableException
     * @throws FileOpenException
     * @throws FileWriteException
     */
    public static function make(
        string $file,
        MetadataInterface $metadata = new Metadata(),
        Encode $encode = Encode::UTF8,
    ): OutputStreamInterface {
        return new self($file, $metadata, $encode);
    }

    /**
     * @param string $file
     * @param MetadataInterface $metadata
     * @param Encode $encode
     * @throws FileNotWritableException
     * @throws FileOpenException
     * @throws FileWriteException
     */
    public function __construct(
        protected string $file,
        protected MetadataInterface $metadata = new Metadata(),
        protected Encode $encode = Encode::UTF8,
    ) {
        $this->stream = TextOutputStream::make($this->file, $this->encode);
        $this->parser = new OutputParser();
    }

    /**
     * @param array<int|string, mixed> $record
     * @return OutputStreamInterface
     * @throws FileWriteException
     */
    public function write(array $record): OutputStreamInterface
    {
        if (count($record) === 0) {
            return $this;
        }
        // transform record fields
        foreach ($record as $key => $value) {
            $record[$key] = $this->metadata->field($key)->transform($value);
        }
        // parse the records value to WSV strings
        $record = $this->parser->parse($record);
        // padding
        $count = count($record);
        foreach ($record as $key => $value) {
            $count--;
            if ($count === 0) {
                continue;
            }
            $strlen = mb_strlen($value, Encode::UTF8->value);
            $target = $this->metadata->field($key)->length();
            if (is_int($target)) {
                $record[$key] = $value . str_repeat(' ', max(1, $target - $strlen));
            }
        }
        // write
        $this->stream->write(implode('', $record) . "\n");
        return $this;
    }
}
