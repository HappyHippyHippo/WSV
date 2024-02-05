<?php

namespace HappyHippyHippo\WSV;

use HappyHippyHippo\TextIO\Encode;
use HappyHippyHippo\WSV\Exception\EndOfLineException;
use HappyHippyHippo\WSV\Exception\Exception;
use IntlChar;

class Parser
{
    /** @var int[] */
    protected array $chars;

    /** @var int */
    protected int $length;

    /** @var int */
    protected int $index;

    /**
     * @param string $text
     * @param Encode $encoding
     * @return string[]
     * @throws Exception
     */
    public function parse(string $text, Encode $encoding = Encode::UTF8): array
    {
        $this->chars = array_map([IntlChar::class, 'ord'], mb_str_split($text, 1, $encoding->value));
        $this->length = count($this->chars);
        $this->index = 0;

        $result = [];

        while (!$this->isEOL()) {
            $this->removeWhitespaces();
            $this->removeComment();
            if (!$this->isEOL()) {
                $result[] = $this->read();
            }
        }

        return $result;
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
     * @throws Exception
     */
    protected function read(): mixed
    {
        return match ($this->chars[$this->index]) {
            IntlChar::ord('-') => $this->readNull(),
            IntlChar::ord('"') => $this->readString(),
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
     * @throws Exception
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
                if ($this->isSlash() && !$this->isEOL($this->index + 1) && $this->isDoubleQuote($this->index + 1)) {
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
        return $index >= $this->length || $this->chars[$index] === IntlChar::ord("\n");
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
        return match ($this->chars[$index]) {
            0x09,0x0A,0x0B,0x0C,0x0D,0x20,0x85,0xA0,
            0x1680,0x2000,0x2001,0x2002,
            0x2003,0x2004,0x2005,0x2006,
            0x2007,0x2008,0x2009,0x200A,
            0x2028,0x2029,0x202F,0x205F,0x3000 => true,
            default => false,
        };
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
        return $this->chars[$index] === IntlChar::ord('#');
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
        return $this->chars[$index] === IntlChar::ord('/');
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
        return $this->chars[$index] === IntlChar::ord('"');
    }
}
