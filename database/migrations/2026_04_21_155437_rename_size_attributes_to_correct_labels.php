<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $renames = [
            3  => 'Pointure',        // was "Chaussures" (means shoes, not shoe size)
            5  => 'Pointure enfant', // was "Tailles de chaussures pour enfants"
            6  => 'Taille',          // was "Tailles hommes"
            11 => 'Pointure',        // was "Chaussures hommes"
            4  => 'Taille enfant',   // was "Tailles enfants et bébés"
        ];

        foreach ($renames as $id => $name) {
            DB::table('attributes')->where('id', $id)->update(['name' => $name]);
        }
    }

    public function down(): void
    {
        $originals = [
            3  => 'Chaussures',
            5  => 'Tailles de chaussures pour enfants',
            6  => 'Tailles hommes',
            11 => 'Chaussures hommes',
            4  => 'Tailles enfants et bébés',
        ];

        foreach ($originals as $id => $name) {
            DB::table('attributes')->where('id', $id)->update(['name' => $name]);
        }
    }
};
