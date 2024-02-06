<?php

namespace HappyHippyHippo\WSV;

interface MetadataInterface
{
    /**
     * @param bool|null $header
     * @return bool|MetadataInterface
     */
    public function header(?bool $header = null): bool|MetadataInterface;

    /**
     * @param int|string $name
     * @return MetadataFieldInterface
     */
    public function field(int|string $name): MetadataFieldInterface;
}
