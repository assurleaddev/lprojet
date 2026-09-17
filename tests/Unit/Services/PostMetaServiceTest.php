<?php

declare(strict_types=1);

use App\Models\Post;
use App\Models\PostMeta;
use App\Services\PostMetaService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new PostMetaService();
    $this->post = Post::factory()->create();
});

test('set meta creates a new record and get meta reads it back', function () {
    $meta = $this->service->setMeta($this->post->id, 'subtitle', 'Hello', 'textarea', 'default');

    expect($meta)->toBeInstanceOf(PostMeta::class)
        ->and($meta->type)->toEqual('textarea')
        ->and($meta->default_value)->toEqual('default')
        ->and($this->service->getMeta($this->post->id, 'subtitle'))->toEqual('Hello');
});

test('set meta updates the existing record for the same key', function () {
    $this->service->setMeta($this->post->id, 'subtitle', 'first');
    $this->service->setMeta($this->post->id, 'subtitle', 'second');

    expect(PostMeta::where('post_id', $this->post->id)->count())->toEqual(1)
        ->and($this->service->getMeta($this->post->id, 'subtitle'))->toEqual('second');
});

test('get meta returns the default when the key is missing', function () {
    expect($this->service->getMeta($this->post->id, 'missing'))->toBeNull()
        ->and($this->service->getMeta($this->post->id, 'missing', 'fallback'))->toEqual('fallback');
});

test('delete meta reports whether a record was removed', function () {
    $this->service->setMeta($this->post->id, 'subtitle', 'Hello');

    expect($this->service->deleteMeta($this->post->id, 'subtitle'))->toBeTrue()
        ->and($this->service->deleteMeta($this->post->id, 'subtitle'))->toBeFalse();
});

test('get all meta returns every meta record for the post', function () {
    $this->service->setMeta($this->post->id, 'a', '1');
    $this->service->setMeta($this->post->id, 'b', '2');

    expect($this->service->getAllMeta($this->post->id))->toHaveCount(2);
});

test('get all meta as array exposes value type and default per key', function () {
    $this->service->setMeta($this->post->id, 'a', '1', 'input', 'd');

    expect($this->service->getAllMetaAsArray($this->post->id))->toEqual([
        'a' => ['value' => '1', 'type' => 'input', 'default_value' => 'd'],
    ]);
});

test('get all meta values returns plain key value pairs', function () {
    $this->service->setMeta($this->post->id, 'a', '1');
    $this->service->setMeta($this->post->id, 'b', '2');

    expect($this->service->getAllMetaValues($this->post->id))->toEqual(['a' => '1', 'b' => '2']);
});

test('update multiple meta accepts both scalar values and descriptor arrays', function () {
    $this->service->updateMultipleMeta($this->post->id, [
        'plain' => 'value',
        'described' => ['value' => 'v', 'type' => 'textarea', 'default_value' => 'd'],
        '' => 'skipped',
    ]);

    $meta = $this->service->getAllMetaAsArray($this->post->id);

    expect($meta)->toHaveCount(2)
        ->and($meta['plain'])->toEqual(['value' => 'value', 'type' => 'input', 'default_value' => null])
        ->and($meta['described'])->toEqual(['value' => 'v', 'type' => 'textarea', 'default_value' => 'd']);
});

test('delete multiple meta returns the number of deleted records', function () {
    $this->service->setMeta($this->post->id, 'a', '1');
    $this->service->setMeta($this->post->id, 'b', '2');
    $this->service->setMeta($this->post->id, 'c', '3');

    expect($this->service->deleteMultipleMeta($this->post->id, ['a', 'b', 'missing']))->toEqual(2)
        ->and($this->service->getAllMetaValues($this->post->id))->toEqual(['c' => '3']);
});

test('sync meta replaces all existing meta for the post', function () {
    $this->service->setMeta($this->post->id, 'old', 'value');

    $this->service->syncMeta($this->post->id, ['new' => 'value']);

    expect($this->service->getAllMetaValues($this->post->id))->toEqual(['new' => 'value']);
});

test('meta operations are scoped to a single post', function () {
    $other = Post::factory()->create();

    $this->service->setMeta($this->post->id, 'a', 'mine');
    $this->service->setMeta($other->id, 'a', 'theirs');

    $this->service->deleteMultipleMeta($this->post->id, ['a']);

    expect($this->service->getMeta($other->id, 'a'))->toEqual('theirs');
});
