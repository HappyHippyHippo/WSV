<?php

namespace HappyHippyHippo\WSV;

use IntlChar;

class Char
{
    /**
     * @param int $value
     * @return bool
     */
    public static function isEOL(int $value): bool
    {
        return $value === self::EOL();
    }

    /**
     * @return int
     */
    public static function EOL(): int
    {
        return IntlChar::ord("\n");
    }

    /**
     * @param int $value
     * @return bool
     */
    public static function isWhitespace(int $value): bool
    {
        return match ($value) {
            0x09,0x0A,0x0B,0x0C,0x0D,0x20,0x85,0xA0,
            0x1680,0x2000,0x2001,0x2002,
            0x2003,0x2004,0x2005,0x2006,
            0x2007,0x2008,0x2009,0x200A,
            0x2028,0x2029,0x202F,0x205F,0x3000 => true,
            default => false,
        };
    }

    /**
     * @return int
     */
    public static function whitespace(): int
    {
        return IntlChar::ord(' ');
    }

    /**
     * @param int $value
     * @return bool
     */
    public static function isComment(int $value): bool
    {
        return $value === self::comment();
    }

    /**
     * @return int
     */
    public static function comment(): int
    {
        return IntlChar::ord('#');
    }

    /**
     * @param int $value
     * @return bool
     */
    public static function isSlash(int $value): bool
    {
        return $value === self::slash();
    }

    /**
     * @return int
     */
    public static function slash(): int
    {
        return IntlChar::ord('/');
    }

    /**
     * @param int $value
     * @return bool
     */
    public static function isNull(int $value): bool
    {
        return $value === self::null();
    }

    /**
     * @return int
     */
    public static function null(): int
    {
        return IntlChar::ord('-');
    }

    /**
     * @param int $value
     * @return bool
     */
    public static function isDoubleQuote(int $value): bool
    {
        return $value === self::doubleQuote();
    }

    /**
     * @return int
     */
    public static function doubleQuote(): int
    {
        return IntlChar::ord('"');
    }
}
