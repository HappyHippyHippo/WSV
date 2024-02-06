<?php

namespace HappyHippyHippo\WSV;

use HappyHippyHippo\TextIO\Encode;
use IntlChar;

class OutputParser implements OutputParserInterface
{
    /**
     * @param array<int|string, mixed> $data
     * @return array<int|string, mixed>
     */
    public function parse(array $data): array
    {
        $content = [];
        foreach ($data as $key => $value) {
            $content[$key] = $this->encode($value);
        }
        return $content;
    }

    /**
     * @param mixed $value
     * @return string
     */
    protected function encode(mixed $value): string
    {
        if (is_null($value)) {
            return '-';
        }
        if (is_string($value)) {
            if ($value === '') {
                return '""';
            }
            $chars = array_map([IntlChar::class, 'ord'], mb_str_split($value, 1, Encode::UTF8->value));
            $chars = $this->encodeString($chars);
            $value = implode(array_map([IntlChar::class, 'chr'], $chars));
        }
        return $value;
    }

    /**
     * @param int[] $chars
     * @return int[]
     */
    protected function encodeString(array $chars): array
    {
        foreach ($chars as $index => $char) {
            if (Char::isEOL($char)) {
                return array_merge(
                    $this->enclose($this->encodeSingleLinedString(array_slice($chars, 0, $index))),
                    [Char::slash()],
                    $this->enclose($this->encodeString(array_slice($chars, $index + 1))),
                );
            }
        }
        return $this->encodeSingleLinedString($chars);
    }

    /**
     * @param int[] $chars
     * @return int[]
     */
    protected function encodeSingleLinedString(array $chars): array
    {
        $enclose = false;
        $count = count($chars);
        for ($i = 0; $i < $count; $i++) {
            $char = $chars[$i];
            if (Char::isDoubleQuote($char)) {
                $enclose = true;
                array_splice($chars, $i, 1, [Char::doubleQuote(), Char::doubleQuote()]);
                $i++;
                $count++;
            }
            if (Char::isWhitespace($char) || Char::isComment($char) || Char::isNull($char)) {
                $enclose = true;
            }
        }
        if ($enclose) {
            $chars = $this->enclose($chars);
        }
        return $chars;
    }

    /**
     * @param int[] $chars
     * @return int[]
     */
    private function enclose(array $chars): array
    {
        if (!Char::isDoubleQuote($chars[0])) {
            array_unshift($chars, Char::doubleQuote());
            $chars[] = Char::doubleQuote();
        }
        return $chars;
    }
}
