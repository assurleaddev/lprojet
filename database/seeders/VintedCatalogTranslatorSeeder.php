<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use App\Models\Category;

class VintedCatalogTranslatorSeeder extends Seeder
{
    private const CHUNK_SIZE = 50;

    public function run(): void
    {
        $apiKey = config('services.openai.key');

        if (! $apiKey) {
            $this->command->error('OPENAI_API_KEY is not set in your .env file.');

            return;
        }

        $categories = Category::whereNull('name_ar')
            ->orWhere('name_ar', '')
            ->get(['id', 'name', 'name_fr']);

        if ($categories->isEmpty()) {
            $this->command->info('All categories already have Arabic translations.');

            return;
        }

        $names = $categories->pluck('name')->unique()->values()->all();
        $chunks = array_chunk($names, self::CHUNK_SIZE);
        $total = count($names);
        $chunkCount = count($chunks);

        $this->command->info("Translating {$total} unique category names in {$chunkCount} batches of ".self::CHUNK_SIZE.'…');

        $translations = [];
        foreach ($chunks as $i => $chunk) {
            $batch = $i + 1;
            $this->command->line("  Batch {$batch}/{$chunkCount}…");

            $result = $this->translateBatch($chunk, $apiKey);

            if (empty($result)) {
                $this->command->error("  Batch {$batch} failed — skipping.");

                continue;
            }

            $translations = array_merge($translations, $result);
        }

        if (empty($translations)) {
            $this->command->error('No translations were returned. Aborting.');

            return;
        }

        $updated = 0;
        foreach ($categories as $category) {
            $ar = $translations[$category->name] ?? null;

            if (! $ar) {
                $this->command->warn("  No translation for: {$category->name}");

                continue;
            }

            $category->update(['name_ar' => $ar]);
            $updated++;
        }

        $this->command->info("Done — {$updated} categories updated with Arabic names.");
    }

    private function translateBatch(array $names, string $apiKey): array
    {
        $list = implode("\n", array_map(
            fn ($i, $name) => ($i + 1).'. '.$name,
            array_keys($names),
            $names
        ));

        $prompt = <<<PROMPT
You are a professional translator. Translate the following e-commerce category names into Arabic (Modern Standard Arabic). These are clothing, fashion, and kids' product categories for a second-hand marketplace.

Return ONLY a valid JSON object where each key is the EXACT original English name and the value is the Arabic translation. No extra text, no markdown, no explanation.

Category names to translate:
{$list}
PROMPT;

        $response = Http::withToken($apiKey)
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.1,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if ($response->failed()) {
            $this->command->error('OpenAI API error: '.$response->body());

            return [];
        }

        $content = $response->json('choices.0.message.content');
        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('Failed to parse OpenAI JSON response.');

            return [];
        }

        return $decoded;
    }
}
