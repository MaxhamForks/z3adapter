<?php

namespace Zhylon\Z3Filesystem;

use League\Flysystem\StorageAttributes;

class Z3Attribute implements StorageAttributes
{
    public function __construct(private Z3Item $item)
    {
    }

    public function offsetExists(mixed $offset): bool
    {
        dd('offsetExists');
    }

    public function offsetGet(mixed $offset): mixed
    {
        dd('offsetGet');
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        dd('offsetSet');
    }

    public function offsetUnset(mixed $offset): void
    {
        dd('offsetUnset');
    }

    public function path(): string
    {
        return $this->item->path();
    }

    public function type(): string
    {
        dd('type');
    }

    public function visibility(): ?string
    {
        dd('visibility');
    }

    public function lastModified(): ?int
    {
        dd('lastModified');
    }

    public static function fromArray(array $attributes): StorageAttributes
    {
        dd('fromArray');
    }

    public function isFile(): bool
    {
        return $this->item->isFile();
    }

    public function isDir(): bool
    {
        return $this->item->isDir();
    }

    public function withPath(string $path): StorageAttributes
    {
        dd('withPath');
    }

    public function extraMetadata(): array
    {
        dd('extraMetadata');
    }

    public function jsonSerialize(): mixed
    {
        dd('jsonSerialize');
    }
}
