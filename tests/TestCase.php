<?php

namespace SysMatter\PackageName\Tests;

use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\TestCase as Orchestra;
use SysMatter\PackageName\PackageNameServiceProvider;

abstract class TestCase extends Orchestra
{
    use WithFaker;

    protected function getPackageProviders($app): array
    {
        return [
            PackageNameServiceProvider::class,
        ];
    }
}
