<?php

namespace HappyHippyHippo\WSV\tests;

use HappyHippyHippo\TextIO\Encode;
use HappyHippyHippo\TextIO\Exception\FileNotWritableException;
use HappyHippyHippo\TextIO\Exception\FileOpenException;
use HappyHippyHippo\TextIO\Exception\FileWriteException;
use HappyHippyHippo\WSV\Encoder;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;

class EncoderTest extends TestCase
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
     * @param array<int, string[]> $content
     * @param string $expected
     * @throws FileNotWritableException
     * @throws FileOpenException
     * @throws FileWriteException
     *
     * @covers ::__construct
     * @covers ::make
     * @covers ::metadata
     * @covers ::decode
     * @dataProvider provideDataToDecodeTest
     */
    public function testEncode(array $content, string $expected): void
    {
        $path = 'data.txt';
        $file = new vfsStreamFile($path, 0777);
        $this->root->addChild($file);

        $encoder = Encoder::make($this->root->url() . '/' . $path);
        $encoder->metadata()->field('age')->transformer(fn (string $age): int => ((int) $age) * 2);
        $encoder->metadata()->field('nat')->length(10);
        foreach ($content as $record) {
            $encoder->write($record);
        }

        $this->assertEquals($expected, $file->getContent());
    }

    /**
     * @return array<string, mixed>
     */
    public static function provideDataToDecodeTest(): array
    {
        $bom = Encode::UTF8->bom();
        return [
            'single record' => [
                'content' => [['John Doe', 64, 'nat' => 'British', '123-456']],
                'expected' => $bom . '"John Doe" 64 British   "123-456"' . "\n",
            ],
            'multiple records' => [
                'content' => [
                    ['name', 'age', 'nat'],
                    ['name' => 'John Doe', 'age' => 32, 'nat' => 'British'],
                    ['name' => 'Jane She', 'age' => 25, 'nat' => 'Italian'],
                ],
                'expected' => $bom .
                    'name age nat' . "\n" .
                    '"John Doe" 64 British' . "\n" .
                    '"Jane She" 50 Italian' . "\n",
            ],
            'jagged records' => [
                'content' => [
                    ['name', 'age', 'nat'],
                    ['name' => 'John Doe', 'age' => 32, 'nat' => 'British', 'vegetable' => 'carrots'],
                    ['name' => 'Jane She', 'age' => 25],
                ],
                'expected' => $bom .
                    'name age nat' . "\n" .
                    '"John Doe" 64 British   carrots' . "\n" .
                    '"Jane She" 50' . "\n",
            ],
        ];
    }
}
