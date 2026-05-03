<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->string('name_fr')->nullable()->after('name');
        });

        // Copy current French names into name_fr, then set English names on `name`.
        $translations = [
            1 => ['en' => 'Colours',                    'fr' => 'Couleurs'],
            2 => ['en' => 'Sizes',                      'fr' => 'Tailles'],
            3 => ['en' => 'Shoe Size',                  'fr' => 'Pointure'],
            4 => ['en' => "Children's Size",            'fr' => 'Taille enfant'],
            5 => ['en' => "Children's Shoe Size",       'fr' => 'Pointure enfant'],
            6 => ['en' => 'Size',                       'fr' => 'Taille'],
            7 => ['en' => 'Nappy Size',                 'fr' => 'Couches'],
            8 => ['en' => 'Baby Car Seat',              'fr' => 'Sièges auto bébé'],
            9 => ['en' => "Children's Age",             'fr' => 'Âge enfant'],
            10 => ['en' => 'Maternity Support Belt',     'fr' => 'Ceintures de soutien maternité'],
            11 => ['en' => 'Shoe Size',                  'fr' => 'Pointure'],
            12 => ['en' => "Children's Hat Size",        'fr' => 'Chapeaux enfant'],
            13 => ['en' => 'Ring Size',                  'fr' => 'Bagues'],
            14 => ['en' => 'Bra Size',                   'fr' => 'Soutiens-gorge'],
            15 => ['en' => 'Bedding Size',               'fr' => 'Literie'],
        ];

        foreach ($translations as $id => $t) {
            DB::table('attributes')->where('id', $id)->update([
                'name' => $t['en'],
                'name_fr' => $t['fr'],
            ]);
        }

        // For any attributes added after this migration, copy name → name_fr as fallback.
        DB::table('attributes')->whereNull('name_fr')->update([
            'name_fr' => DB::raw('name'),
        ]);
    }

    public function down(): void
    {
        // Restore French names before dropping the column.
        DB::table('attributes')->whereNotNull('name_fr')->update([
            'name' => DB::raw('name_fr'),
        ]);

        Schema::table('attributes', function (Blueprint $table) {
            $table->dropColumn('name_fr');
        });
    }
};
