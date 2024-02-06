<?php

namespace HappyHippyHippo\WSV\tests;

use HappyHippyHippo\TextIO\Encode;
use HappyHippyHippo\TextIO\Exception\FileNotWritableException;
use HappyHippyHippo\TextIO\Exception\FileOpenException;
use HappyHippyHippo\TextIO\Exception\FileWriteException;
use HappyHippyHippo\WSV\Metadata;
use HappyHippyHippo\WSV\OutputStream;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \HappyHippyHippo\WSV\OutputStream
 */
class OutputStreamTest extends TestCase
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
     * @throws FileNotWritableException
     * @throws FileOpenException
     * @throws FileWriteException
     */
    public function testCreateStream(): void
    {
        $path = 'data.txt';
        $file = new vfsStreamFile($path, 0777);
        $this->root->addChild($file);
        $stream = OutputStream::make($this->root->url() . '/' . $path);
        $this->assertNotNull($stream);
    }

    /**
     * @param array<int|string, mixed> $record
     * @param string $expected
     * @return void
     * @throws FileNotWritableException
     * @throws FileOpenException
     * @throws FileWriteException
     *
     * @covers ::__construct
     * @covers ::make
     * @covers ::write
     * @dataProvider provideDataToWriteTest
     */
    public function testWrite(array $record, string $expected): void
    {
        $path = 'data.txt';
        $file = new vfsStreamFile($path, 0777);
        $this->root->addChild($file);

        $stream = OutputStream::make($this->root->url() . '/' . $path);
        $stream->write($record);
        $this->assertEquals($expected, $file->getContent());
    }

    /**
     * @return array<string, mixed>
     */
    public static function provideDataToWriteTest(): array
    {
        $bom = Encode::UTF8->bom();
        return [
            'empty records' => [
                'record' => [],
                'expected' => $bom,
            ],
            'single value' => [
                'record' => [123],
                'expected' => $bom . '123' . "\n",
            ],
            'multiple values - test 1' => [
                'record' => [123, null, 'abc'],
                'expected' => $bom . '123 - abc' . "\n",
            ],
            'multiple values - test 2' => [
                'record' => [null, 456.789],
                'expected' => $bom . '- 456.789' . "\n",
            ],
            'multiple values - test 3' => [
                'record' => ['abc' . "\n" . 'def', 123],
                'expected' => $bom . '"abc"/"def" 123' . "\n",
            ],
        ];
    }

    /**
     * @return void
     * @throws FileNotWritableException
     * @throws FileOpenException
     * @throws FileWriteException
     *
     * @covers ::__construct
     * @covers ::make
     * @covers ::write
     */
    public function testWriteWithTransforms(): void
    {
        $record = ['abc' . "\n" . 'def', 'extra' => 123, null];
        $expected = Encode::UTF8->bom() . '"abc"/"def" 123~data -' . "\n";

        $path = 'data.txt';
        $file = new vfsStreamFile($path, 0777);
        $this->root->addChild($file);

        $metadata = new Metadata();
        $metadata->field('extra')->transformer(fn ($value): string => $value . '~data');
        $stream = OutputStream::make($this->root->url() . '/' . $path, $metadata);
        $stream->write($record);
        $this->assertEquals($expected, $file->getContent());
    }

    /**
     * @param array<int|string, mixed> $record
     * @param string $expected
     * @return void
     * @throws FileNotWritableException
     * @throws FileOpenException
     * @throws FileWriteException
     *
     * @covers ::__construct
     * @covers ::make
     * @covers ::write
     * @dataProvider provideDataToWriteWithExtraPaddingTest
     */
    public function testWriteWithExtraPadding(array $record, string $expected): void
    {
        $path = 'data.txt';
        $file = new vfsStreamFile($path, 0777);
        $this->root->addChild($file);

        $metadata = new Metadata();
        $metadata->field(1)->length(10);
        $metadata->field('extra')->length(6);
        $stream = OutputStream::make($this->root->url() . '/' . $path, $metadata);
        $stream->write($record);
        $this->assertEquals($expected, $file->getContent());
    }

    /**
     * @return array<string, mixed>
     */
    public static function provideDataToWriteWithExtraPaddingTest(): array
    {
        $bom = Encode::UTF8->bom();
        return [
            'empty records' => [
                'record' => [],
                'expected' => $bom,
            ],
            'single value' => [
                'record' => [123],
                'expected' => $bom . '123' . "\n",
            ],
            'multiple values - test 1' => [
                'record' => [123, null, 'abc'],
                'expected' => $bom . '123 -         abc' . "\n",
            ],
            'multiple values - test 2' => [
                'record' => [null, 456.789, 'abc'],
                'expected' => $bom . '- 456.789   abc' . "\n",
            ],
            'multiple values - test 3' => [
                'record' => ['abc' . "\n" . 'def', 123],
                'expected' => $bom . '"abc"/"def" 123' . "\n",
            ],
            'multiple values - test 4' => [
                'record' => ['abc' . "\n" . 'def', 'extra' => 123, null],
                'expected' => $bom . '"abc"/"def" 123   -' . "\n",
            ],
        ];
    }
}
