<?php

namespace HappyHippyHippo\WSV;

use Closure;

class MetadataField implements MetadataFieldInterface
{
    /** @var Closure[] */
    protected array $transformers = [];

    /** @var int */
    protected int $length = 0;

    /**
     * @param int|null $length
     * @return int|MetadataFieldInterface
     */
    public function length(null|int $length = null): int|MetadataFieldInterface
    {
        if (is_null($length)) {
            return $this->length;
        }
        $this->length = $length;
        return $this;
    }

    /**
     * @param Closure $transformer
     * @return $this
     */
    public function transformer(Closure $transformer): MetadataFieldInterface
    {
        $this->transformers[] = $transformer;
        return $this;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function transform(mixed $value): mixed
    {
        foreach ($this->transformers as $transformer) {
            $value = $transformer($value);
        }
        return $value;
    }
}
