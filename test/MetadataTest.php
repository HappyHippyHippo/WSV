<?php

namespace HappyHippyHippo\WSV\tests;

use HappyHippyHippo\WSV\Metadata;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \HappyHippyHippo\WSV\Metadata
 */
class MetadataTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::header
     */
    public function testHeader(): void
    {
        $metadata = new Metadata();

        $this->assertFalse($metadata->header());
        $this->assertFalse($metadata->header());
        $this->assertSame($metadata, $metadata->header(true));
        $this->assertTrue($metadata->header());
        $this->assertTrue($metadata->header());
        $this->assertSame($metadata, $metadata->header(false));
        $this->assertFalse($metadata->header());
        $this->assertFalse($metadata->header());
    }

    /**
     * @return void
     *
     * @covers ::field
     * @covers ::getField
     */
    public function testField(): void
    {
        $metadata = new Metadata();

        $this->assertNotNull($metadata->field('field_name_!'));
        $this->assertNotNull($metadata->field('field_name_!'));
        $this->assertNotNull($metadata->field('field_name_2'));
    }
}
