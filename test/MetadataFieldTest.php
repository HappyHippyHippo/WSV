<?php

namespace HappyHippyHippo\WSV\tests;

use HappyHippyHippo\WSV\MetadataField;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \HappyHippyHippo\WSV\MetadataField
 */
class MetadataFieldTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::getLength
     * @covers ::setLength
     */
    public function testLength(): void
    {
        $field = new MetadataField();
        $this->assertEquals(0, $field->length());
        $this->assertSame($field, $field->length(100));
        $this->assertEquals(100, $field->length());
    }

    /**
     * @return void
     *
     * @covers ::transformer
     * @covers ::transform
     */
    public function testTransform(): void
    {
        $field = new MetadataField();
        $this->assertEquals(100, $field->transform(100));
        $field->transformer(fn (int $value): int => $value * 100);
        $this->assertEquals(10000, $field->transform(100));
    }
}
