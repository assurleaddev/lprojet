<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImageSearchService
{
    private const GEMINI_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

    public function analyze(UploadedFile $image): array
    {
        $base64 = base64_encode(file_get_contents($image->getRealPath()));
        $mimeType = $image->getMimeType() ?: 'image/jpeg';

        $categories = Category::whereNull('parent_id')->get(['id', 'name']);
        $categoryList = $categories->pluck('name')->implode(', ');

        $prompt = "You are a fashion search assistant. Look at this clothing/fashion image and identify what it shows. "
            . "The available categories are: {$categoryList}. "
            . "Reply with ONLY a JSON object in this exact format: "
            . '{\"query\": \"<2-4 word description like blue dress or white sneakers>\", \"category\": \"<best matching category name from the list or null>\"} '
            . 'No explanation, just the JSON.';

        $response = Http::withQueryParameters(['key' => config('services.gemini.key')])
            ->when(app()->isLocal(), fn ($http) => $http->withoutVerifying())
            ->timeout(30)
            ->post(self::GEMINI_URL, [
                'contents' => [[
                    'parts' => [
                        ['inline_data' => ['mime_type' => $mimeType, 'data' => $base64]],
                        ['text' => $prompt],
                    ],
                ]],
                'generationConfig' => ['temperature' => 0, 'maxOutputTokens' => 100],
            ]);

        if (! $response->successful()) {
            Log::warning('Gemini image search failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['error' => 'api_error', 'debug' => app()->isLocal() ? $response->body() : null];
        }

        $text = $response->json('candidates.0.content.parts.0.text');

        if (! $text) {
            return ['error' => 'no_match'];
        }

        // Strip markdown code fences if Gemini wraps the JSON
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
