<?php

namespace Zhylon\Z3Filesystem;

use GuzzleHttp\Psr7\Stream;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use League\Flysystem\FileAttributes;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Http;

class Z3Client
{
    private Encrypter $encrypter;

    public function __construct(
        private readonly array $config
    ) {
        throw_if(!isset($this->config['key']), new Z3Exception('Z3 key is required'));
        throw_if(!isset($this->config['secret']), new Z3Exception('Z3 secret is required'));
        throw_if(!isset($this->config['bucket']), new Z3Exception('Z3 bucket is required'));
        throw_if(!isset($this->config['endpoint']), new Z3Exception('Z3 endpoint is required'));
        throw_if(!isset($this->config['region']), new Z3Exception('Z3 region is required'));
        throw_if(!isset($this->config['encryption_key']), new Z3Exception('Z3 encryption_key is required'));
        throw_if(!isset($this->config['cipher']), new Z3Exception('Z3 cipher is required'));

        $this->encrypter = new Encrypter(base64_decode($this->config['encryption_key']), $this->config['cipher']);
    }

    public function client()
    {
        $version = Arr::get('version', $this->config, 'v4');
        return Http::withToken($this->config['secret'])
            ->baseUrl(vsprintf('https://%s.%s/api/%s/%s', [
                    $this->config['region'],
                    $this->config['endpoint'],
                    $version,
                    $this->config['bucket'],
                ])
            )
            ->acceptJson()
            ->withHeaders([
                'x-z3-date'          => now()->format('Ymd\THis\Z'),
                'x-z3-storage-class' => 'STANDARD',
                'x-z3-key'           => $this->config['key'],
            ]);
    }

    public function listObjectsV2(string $path, bool $deep)
    {
        $response = $this->client()->get('listObjectsV2', [
            'query' => [
                'prefix' => $path,
                'deep'   => $deep,
            ],
        ])->object();

        throw_unless(isset($response->Contents), new Z3Exception('Invalid response from Z3'));

        return collect($response->Contents)->map(function ($item) {
            return new Z3Attribute(new Z3Item((array) $item));
        });
    }

    public function read(string $path): string
    {
        $content = $this->client()->get('getObject', [
            'query' => [
                'path' => $path,
            ],
        ])->body();

        return $this->decryptContents($content);
    }

    public function write(string $path, string $contents): void
    {
        $response = $this->client()
            ->send('POST', 'putObject?query[path]='.urlencode($path), [
                'body' => $this->encryptContents($contents),
            ]);

        throw_unless($response->successful(), new Z3Exception('Failed to write to Z3'));
    }

    public function getFileAttributes(string $path)
    {
        $response = $this->client()->get('headObject', [
            'query' => [
                'path' => $path,
            ],
        ]);

        throw_unless($response->successful(), new Z3Exception('Invalid response from Z3'));

        return new FileAttributes(
            $path,
            $response->header('ContentLength'),
            null,
            Carbon::parse($response->header('LastModified'))->timestamp,
            explode(';', $response->header('Content-Type'))[0],
            [
                'ETag'         => $response->header('ETag'),
                'StorageClass' => $response->header('StorageClass'),
            ]
        );
    }

    public function readStream(string $path)
    {
        $response = $this->client()->get('getObject', [
            'query' => [
                'path' => $path,
            ],
        ]);

        // Erhalte den MIME-Typ aus dem Response-Header
        $mimeType = $response->getHeaderLine('Content-Type');

        // Setze den Content-Type-Header des Response neu
        $response = $response->withHeader('Content-Type', $mimeType);

        // Hole den Response-Stream
        $stream = $response->getBody();

        // Erstelle einen neuen Stream mit angepasstem Content-Type-Header
        $stream = new Stream($stream->detach());
        $stream->rewind();

        return $stream;
    }

    private function encryptContents(string $contents): string
    {
        return $this->encrypter->encrypt($contents);
    }

    private function decryptContents(string $contents): string
    {
        try {
            return $this->encrypter->decrypt($contents);
        } catch (\Exception $e) {
            throw new Z3Exception('Failed to decrypt contents');
        }
    }
}
