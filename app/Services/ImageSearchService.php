<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImageSearchService
{
    public function analyze(UploadedFile $image): array
    {
        // Use only top-level categories to keep the label list manageable
        $categories = Category::whereNull('parent_id')->get(['id', 'name']);

        if ($categories->isEmpty()) {
            $categories = Category::limit(30)->get(['id', 'name']);
        }

        $labelMap = $categories->mapWithKeys(fn ($cat) => [$cat->name => $cat->id]);

        $imageBytes = file_get_contents($image->getRealPath());
        $mimeType = $image->getMimeType() ?: 'image/jpeg';

        // Send raw binary image with candidate_labels in query string
        $labels = $labelMap->keys()->values()->toArray();
        $queryString = http_build_query(['candidate_labels' => implode(',', $labels)]);

        $response = Http::withToken(config('services.huggingface.token'))
            ->withHeaders(['Content-Type' => $mimeType])
            ->withBody($imageBytes, $mimeType)
            ->timeout(30)
            ->post('https://api-inference.huggingface.co/models/openai/clip-vit-base-patch32?' . $queryString);

        if ($response->status() === 503) {
            return ['error' => 'model_loading'];
        }

        if (! $response->successful()) {
            Log::warning('HuggingFace image search failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['error' => 'api_error'];
        }

        $results = collect($response->json())->sortByDesc('score');
        $top = $results->first();

        if (! $top || ($top['score'] ?? 0) < 0.05) {
            return ['error' => 'no_match'];
        }

        $detectedLabel = $top['label'];
        $categoryId = $labelMap->get($detectedLabel);

        return [
            'detected' => $detectedLabel,
            'category_id' => $categoryId,
            'confidence' => $top['score'],
        ];
    }
}
