<?php

namespace HappyHippyHippo\WSV;

use HappyHippyHippo\TextIO\Encode;
use HappyHippyHippo\WSV\Exception\EndOfLineException;
use IntlChar;

class InputParser implements InputParserInterface
{
    /** @var int[] */
    protected array $chars;

    /** @var int */
    protected int $length;

    /** @var int */
    protected int $index;

    /**
     * @param string $data
     * @return array<int, mixed>
     * @throws EndOfLineException
     */
    public function parse(string $data): array
    {
        $this->chars = array_map([IntlChar::class, 'ord'], mb_str_split($data, 1, Encode::UTF8->value));
        $this->length = count($this->chars);
        $this->index = 0;

        $record = [];
        while (!$this->isEOL()) {
            $this->removeWhitespaces();
            $this->removeComment();
            if (!$this->isEOL()) {
                $record[] = $this->read();
            }
        }
        return $record;
    }

    /**
     * @return void
     */
    protected function removeWhitespaces(): void
    {
        while (!$this->isEOL() && $this->isWhitespace()) {
            $this->index++;
        }
    }

    /**
     * @return void
     */
    protected function removeComment(): void
    {
        if (!$this->isEOL() && $this->isComment()) {
            $this->index = $this->length;
        }
    }

    /**
     * @return mixed
     * @throws EndOfLineException
     */
    protected function read(): mixed
    {
        return match ($this->chars[$this->index]) {
            Char::null() => $this->readNull(),
            Char::doubleQuote() => $this->readString(),
            default => $this->readValue(),
        };
    }

    /**
     * @return mixed
     */
    protected function readNull(): mixed
    {
        $this->index++;
        return null;
    }

    /**
     * @return string
     * @throws EndOfLineException
     */
    protected function readString(): string
    {
        $result = [];
        $this->index++;
        do {
            if ($this->isEOL()) {
                throw new EndOfLineException();
            }
            if ($this->isDoubleQuote()) {
                $this->index++;
                // no more content
                if ($this->isEOL()) {
                    break;
                }
                // escaped double-quote
                if ($this->isDoubleQuote()) {
                    $result[] = $this->chars[$this->index];
                    $this->index++;
                    continue;
                }
                // multi-line string
                if (
                    $this->isSlash() &&
                    !$this->isEOL($this->index + 1) &&
                    $this->isDoubleQuote($this->index + 1)
                ) {
                    $result[] = 0x0A;
                    $this->index++;
                    $this->index++;
                    continue;
                }
                break;
            }
            $result[] = $this->chars[$this->index];
            $this->index++;
        } while (true);

        return implode(array_map([IntlChar::class, 'chr'], $result));
    }

    /**
     * @return string
     */
    protected function readValue(): string
    {
        $result = [];
        do {
            $result[] = $this->chars[$this->index];
            $this->index++;
        } while (!$this->isEOL() && !$this->isWhitespace() && !$this->isDoubleQuote() && !$this->isComment());
        return implode(array_map([IntlChar::class, 'chr'], $result));
    }

    /**
     * @param int|null $index
     * @return bool
     */
    protected function isEOL(?int $index = null): bool
    {
        if ($index === null) {
            $index = $this->index;
        }
        return $index >= $this->length || Char::isEOL($this->chars[$index]);
    }

    /**
     * @param int|null $index
     * @return bool
     */
    protected function isWhitespace(?int $index = null): bool
    {
        if ($index === null) {
            $index = $this->index;
        }
        return Char::isWhitespace($this->chars[$index]);
    }

    /**
     * @param int|null $index
     * @return bool
     */
    protected function isComment(?int $index = null): bool
    {
        if ($index === null) {
            $index = $this->index;
        }
        return Char::isComment($this->chars[$index]);
    }

    /**
     * @param int|null $index
     * @return bool
     */
    protected function isSlash(?int $index = null): bool
    {
        if ($index === null) {
            $index = $this->index;
        }
        return Char::isSlash($this->chars[$index]);
    }

    /**
     * @param int|null $index
     * @return bool
     */
    protected function isDoubleQuote(?int $index = null): bool
    {
        if ($index === null) {
            $index = $this->index;
        }
        return Char::isDoubleQuote($this->chars[$index]);
    }
}
