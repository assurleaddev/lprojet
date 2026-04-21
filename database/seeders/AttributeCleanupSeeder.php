<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttributeCleanupSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Rename misnamed size attributes to correct display labels
        $renames = [
            'size_group_7'  => 'Pointure',
            'size_group_31' => 'Pointure enfant',
            'size_group_14' => 'Taille',
            'size_group_38' => 'Pointure',
            'size_group_32' => 'Taille enfant',
        ];

        foreach ($renames as $code => $name) {
            DB::table('attributes')->where('code', $code)->update(['name' => $name]);
        }

        // 2. Remove attribute assignments from non-leaf categories (those with children).
        //    Leaf categories already have the correct Vinted-mapped attributes.
        $nonLeafIds = DB::table('categories as c')
            ->whereExists(fn ($q) => $q->select(DB::raw(1))
                ->from('categories as child')
                ->whereColumn('child.parent_id', 'c.id'))
            ->pluck('c.id')
            ->toArray();

        if (! empty($nonLeafIds)) {
            DB::table('attribute_category')
                ->whereIn('category_id', $nonLeafIds)
                ->delete();
        }

        $this->command->info('Attribute cleanup done.');
    }
}
