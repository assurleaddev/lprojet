<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImageSearchService
{
    // BLIP image captioning — reliably available on HF free Inference API
    private const MODEL_URL = 'https://api-inference.huggingface.co/models/Salesforce/blip-image-captioning-base';

    public function analyze(UploadedFile $image): array
    {
        $imageBytes = file_get_contents($image->getRealPath());
        $mimeType = $image->getMimeType() ?: 'image/jpeg';

        $response = Http::withToken(config('services.huggingface.token'))
            ->withHeaders(['Content-Type' => $mimeType])
            ->withBody($imageBytes, $mimeType)
            ->when(app()->isLocal(), fn ($http) => $http->withoutVerifying())
            ->timeout(30)
            ->post(self::MODEL_URL);

        if ($response->status() === 503) {
            return ['error' => 'model_loading'];
        }

        if (! $response->successful()) {
            Log::warning('HuggingFace image search failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['error' => 'api_error', 'debug' => app()->isLocal() ? $response->body() : null];
        }

        $json = $response->json();

        // BLIP returns [{"generated_text": "a woman wearing a blue dress"}]
        $caption = $json[0]['generated_text'] ?? null;

        if (! $caption) {
            return ['error' => 'no_match'];
        }

        // Try to match the caption against our category names
        $categories = Category::whereNull('parent_id')->get(['id', 'name']);
        $matchedCategory = null;

        foreach ($categories as $category) {
            if (Str::contains(strtolower($caption), strtolower($category->name))) {
                $matchedCategory = $category;
                break;
            }
        }

        return [
            'detected' => $caption,
            'category_id' => $matchedCategory?->id,
            'confidence' => 1.0,
        ];
    }
}
