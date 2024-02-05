<?php

namespace HappyHippyHippo\WSV\tests;

use HappyHippyHippo\TextIO\Exception\Exception;
use HappyHippyHippo\WSV\Decoder;
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
     * @throws Exception
     *
     * @covers ::__construct
     * @covers ::make
     * @covers ::records
     */
    public function testEmptySource(): void
    {
        $path = 'data.txt';
        $this->root->addChild(new vfsStreamFile($path, 0777));

        $decoder = Decoder::make($this->root->url() . '/' . $path);
        $this->assertNotNull($decoder);
        foreach ($decoder->records() as $ignored) {
            $this->fail('unexpected record found');
        }
    }


    /**
     * @throws Exception
     *
     * @covers ::__construct
     * @covers ::make
     * @covers ::records
     * @covers ::applyKeys
     * @covers ::applyTransformers
     */
    public function testSingleLineDecode(): void
    {
        $path = 'data.txt';
        $content = '"John Doe" 32 British';
        $expected = [['John Doe', '32', 'British']];
        $file = new vfsStreamFile($path, 0777);
        $file->setContent($content);
        $this->root->addChild($file);

        $decoder = Decoder::make($this->root->url() . '/' . $path);
        $records = [];
        foreach ($decoder->records() as $record) {
            $records[] = $record;
        }
        $this->assertCount(count($expected), $records);
        $this->assertEquals($expected, $records);
    }

    /**
     * @throws Exception
     *
     * @covers ::__construct
     * @covers ::make
     * @covers ::records
     * @covers ::applyKeys
     * @covers ::applyTransformers
     */
    public function testMultipleLineDecode(): void
    {
        $path = 'data.txt';
        $content = '"John Doe" 32 British' . "\n" . '"Jane She" 25 Italian';
        $expected = [
            ['John Doe', '32', 'British'],
            ['Jane She', '25', 'Italian'],
        ];
        $file = new vfsStreamFile($path, 0777);
        $file->setContent($content);
        $this->root->addChild($file);

        $decoder = Decoder::make($this->root->url() . '/' . $path);
        $records = [];
        foreach ($decoder->records() as $record) {
            $records[] = $record;
        }
        $this->assertCount(count($expected), $records);
        $this->assertEquals($expected, $records);
    }

    /**
     * @throws Exception
     *
     * @covers ::__construct
     * @covers ::make
     * @covers ::header
     * @covers ::records
     * @covers ::applyKeys
     * @covers ::applyTransformers
     */
    public function testHeaderArray(): void
    {
        $path = 'data.txt';
        $header = ['name', 'age', 'nationality'];
        $content = '"John Doe" 32 British' . "\n" . '"Jane She" 25 Italian';
        $expected = [
            ['name' => 'John Doe', 'age' => '32', 'nationality' => 'British'],
            ['name' => 'Jane She', 'age' => '25', 'nationality' => 'Italian'],
        ];
        $file = new vfsStreamFile($path, 0777);
        $file->setContent($content);
        $this->root->addChild($file);

        $decoder = Decoder::make($this->root->url() . '/' . $path)
            ->header($header);
        $records = [];
        foreach ($decoder->records() as $record) {
            $records[] = $record;
        }
        $this->assertCount(count($expected), $records);
        $this->assertEquals($expected, $records);
    }

    /**
     * @throws Exception
     *
     * @covers ::__construct
     * @covers ::make
     * @covers ::header
     * @covers ::records
     * @covers ::applyKeys
     * @covers ::applyTransformers
     */
    public function testHeaderRecord(): void
    {
        $path = 'data.txt';
        $content = 'name age nationality' . "\n" . '"John Doe" 32 British' . "\n" . '"Jane She" 25 Italian';
        $expected = [
            ['name' => 'John Doe', 'age' => '32', 'nationality' => 'British'],
            ['name' => 'Jane She', 'age' => '25', 'nationality' => 'Italian'],
        ];
        $file = new vfsStreamFile($path, 0777);
        $file->setContent($content);
        $this->root->addChild($file);

        $decoder = Decoder::make($this->root->url() . '/' . $path)
            ->header(true);
        $records = [];
        foreach ($decoder->records() as $record) {
            $records[] = $record;
        }
        $this->assertCount(count($expected), $records);
        $this->assertEquals($expected, $records);
    }

    /**
     * @throws Exception
     *
     * @covers ::__construct
     * @covers ::make
     * @covers ::header
     * @covers ::transformer
     * @covers ::records
     * @covers ::applyKeys
     * @covers ::applyTransformers
     */
    public function testTransformer(): void
    {
        $path = 'data.txt';
        $content = 'name age nationality' . "\n" . '"John Doe" 32 British' . "\n" . '"Jane She" 25 Italian';
        $expected = [
            ['name' => 'John Doe', 'age' => 64, 'nationality' => 'British'],
            ['name' => 'Jane She', 'age' => 50, 'nationality' => 'Italian'],
        ];
        $file = new vfsStreamFile($path, 0777);
        $file->setContent($content);
        $this->root->addChild($file);

        $decoder = Decoder::make($this->root->url() . '/' . $path)
            ->header(true)
            ->transformer('age', fn (string $age) => ((int) $age) * 2);
        $records = [];
        foreach ($decoder->records() as $record) {
            $records[] = $record;
        }
        $this->assertCount(count($expected), $records);
        $this->assertEquals($expected, $records);
    }
}
