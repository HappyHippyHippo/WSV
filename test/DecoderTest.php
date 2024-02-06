<?php

namespace HappyHippyHippo\WSV\tests;

use HappyHippyHippo\TextIO\Exception\FileNotFoundException;
use HappyHippyHippo\TextIO\Exception\FileNotReadableException;
use HappyHippyHippo\TextIO\Exception\FileOpenException;
use HappyHippyHippo\TextIO\Exception\FileReadException;
use HappyHippyHippo\WSV\Decoder;
use HappyHippyHippo\WSV\Exception\EndOfLineException;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \HappyHippyHippo\WSV\Decoder
 */
class DecoderTest extends TestCase
{
    /** @var vfsStreamDirectory */
    private vfsStreamDirectory $root;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    /**
     * @param string $content
     * @param array<int, string[]> $expected
     * @throws FileNotFoundException
     * @throws FileNotReadableException
     * @throws FileOpenException
     * @throws FileReadException
     * @throws EndOfLineException
     *
     * @covers ::__construct
     * @covers ::make
     * @covers ::metadata
     * @covers ::decode
     * @dataProvider provideDataToDecodeTest
     */
    public function testDecode(string $content, array $expected): void
    {
        $path = 'data.txt';
        $file = new vfsStreamFile($path, 0777);
        $file->setContent($content);
        $this->root->addChild($file);

        $decoder = Decoder::make($this->root->url() . '/' . $path);
        $decoder->metadata()->header(true);
        $decoder->metadata()->field('age')->transformer(fn (string $age): int => ((int) $age) * 2);

        $records = [];
        foreach ($decoder->read() as $record) {
            $records[] = $record;
        }
        $this->assertCount(count($expected), $records);
        $this->assertEquals($expected, $records);
    }

    /**
     * @return array<string, mixed>
     */
    public static function provideDataToDecodeTest(): array
    {
        return [
            'single line' => [
                'content' => 'name age nat' . "\n" . '"John Doe" 32 British',
                'expected' => [['name' => 'John Doe', 'age' => 64, 'nat' => 'British']],
            ],
            'multiple lines' => [
                'content' => 'name age nat' . "\n" . '"John Doe" 32 British' . "\n" . '"Jane She" 25 Italian',
                'expected' => [
                    ['name' => 'John Doe', 'age' => 64, 'nat' => 'British'],
                    ['name' => 'Jane She', 'age' => 50, 'nat' => 'Italian'],
                ],
            ],
            'multiple lines with empty lines' => [
                'content' => 'name age nat' . "\n" .
                    '"John Doe" 32 British' . "\n\n\n" .
                    '"Jane She" 25 Italian',
                'expected' => [
                    ['name' => 'John Doe', 'age' => 64, 'nat' => 'British'],
                    ['name' => 'Jane She', 'age' => 50, 'nat' => 'Italian'],
                ],
            ],
        ];
    }

    /**
     * @throws FileNotFoundException
     * @throws FileNotReadableException
     * @throws FileOpenException
     * @throws FileReadException
     * @throws EndOfLineException
     *
     * @covers ::__construct
     * @covers ::make
     * @covers ::decode
     */
    public function testNonTabularDecode(): void
    {
        $content = 'name age nat' . "\n" .
            '"John Doe" 32 British feet' . "\n\n\n" .
            '"Jane She" string';
        $expected = [
            ['name', 'age', 'nat'],
            ['John Doe', '32', 'British', 'feet'],
            ['Jane She', 'string'],
        ];

        $path = 'data.txt';
        $file = new vfsStreamFile($path, 0777);
        $file->setContent($content);
        $this->root->addChild($file);

        $decoder = Decoder::make($this->root->url() . '/' . $path);
        $records = [];
        foreach ($decoder->read() as $record) {
            $records[] = $record;
        }
        $this->assertCount(count($expected), $records);
        $this->assertEquals($expected, $records);
    }
}
