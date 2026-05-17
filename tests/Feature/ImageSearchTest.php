<?php

use App\Services\ImageSearchService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

it('redirects to search with detected category when image matches', function () {
    $this->mock(ImageSearchService::class, function ($mock) {
        $mock->shouldReceive('analyze')->once()->andReturn([
            'detected' => 'Dresses',
            'category_id' => 42,
            'confidence' => 0.92,
        ]);
    });

    $image = UploadedFile::fake()->image('item.jpg');
    $response = $this->postJson(route('search.image'), ['image' => $image]);

    $response->assertOk()
        ->assertJsonStructure(['redirect', 'detected'])
        ->assertJsonFragment(['detected' => 'Dresses']);

    expect($response->json('redirect'))
        ->toContain('42')
        ->toContain('Dresses');
});

it('redirects with only query when category id is null', function () {
    $this->mock(ImageSearchService::class, function ($mock) {
        $mock->shouldReceive('analyze')->once()->andReturn([
            'detected' => 'Jacket',
            'category_id' => null,
            'confidence' => 0.12,
        ]);
    });

    $image = UploadedFile::fake()->image('item.jpg');
    $response = $this->postJson(route('search.image'), ['image' => $image]);

    $response->assertOk()
        ->assertJsonFragment(['detected' => 'Jacket']);
});

it('returns 422 with a friendly message when the model is warming up', function () {
    $this->mock(ImageSearchService::class, function ($mock) {
        $mock->shouldReceive('analyze')->once()->andReturn(['error' => 'model_loading']);
    });

    $image = UploadedFile::fake()->image('item.jpg');
    $response = $this->postJson(route('search.image'), ['image' => $image]);

    $response->assertStatus(422)->assertJsonStructure(['error']);
});

it('returns 422 with a friendly message when the api fails', function () {
    $this->mock(ImageSearchService::class, function ($mock) {
        $mock->shouldReceive('analyze')->once()->andReturn(['error' => 'api_error']);
    });

    $image = UploadedFile::fake()->image('item.jpg');
    $response = $this->postJson(route('search.image'), ['image' => $image]);

    $response->assertStatus(422)->assertJsonStructure(['error']);
});

it('rejects non-image files', function () {
    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $response = $this->postJson(route('search.image'), ['image' => $file]);

    $response->assertStatus(422);
});

it('rejects missing image', function () {
    $response = $this->postJson(route('search.image'), []);

    $response->assertStatus(422);
});
