<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class ImageSearchService
{
    public function analyze(UploadedFile $image): array
    {
        $categories = Category::all(['id', 'name']);

        $labelMap = $categories->mapWithKeys(function ($cat) {
            return ["a photo of {$cat->name}" => $cat->id];
        });

        $base64 = base64_encode(file_get_contents($image->getRealPath()));
        $mimeType = $image->getMimeType() ?: 'image/jpeg';

        $response = Http::withToken(config('services.huggingface.token'))
            ->timeout(30)
            ->post('https://api-inference.huggingface.co/models/openai/clip-vit-base-patch32', [
                'inputs' => "data:{$mimeType};base64,{$base64}",
                'parameters' => [
                    'candidate_labels' => $labelMap->keys()->values()->toArray(),
                ],
            ]);

        if ($response->status() === 503) {
            return ['error' => 'model_loading'];
        }

        if (! $response->successful()) {
            return ['error' => 'api_error'];
        }

        $results = collect($response->json())->sortByDesc('score');
        $top = $results->first();

        if (! $top || ($top['score'] ?? 0) < 0.05) {
            return ['error' => 'no_match'];
        }

        $detectedLabel = $top['label'];
        $categoryId = $labelMap->get($detectedLabel);
        $cleanLabel = str_replace('a photo of ', '', $detectedLabel);

        return [
            'detected' => $cleanLabel,
            'category_id' => $categoryId,
            'confidence' => $top['score'],
        ];
    }
}
