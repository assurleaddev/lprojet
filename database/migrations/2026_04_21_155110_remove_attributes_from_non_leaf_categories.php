<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remove attribute assignments from every category that has children.
        // Leaf categories (no children) already have the correct Vinted-mapped attributes.
        // Parent/intermediate categories have bloated sets that cause unrelated
        // attributes to appear in product forms.
        $nonLeafIds = DB::table('categories as c')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('categories as child')
                    ->whereColumn('child.parent_id', 'c.id');
            })
            ->pluck('c.id')
            ->toArray();

        if (! empty($nonLeafIds)) {
            DB::table('attribute_category')
                ->whereIn('category_id', $nonLeafIds)
                ->delete();
        }
    }

    public function down(): void
    {
        // Cannot reliably restore — re-run VintedCatalogSeeder to restore all assignments.
    }
};
