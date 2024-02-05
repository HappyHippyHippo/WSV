<?php

namespace HappyHippyHippo\WSV\tests;

use HappyHippyHippo\WSV\Exception;
use HappyHippyHippo\WSV\Parser;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \HappyHippyHippo\WSV\Parser
 */
class ParserTest extends TestCase
{
    /**
     * @param string $text
     * @param string[] $expected
     * @return void
     * @throws Exception\Exception
     *
     * @covers ::parse
     * @covers ::removeWhitespaces
     * @covers ::removeComment
     * @covers ::read
     * @covers ::readNull
     * @covers ::readValue
     * @covers ::readString
     * @covers ::isEOL
     * @covers ::isWhitespace
     * @covers ::isComment
     * @covers ::isSlash
     * @covers ::isDoubleQuote
     * @dataProvider provideDataToParseTest
     */
    public function testParse(string $text, array $expected): void
    {
        $this->assertEquals($expected, (new Parser())->parse($text));
    }

    /**
     * @return array<string, mixed>
     */
    public static function provideDataToParseTest(): array
    {
        return [
            'empty string' => [
                'text' => '',
                'expected' => [],
            ],
            'single null value' => [
                'text' => '-',
                'expected' => [null],
                ],
            'multiple null value' => [
                'text' => '- - -',
                'expected' => [null, null, null],
                ],
            'multiple null value with variable ws' => [
                'text' => '   -    -    -     ',
                'expected' => [null, null, null],
                ],
            'single value' => [
                'text' => '123',
                'expected' => ['123'],
                ],
            'multiple value' => [
                'text' => '123 abc %\&/',
                'expected' => ['123', 'abc', '%\&/'],
                ],
            'multiple value with variable ws' => [
                'text' => '    123    abc     %\&/   ',
                'expected' => ['123', 'abc', '%\&/'],
                ],
            'comment' => [
                'text' => '# comment',
                'expected' => [],
                ],
            'line comment non starting with comment char' => [
                'text' => '   # comment',
                'expected' => [],
                ],
            'extra comment line' => [
                'text' => '123 # comment',
                'expected' => ['123'],
                ],
            'single string' => [
                'text' => '"123"',
                'expected' => ['123'],
                ],
            'single string with extra wc' => [
                'text' => '  "1 2 3"   ',
                'expected' => ['1 2 3'],
                ],
            'multiple strings' => [
                'text' => '"123" 456 "789"',
                'expected' => ['123', '456', '789'],
                ],
            'escaped null' => [
                'text' => '"-"',
                'expected' => ['-'],
                ],
            'single escaped double-quote' => [
                'text' => '""""',
                'expected' => ['"'],
                ],
            'multiple escaped double-quote' => [
                'text' => '"a""b""c"',
                'expected' => ['a"b"c'],
                ],
            'multiple escaped double-quote values' => [
                'text' => '  "a""b""c"   123   "d""ef"',
                'expected' => ['a"b"c', '123', 'd"ef'],
                ],
            'multi-line string' => [
                'text' => '"123"/"456"',
                'expected' => ["123\n456"],
                ],
            'multi-line string with extra wc' => [
                'text' => '" 123  "/" 456  " abc " abc  "/" def  "',
                'expected' => [" 123  \n 456  ", 'abc', " abc  \n def  "],
                ],
        ];
    }

    /**
     * @return void
     * @throws Exception\Exception
     *
     * @covers ::parse
     * @covers ::read
     * @covers ::readString
     */
    public function testParseUnterminatedString(): void
    {
        $this->expectException(Exception\EndOfLineException::class);
        $this->expectExceptionMessage('Unexpected end of line');

        (new Parser())->parse('asd "asd 123');
    }
}
