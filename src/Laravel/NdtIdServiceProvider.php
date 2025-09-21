<?php
declare(strict_types=1);

namespace ndtan\Laravel;

use Illuminate\Support\ServiceProvider;
use ndtan\Manager;
use ndtan\Uuid\UuidV7Generator;

final class NdtIdServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('ndtid.manager', function () {
            return new Manager([
                'default' => 'uuid7',
                'drivers' => [ 'uuid7' => [ 'class' => UuidV7Generator::class ] ]
            ]);
        });
        if (!function_exists('ndtid')) {
            function ndtid(?string $driver = null): string {
                /** @var Manager $mgr */
                $mgr = app('ndtid.manager');
                return $driver ? $mgr->driver($driver)->generate() : $mgr->generate();
            }
        }
    }
}
