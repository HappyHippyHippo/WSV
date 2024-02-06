<?php

namespace HappyHippyHippo\WSV\tests;

use HappyHippyHippo\WSV\Char;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \HappyHippyHippo\WSV\Char
 */
class CharTest extends TestCase
{
    /**
     * @return void
     *
     * @covers ::isEOL
     */
    public function testIsEOL(): void
    {
        $this->assertTrue(Char::isEOL(0x0A));
    }

    /**
     * @return void
     *
     * @covers ::EOL
     */
    public function testEOL(): void
    {
        $this->assertEquals(0x0A, Char::EOL());
    }

    /**
     * @return void
     *
     * @covers ::isWhitespace
     */
    public function testIsWhitespace(): void
    {
        $this->assertTrue(Char::isWhitespace(0x20));
    }

    /**
     * @return void
     *
     * @covers ::whitespace
     */
    public function testWhitespace(): void
    {
        $this->assertEquals(0x20, Char::whitespace());
    }

    /**
     * @return void
     *
     * @covers ::isComment
     */
    public function testIsComment(): void
    {
        $this->assertTrue(Char::isComment(0x23));
    }

    /**
     * @return void
     *
     * @covers ::comment
     */
    public function testComment(): void
    {
        $this->assertEquals(0x23, Char::comment());
    }

    /**
     * @return void
     *
     * @covers ::isSlash
     */
    public function testIsSlash(): void
    {
        $this->assertTrue(Char::isSlash(0x2f));
    }

    /**
     * @return void
     *
     * @covers ::slash
     */
    public function testSlash(): void
    {
        $this->assertEquals(0x2f, Char::slash());
    }

    /**
     * @return void
     *
     * @covers ::isNull
     */
    public function testIsNull(): void
    {
        $this->assertTrue(Char::isNull(0x2d));
    }

    /**
     * @return void
     *
     * @covers ::null
     */
    public function testNull(): void
    {
        $this->assertEquals(0x2d, Char::null());
    }

    /**
     * @return void
     *
     * @covers ::isDoubleQuote
     */
    public function testIsDoubleQuote(): void
    {
        $this->assertTrue(Char::isDoubleQuote(0x22));
    }

    /**
     * @return void
     *
     * @covers ::doubleQuote
     */
    public function testDoubleQuote(): void
    {
        $this->assertEquals(0x22, Char::doubleQuote());
    }
}
