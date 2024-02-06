<?php

namespace HappyHippyHippo\WSV;

class Metadata implements MetadataInterface
{
    /**
     * @var array<int|string, MetadataFieldInterface>
     */
    protected array $fields = [];

    /** @var bool */
    protected bool $header = false;

    /**
     * @param bool|null $header
     * @return bool|MetadataInterface
     */
    public function header(?bool $header = null): bool|MetadataInterface
    {
        if (!is_null($header)) {
            $this->header = $header;
            return $this;
        }
        return $this->header;
    }

    /**
     * @param int|string $name
     * @return MetadataFieldInterface
     */
    public function field(int|string $name): MetadataFieldInterface
    {
        if (!array_key_exists($name, $this->fields)) {
            $this->fields[$name] = new MetadataField();
        }
        return $this->fields[$name];
    }
}
