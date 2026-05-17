<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImageSearchService
{
    private const UPLOAD_URL = 'https://generativelanguage.googleapis.com/upload/v1beta/files';

    private const GENERATE_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    public function analyze(UploadedFile $image): array
    {
        $apiKey = config('services.gemini.key');
        $imageBytes = file_get_contents($image->getRealPath());
        $mimeType = $image->getMimeType() ?: 'image/jpeg';

        $fileUri = $this->uploadToFilesApi($apiKey, $imageBytes, $mimeType);

        if (! $fileUri) {
            return ['error' => 'api_error'];
        }

        $categories = Category::whereNull('parent_id')->get(['id', 'name']);
        $categoryList = $categories->pluck('name')->implode(', ');

        $prompt = "You are a fashion search assistant. Look at this clothing/fashion image. "
            . "The available categories are: {$categoryList}. "
            . "Reply with ONLY a JSON object: "
            . '{\"query\": \"<2-4 word description e.g. blue dress, white sneakers>\", \"category\": \"<best matching category from the list or null>\"}';

        $response = Http::withQueryParameters(['key' => $apiKey])
            ->when(app()->isLocal(), fn ($http) => $http->withoutVerifying())
            ->timeout(30)
            ->post(self::GENERATE_URL, [
                'contents' => [[
                    'parts' => [
                        ['file_data' => ['mime_type' => $mimeType, 'file_uri' => $fileUri]],
                        ['text' => $prompt],
                    ],
                ]],
                'generationConfig' => ['temperature' => 0, 'maxOutputTokens' => 100],
            ]);

        if (! $response->successful()) {
            Log::warning('Gemini generateContent failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['error' => 'api_error', 'debug' => app()->isLocal() ? $response->body() : null];
        }

        $text = $response->json('candidates.0.content.parts.0.text');

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

    private function uploadToFilesApi(string $apiKey, string $imageBytes, string $mimeType): ?string
    {
        $boundary = 'boundary_' . bin2hex(random_bytes(8));
        $metadata = json_encode(['file' => ['displayName' => 'search_image']]);

        $body = "--{$boundary}\r\n"
            . "Content-Type: application/json; charset=utf-8\r\n\r\n"
            . $metadata . "\r\n"
            . "--{$boundary}\r\n"
            . "Content-Type: {$mimeType}\r\n\r\n"
            . $imageBytes . "\r\n"
            . "--{$boundary}--";

        $response = Http::withQueryParameters(['key' => $apiKey])
            ->withHeaders(['X-Goog-Upload-Protocol' => 'multipart'])
            ->withBody($body, "multipart/related; boundary={$boundary}")
            ->when(app()->isLocal(), fn ($http) => $http->withoutVerifying())
            ->timeout(30)
            ->post(self::UPLOAD_URL);

        if (! $response->successful()) {
            Log::warning('Gemini Files API upload failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        return $response->json('file.uri');
    }
}
