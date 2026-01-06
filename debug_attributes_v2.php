<?php

use App\Models\Category;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$out = [];
$out[] = "START DEBUG ACCESSOR";

$cat = Category::whereHas('attributes')->first();

if (!$cat) {
    $out[] = "No category with attributes found!";
} else {
    $out[] = "Category Found: {$cat->name} (ID: {$cat->id})";

    // Call ACCESSOR
    $fromAccessor = $cat->inherited_attributes;

    $out[] = "Accessor Result Count: " . $fromAccessor->count();
    $first = $fromAccessor->first();

    if (is_object($first)) {
        $out[] = "First Item Type: " . get_class($first);
        $out[] = "First Item Data: " . json_encode($first->toArray());
    } else {
        $out[] = "First Item Type: " . gettype($first);
        $out[] = "First Item Value: " . json_encode($first);
    }

    $out[] = "Available Keys on Collection: " . implode(', ', array_keys($fromAccessor->toArray()));
}

file_put_contents(__DIR__ . '/debug_attributes_out.txt', implode("\n", $out));
echo "Done.\n";
