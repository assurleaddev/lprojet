<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NormalizeSizeAttributeNamesSeeder extends Seeder
{
    public function run(): void
    {
        // Match any attribute whose English name contains "size" (case-insensitive).
        // Rename both the English and French labels to the canonical "Size" / "Taille".
        $affected = DB::table('attributes')
            ->whereRaw('LOWER(name) LIKE ?', ['%size%'])
            ->orWhereRaw('LOWER(name_fr) LIKE ?', ['%taille%'])
            ->get(['id', 'name', 'name_fr']);

        foreach ($affected as $attr) {
            DB::table('attributes')->where('id', $attr->id)->update([
                'name' => 'Size',
                'name_fr' => 'Taille',
            ]);

            $this->command->line("  Renamed [{$attr->id}] \"{$attr->name}\" / \"{$attr->name_fr}\" → \"Size\" / \"Taille\"");
        }

        $this->command->info("Done. {$affected->count()} attribute(s) normalized.");
    }
}
