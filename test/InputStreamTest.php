<?php

namespace HappyHippyHippo\WSV\tests;

use HappyHippyHippo\TextIO\Exception\FileNotFoundException;
use HappyHippyHippo\TextIO\Exception\FileNotReadableException;
use HappyHippyHippo\TextIO\Exception\FileOpenException;
use HappyHippyHippo\TextIO\Exception\FileReadException;
use HappyHippyHippo\WSV\Exception\EndOfLineException;
use HappyHippyHippo\WSV\InputStream;
use HappyHippyHippo\WSV\Metadata;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \HappyHippyHippo\WSV\InputStream
 */
class InputStreamTest extends TestCase
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
     * @return void
     * @throws EndOfLineException
     * @throws FileNotFoundException
     * @throws FileNotReadableException
     * @throws FileOpenException
     * @throws FileReadException
     */
    public function testEmptySource(): void
    {
        $path = 'data.txt';
        $this->root->addChild(new vfsStreamFile($path, 0777));

        $decoder = InputStream::make($this->root->url() . '/' . $path);
        $this->assertNotNull($decoder);
        foreach ($decoder->read() as $ignored) {
            $this->fail('unexpected record found');
        }
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
     * @covers ::read
     * @dataProvider provideDataToReadTest
     */
    public function testRead(string $content, array $expected): void
    {
        $path = 'data.txt';
        $file = new vfsStreamFile($path, 0777);
        $file->setContent($content);
        $this->root->addChild($file);

        $decoder = InputStream::make($this->root->url() . '/' . $path);
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
    public static function provideDataToReadTest(): array
    {
        return [
            'single line' => [
                'content' => '"John Doe" 32 British',
                'expected' => [['John Doe', '32', 'British']],
            ],
            'multiple lines' => [
                'content' => '"John Doe" 32 British' . "\n" . '"Jane She" 25 Italian',
                'expected' => [
                    ['John Doe', '32', 'British'],
                    ['Jane She', '25', 'Italian'],
                ],
            ],
            'multiple lines with empty lines' => [
                'content' => '"John Doe" 32 British' . "\n\n\n" . '"Jane She" 25 Italian',
                'expected' => [
                    ['John Doe', '32', 'British'],
                    ['Jane She', '25', 'Italian'],
                ],
            ],
        ];
    }

    /**
     * @param string $content
     * @param array<int, string[]> $expected
     * @throws EndOfLineException
     * @throws FileNotFoundException
     * @throws FileNotReadableException
     * @throws FileOpenException
     * @throws FileReadException
     *
     * @covers ::__construct
     * @covers ::make
     * @covers ::read
     * @dataProvider provideDataToReadWithHeaderTest
     */
    public function testReadWithHeader(string $content, array $expected): void
    {
        $path = 'data.txt';
        $file = new vfsStreamFile($path, 0777);
        $file->setContent($content);
        $this->root->addChild($file);

        $metadata = new Metadata();
        $metadata->header(true);
        $decoder = InputStream::make($this->root->url() . '/' . $path, $metadata);
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
    public static function provideDataToReadWithHeaderTest(): array
    {
        return [
            'single line' => [
                'content' => 'name age nat' . "\n" . '"John Doe" 32 British',
                'expected' => [['name' => 'John Doe', 'age' => '32', 'nat' => 'British']],
            ],
            'multiple lines' => [
                'content' => 'name age nat' . "\n" . '"John Doe" 32 British' . "\n" . '"Jane She" 25 Italian',
                'expected' => [
                    ['name' => 'John Doe', 'age' => '32', 'nat' => 'British'],
                    ['name' => 'Jane She', 'age' => '25', 'nat' => 'Italian'],
                ],
            ],
            'multiple lines with empty lines' => [
                'content' => 'name age nat' . "\n" . '"John Doe" 32 British' . "\n\n\n" . '"Jane She" 25 Italian',
                'expected' => [
                    ['name' => 'John Doe', 'age' => '32', 'nat' => 'British'],
                    ['name' => 'Jane She', 'age' => '25', 'nat' => 'Italian'],
                ],
            ],
        ];
    }

    /**
     * @param string $content
     * @param array<int, string[]> $expected
     * @throws EndOfLineException
     * @throws FileNotFoundException
     * @throws FileNotReadableException
     * @throws FileOpenException
     * @throws FileReadException
     *
     * @covers ::__construct
     * @covers ::make
     * @covers ::read
     * @dataProvider provideDataToReadWithHeaderAndTransformTest
     */
    public function testReadWithHeaderAndTransform(string $content, array $expected): void
    {
        $path = 'data.txt';
        $file = new vfsStreamFile($path, 0777);
        $file->setContent($content);
        $this->root->addChild($file);

        $metadata = new Metadata();
        $metadata->header(true);
        $metadata->field('age')->transformer(fn (string $age): int => ((int) $age) * 2);
        $decoder = InputStream::make($this->root->url() . '/' . $path, $metadata);
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
    public static function provideDataToReadWithHeaderAndTransformTest(): array
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
                'content' => 'name age nat' . "\n" . '"John Doe" 32 British' . "\n\n\n" . '"Jane She" 25 Italian',
                'expected' => [
                    ['name' => 'John Doe', 'age' => 64, 'nat' => 'British'],
                    ['name' => 'Jane She', 'age' => 50, 'nat' => 'Italian'],
                ],
            ],
        ];
    }
}
