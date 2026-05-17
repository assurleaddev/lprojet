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
        $labels = $labelMap->keys()->values()->toArray();

        $base64 = base64_encode(file_get_contents($image->getRealPath()));

        // HF Inference API: JSON body with plain base64 + parameters.candidate_labels
        $response = Http::withToken(config('services.huggingface.token'))
            ->timeout(30)
            ->post('https://api-inference.huggingface.co/models/openai/clip-vit-base-patch32', [
                'inputs' => $base64,
                'parameters' => [
                    'candidate_labels' => $labels,
                ],
            ]);

        if ($response->status() === 503) {
            return ['error' => 'model_loading'];
        }

        if (! $response->successful()) {
            Log::warning('HuggingFace image search failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'labels_sent' => $labels,
            ]);

            return [
                'error' => 'api_error',
                'debug' => app()->isLocal() ? $response->body() : null,
            ];
        }

        $json = $response->json();

        // HF returns either a flat array [{'score':..,'label':..}] or nested
        $results = collect(is_array($json[0] ?? null) ? $json : [$json])
            ->flatten(1)
            ->sortByDesc('score');

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
