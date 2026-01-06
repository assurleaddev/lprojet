<?php

use App\Models\Category;
use App\Models\Attribute;
use App\Models\Brand;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$output = "";

$category = Category::whereHas('attributes')->first();
if (!$category) {
    $output .= "No category with attributes found.\n";
    $output .= "Attributes Count: " . Attribute::count() . "\n";
    $output .= "First Attribute: " . print_r(Attribute::first(), true) . "\n";
} else {
    $output .= "Category: " . $category->name . " (ID: " . $category->id . ")\n";
    $attributes = $category->inherited_attributes;

    $output .= "Attributes Count: " . $attributes->count() . "\n";
    $output .= "First Attribute Dump:\n" . print_r($attributes->first()->toArray(), true) . "\n";

    $json = json_encode($attributes, JSON_PRETTY_PRINT);
    if ($json === false) {
        $output .= "JSON Error: " . json_last_error_msg() . "\n";
    } else {
        $output .= "JSON Output:\n" . $json;
    }
}

file_put_contents(__DIR__ . '/debug_output.txt', $output);
