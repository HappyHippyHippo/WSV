<?php

namespace HappyHippyHippo\WSV\tests;

use HappyHippyHippo\WSV\OutputParser;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \HappyHippyHippo\WSV\OutputParser
 */
class OutputParserTest extends TestCase
{
    /**
     * @param array<int, mixed> $data
     * @param string[] $expected
     * @return void
     *
     * @covers ::parse
     * @covers ::encode
     * @covers ::encodeString
     * @covers ::encodeSingleLinedString
     * @covers ::enclose
     * @dataProvider provideDataToParseTest
     */
    public function testParse(array $data, array $expected): void
    {
        $this->assertEquals($expected, (new OutputParser())->parse($data));
    }

    /**
     * @return array<string, mixed>
     */
    public static function provideDataToParseTest(): array
    {
        return [
            'empty record' => [
                'data' => [],
                'expected' => [],
            ],
            'single null value record' => [
                'data' => [null],
                'expected' => ['-'],
            ],
            'multiple null value record' => [
                'data' => [null, null, null],
                'expected' => ['-', '-', '-'],
            ],
            'single value record' => [
                'data' => [123],
                'expected' => ['123'],
            ],
            'multiple value record' => [
                'data' => [123, 456.789, 321],
                'expected' => ['123', '456.789', '321'],
            ],
            'single empty string value record' => [
                'data' => [''],
                'expected' => ['""'],
            ],
            'multiple empty string value record' => [
                'data' => ['', 'abc', '', 'def'],
                'expected' => ['""', 'abc', '""', 'def'],
            ],
            'single multi lined string value record' => [
                'data' => ["abc\ndef"],
                'expected' => ['"abc"/"def"'],
            ],
            'multiple multi lined string value record' => [
                'data' => [123, "abc\ndef", 'abc', "ghi\njkl", 456.789],
                'expected' => ['123', '"abc"/"def"', 'abc', '"ghi"/"jkl"', '456.789'],
            ],
            'string with double quote value record' => [
                'data' => ['a"b"c'],
                'expected' => ['"a""b""c"'],
            ],
            'string with whitespace value record' => [
                'data' => ['a b c'],
                'expected' => ['"a b c"'],
            ],
            'string with dash value record' => [
                'data' => ['a-b-c'],
                'expected' => ['"a-b-c"'],
            ],
            'string with comment value record' => [
                'data' => ['a#b#c'],
                'expected' => ['"a#b#c"'],
            ],
            'string with null value record' => [
                'data' => ['-'],
                'expected' => ['"-"'],
            ],
            'multiple enclose rules test' => [
                'data' => ['a - "b /#c'],
                'expected' => ['"a - ""b /#c"'],
            ],
            'multiple enclose rules test with multi-line' => [
                'data' => ['a - "' . "\n" . 'b /#c'],
                'expected' => ['"a - """/"b /#c"'],
            ],
        ];
    }
}
