<?php

// Temporary debug file
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$output = '';

$output .= "All Active Shipping Options:\n";
$allShippingOptions = \App\Models\ShippingOption::where('is_active', true)->get();
$output .= json_encode($allShippingOptions->toArray(), JSON_PRETTY_PRINT) . "\n\n";

$output .= "Default Shipping IDs from config:\n";
$defaultShippingIds = json_decode(config('settings.default_shipping_options', '[]'), true) ?? [];
$output .= json_encode($defaultShippingIds) . "\n\n";

$output .= "Filtered using whereIn on Collection:\n";
$filtered = $allShippingOptions->whereIn('id', $defaultShippingIds);
$output .= "Count: " . $filtered->count() . "\n";
$output .= json_encode($filtered->values()->toArray(), JSON_PRETTY_PRINT) . "\n";

file_put_contents(__DIR__ . '/debug_output.txt', $output);
echo "Debug output written to debug_output.txt\n";
