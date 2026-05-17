<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImageSearchService
{
    public function analyze(UploadedFile $image): array
    {
        $base64 = base64_encode(file_get_contents($image->getRealPath()));
        $mimeType = $image->getMimeType() ?: 'image/jpeg';

        $categories = Category::whereNull('parent_id')->get(['id', 'name']);
        $categoryList = $categories->pluck('name')->implode(', ');

        $prompt = "You are a fashion search assistant. Look at this clothing/fashion image. "
            . "The available categories are: {$categoryList}. "
            . "Reply with ONLY a JSON object: "
            . '{\"query\": \"<2-4 word description e.g. blue dress, white sneakers>\", \"category\": \"<best matching category from the list or null>\"}';

        $response = Http::withToken(config('services.openai.key'))
            ->when(app()->isLocal(), fn ($http) => $http->withoutVerifying())
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'max_tokens' => 100,
                'messages' => [[
                    'role' => 'user',
                    'content' => [
                        ['type' => 'image_url', 'image_url' => ['url' => "data:{$mimeType};base64,{$base64}", 'detail' => 'low']],
                        ['type' => 'text', 'text' => $prompt],
                    ],
                ]],
            ]);

        if (! $response->successful()) {
            Log::warning('OpenAI image search failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['error' => 'api_error', 'debug' => app()->isLocal() ? $response->body() : null];
        }

        $text = $response->json('choices.0.message.content');

        if (! $text) {
            return ['error' => 'no_match'];
        }

        $text = trim(preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($text)));
        $parsed = json_decode($text, true);
        $query = $parsed['query'] ?? null;
        $categoryName = $parsed['category'] ?? null;

        if (! $query) {
            return ['error' => 'no_match'];
        }

        $matchedCategory = $categoryName
            ? $categories->first(fn ($c) => Str::lower($c->name) === Str::lower($categoryName))
            : null;

        return [
            'detected' => $query,
            'category_id' => $matchedCategory?->id,
            'confidence' => 1.0,
        ];
    }
}
