<?php
$options = \App\Models\ShippingOption::all();
foreach ($options as $option) {
    $option->price = rand(15, 35);
    $option->save();
}
echo "Prices randomized successfully.";
