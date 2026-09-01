<?php

declare(strict_types=1);

use App\Livewire\Search\CategoryFilter;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

// --- P9b: seller initials (first of first+last name, or first 2 chars) ---

test('initials use first letter of first and last name', function () {
    $u = new User(['first_name' => 'Houda', 'last_name' => 'Store']);
    expect($u->initials)->toBe('HS');
});

test('initials fall back to first two chars for a single-word name', function () {
    $u = new User(['username' => 'Houdastore']);
    expect($u->initials)->toBe('HO');

    $u2 = new User(['first_name' => 'Reda']);
    expect($u2->initials)->toBe('RE');
});

// --- P1: category filter back navigation must not 500 ---

test('category filter drills down and back without leaking the request into links', function () {
    $women = Category::create(['name' => 'Women', 'slug' => 'women']);
    $clothing = Category::create(['name' => 'Clothing', 'slug' => 'clothing', 'parent_id' => $women->id]);
    Category::create(['name' => 'Dresses', 'slug' => 'dresses', 'parent_id' => $clothing->id]);

    Livewire::test(CategoryFilter::class, ['categoryIds' => []])
        ->assertSee('Women')                 // root list
        ->call('drillDown', $women->id)
        ->assertSee('Clothing')              // Women's children
        ->call('goBack')                     // back to root — this is what used to 500
        ->assertSee('Women')
        ->assertOk();
});

test('base query is captured once at mount, not from the live-update request', function () {
    $c = Category::create(['name' => 'Men', 'slug' => 'men']);

    Livewire::test(CategoryFilter::class, ['categoryIds' => []])
        ->call('drillDown', $c->id)
        ->call('goBack')
        // baseQuery stays an array (never the Livewire payload) across re-renders.
        ->assertSet('baseQuery', fn ($v) => is_array($v));
});
