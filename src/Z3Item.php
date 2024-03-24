<?php

namespace Zhylon\Z3Filesystem;

class Z3Item
{
    public function __construct(private array $attributes)
    {
    }

    public function isFile(): bool
    {
        return $this->attributes['type'] === 'file';
    }

    public function path()
    {
        return $this->attributes['path'];
    }

    public function isDir(): bool
    {
        return $this->attributes['type'] === 'directory';
    }
}
