<?php

namespace Zhylon\Z3Filesystem;

use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;

class Z3Adapter implements FilesystemAdapter
{
    public function __construct(
        private Z3Client $client
    ) {
    }

    public function fileExists(string $path): bool
    {
        dd('fileExists');
    }

    public function directoryExists(string $path): bool
    {
        dd('directoryExists');
    }

    public function write(string $path, string $contents, Config $config): void
    {
        $this->client->write($path, $contents);
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        dd('writeStream');
    }

    public function read(string $path): string
    {
        return $this->client->read($path);
    }

    public function readStream(string $path)
    {
        throw new \Exception('Cannot read stream');
    }

    public function delete(string $path): void
    {
        dd('delete');
    }

    public function deleteDirectory(string $path): void
    {
        dd('deleteDirectory');
    }

    public function createDirectory(string $path, Config $config): void
    {
        dd('createDirectory');
    }

    public function setVisibility(string $path, string $visibility): void
    {
        dd('setVisibility');
    }

    public function visibility(string $path): FileAttributes
    {
        dd('visibility');
    }

    public function mimeType(string $path): FileAttributes
    {
        return $this->client->getFileAttributes($path);
    }

    public function lastModified(string $path): FileAttributes
    {
        return $this->client->getFileAttributes($path);
    }

    public function fileSize(string $path): FileAttributes
    {
        return $this->client->getFileAttributes($path);
    }

    public function listContents(string $path, bool $deep): iterable
    {
        return $this->client->listObjectsV2($path, $deep);
    }

    public function move(string $source, string $destination, Config $config): void
    {
        dd('move');
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        dd('copy');
    }
}

function dd($method)
{
    throw new \Exception("Method $method not implemented");
}
