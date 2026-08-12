<?php

declare(strict_types=1);

use App\Enums\Hooks\CommonFilterHook;
use App\Support\HookManager;

beforeEach(function () {
    $this->hooks = new HookManager();
});

test('actions run every registered listener', function () {
    $calls = [];

    $this->hooks->addAction('unit.action', function ($value) use (&$calls) {
        $calls[] = $value;
    });

    $this->hooks->doAction('unit.action', 'payload');

    expect($calls)->toEqual(['payload']);
});

test('removed actions are no longer executed', function () {
    $listener = function () {
        throw new \RuntimeException('should not run');
    };

    $this->hooks->addAction('unit.removed', $listener);
    $this->hooks->removeAction('unit.removed', $listener);

    $this->hooks->doAction('unit.removed');
})->throwsNoExceptions();

test('filters transform the value in priority order', function () {
    $this->hooks->addFilter('unit.filter', fn ($value) => $value.'-second', 20);
    $this->hooks->addFilter('unit.filter', fn ($value) => $value.'-first', 10);

    expect($this->hooks->applyFilters('unit.filter', 'base'))->toEqual('base-first-second');
});

test('filters receive the additional arguments passed to apply filters', function () {
    $this->hooks->addFilter('unit.filter.args', fn ($value, $suffix) => $value.$suffix, 20, 2);

    expect($this->hooks->applyFilters('unit.filter.args', 'base', '-extra'))->toEqual('base-extra');
});

test('applying a filter with no listeners returns the original value', function () {
    expect($this->hooks->applyFilters('unit.filter.unused', 'unchanged'))->toEqual('unchanged');
});

test('removed filters no longer transform the value', function () {
    $listener = fn ($value) => $value.'-filtered';

    $this->hooks->addFilter('unit.filter.removed', $listener);
    $this->hooks->removeFilter('unit.filter.removed', $listener);

    expect($this->hooks->applyFilters('unit.filter.removed', 'base'))->toEqual('base');
});

test('hook tags may be provided as backed enums', function () {
    $this->hooks->addFilter(CommonFilterHook::AVAILABLE_KEYS, fn ($keys) => $keys + ['extra' => 'EXTRA']);

    expect($this->hooks->applyFilters(CommonFilterHook::AVAILABLE_KEYS->value, ['app_name' => 'APP_NAME']))
        ->toEqual(['app_name' => 'APP_NAME', 'extra' => 'EXTRA']);
});
