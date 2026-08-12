<?php

declare(strict_types=1);

use App\Models\Post;
use App\Services\SlugService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new SlugService();
});

test('generate unique slug derives the slug from the model title', function () {
    $post = Post::factory()->make(['title' => 'Hello World']);

    expect($this->service->generateUniqueSlug($post))->toEqual('hello-world');
});

test('generate unique slug appends a counter when the slug is taken', function () {
    Post::factory()->create(['title' => 'Hello World', 'slug' => 'hello-world']);
    Post::factory()->create(['title' => 'Hello World', 'slug' => 'hello-world-1']);

    $post = Post::factory()->make(['title' => 'Hello World']);

    expect($this->service->generateUniqueSlug($post))->toEqual('hello-world-2');
});

test('generate unique slug ignores the model being updated', function () {
    $post = Post::factory()->create(['title' => 'Hello World', 'slug' => 'hello-world']);

    expect($this->service->generateUniqueSlug($post))->toEqual('hello-world');
});

test('generate unique slug honours a custom separator', function () {
    $post = Post::factory()->make(['title' => 'Hello World']);

    expect($this->service->generateUniqueSlug($post, 'slug', '_'))->toEqual('hello_world');
});

test('generate slug from string slugifies the given label', function () {
    $post = Post::factory()->make();

    expect($this->service->generateSlugFromString('Été à Paris !', 'slug', '-', $post))
        ->toEqual('ete-a-paris');
});

test('generate slug from string appends a counter when the label is taken', function () {
    Post::factory()->create(['slug' => 'summer-sale']);

    $post = Post::factory()->make();

    expect($this->service->generateSlugFromString('Summer Sale', 'slug', '-', $post))
        ->toEqual('summer-sale-1');
});
