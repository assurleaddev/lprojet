<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c = App\Models\Category::find(117);
while ($c) {
    echo "Category: {$c->name} ({$c->id})\n";
    echo "Attributes: " . $c->assignedAttributes->pluck('name')->join(', ') . "\n";
    echo "---\n";
    $c = $c->parent;
}
