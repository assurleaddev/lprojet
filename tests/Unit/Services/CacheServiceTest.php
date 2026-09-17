<?php

declare(strict_types=1);

use App\Services\CacheService;
use Illuminate\Support\Facades\Artisan;

test('clear cache clears config route view and application caches', function () {
    $called = [];

    Artisan::shouldReceive('call')->times(4)->andReturnUsing(function ($command) use (&$called) {
        $called[] = $command;

        return 0;
    });

    (new CacheService())->clearCache();

    expect($called)->toEqual(['config:clear', 'route:cache', 'view:clear', 'cache:clear']);
});

test('clear necessary caches only clears config and route caches', function () {
    $called = [];

    Artisan::shouldReceive('call')->times(2)->andReturnUsing(function ($command) use (&$called) {
        $called[] = $command;

        return 0;
    });

    (new CacheService())->clearNecessaryCaches();

    expect($called)->toEqual(['config:clear', 'route:cache']);
});

test('clear cache keeps clearing the remaining caches when one command fails', function () {
    $called = [];

    Artisan::shouldReceive('call')->times(4)->andReturnUsing(function ($command) use (&$called) {
        $called[] = $command;

        if ($command === 'config:clear') {
            throw new \RuntimeException('boom');
        }

        return 0;
    });

    (new CacheService())->clearCache();

    expect($called)->toEqual(['config:clear', 'route:cache', 'view:clear', 'cache:clear']);
});
