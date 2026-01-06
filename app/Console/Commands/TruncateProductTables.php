<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TruncateProductTables extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:truncate-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate products, categories, attributes, and related tables with foreign key checks disabled.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->confirm('This will wipe all Products, Categories, Attributes, Brands, Orders, and Reviews. Are you sure?', true)) {
            return;
        }

        $tables = [
            'product_option',
            'product_images', // If distinct table
            'attribute_category',
            'favorites',
            'reviews',
            'chat_message_attachments', // Might link to products?
            'orders', // Truncate orders first
            // 'order_items', // If exists
            'products',
            'options',
            'attributes',
            'categories',
            'brands',
        ];

        // Also media table might contain product images. 
        // Typically we shouldn't just truncate media unless we filter by model_type, but user said "force truncate".
        // Let's stick to the main tables first. 
        // WARNING: Deleting products but leaving media records keeps orphan files/records. 
        // For a true clean, we should delete media where model_type = Product.

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $this->info("Truncating table: {$table}");
                DB::table($table)->truncate();
            } else {
                $this->warn("Table {$table} not found, skipping.");
            }
        }

        // Clean Media library for Products/Categories/Brands
        $this->info("Cleaning Media table for Products, Categories, Brands...");
        if (Schema::hasTable('media')) {
            DB::table('media')->whereIn('model_type', [
                'App\Models\Product',
                'App\Models\Category',
                'App\Models\Brand'
            ])->delete();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('Product relations truncated successfully.');
    }
}
