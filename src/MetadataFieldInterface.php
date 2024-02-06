<?php

namespace HappyHippyHippo\WSV;

use Closure;

interface MetadataFieldInterface
{
    /**
     * @param null|int $length
     * @return int|MetadataFieldInterface
     */
    public function length(null|int $length = null): int|MetadataFieldInterface;

    /**
     * @param Closure $transformer
     * @return $this
     */
    public function transformer(Closure $transformer): MetadataFieldInterface;

    /**
     * @param mixed $value
     * @return mixed
     */
    public function transform(mixed $value): mixed;
}
