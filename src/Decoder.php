<?php

namespace HappyHippyHippo\WSV;

use Closure;
use Generator;
use HappyHippyHippo\TextIO\Encode;
use HappyHippyHippo\TextIO\Exception\Exception;
use HappyHippyHippo\TextIO\InputStream;

class Decoder
{
    /** @var null|bool|string[] */
    protected null|bool|array $header = null;

    /** @var array<string, Closure> */
    protected array $transformers = [];

    /**
     * @param string $file
     * @param Encode $encode
     */
    public function __construct(protected string $file, protected Encode $encode = Encode::UTF8)
    {
    }

    /**
     * @param string $file
     * @param Encode $encode
     * @return Decoder
     */
    public static function make(string $file, Encode $encode = Encode::UTF8): Decoder
    {
        return new self($file, $encode);
    }

    /**
     * @param null|bool|string[] $header
     * @return $this
     */
    public function header(null|bool|array $header): Decoder
    {
        $this->header = $header;
        return $this;
    }

    /**
     * @param string $key
     * @param Closure $transformer
     * @return $this
     */
    public function transformer(string $key, Closure $transformer): Decoder
    {
        $this->transformers[$key] = $transformer;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function records(): Generator
    {
        $input = new InputStream($this->file);
        $parser = new Parser();
        foreach ($input->lines($this->encode) as $id => $line) {
            $values = $parser->parse($line, $this->encode);
            if ($this->header === true && $id === 0) {
                $this->header = $values;
                continue;
            }
            yield $this->applyTransformers($this->applyKeys($values));
        }
    }

    /**
     * @param array<int|string, mixed> $values
     * @return array<int|string, mixed>
     */
    protected function applyKeys(array $values): array
    {
        if (is_array($this->header)) {
            $values = array_combine($this->header, $values);
        }
        return $values;
    }

    /**
     * @param array<int|string, mixed> $values
     * @return array<int|string, mixed>
     */
    protected function applyTransformers(array $values): array
    {
        foreach ($this->transformers as $key => $transformer) {
            if (array_key_exists($key, $values)) {
                $values[$key] = $transformer($values[$key]);
            }
        }
        return $values;
    }
}
