<?php

namespace Zhylon\Z3Filesystem;

use League\Flysystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Contracts\Foundation\Application;

class Z3Provider extends ServiceProvider
{
    public function boot(): void
    {
        Storage::extend('z3', function (Application $app, array $config) {
            $adapter = new Z3Adapter(new Z3Client(
                $config,
            ));

            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });
    }
}
