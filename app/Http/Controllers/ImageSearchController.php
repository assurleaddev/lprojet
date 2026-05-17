<?php

namespace App\Http\Controllers;

use App\Services\ImageSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImageSearchController extends Controller
{
    public function search(Request $request, ImageSearchService $service): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:5120'],
        ]);

        $result = $service->analyze($request->file('image'));

        if (isset($result['error'])) {
            $message = $result['error'] === 'model_loading'
                ? __('The image search is warming up. Please try again in a moment.')
                : __('Could not analyze the image. Please try again.');

            return response()->json(['error' => $message], 422);
        }

        $params = ['type' => 'product'];

        if ($result['category_id']) {
            $params['categories'] = [$result['category_id']];
        }

        if ($result['detected']) {
            $params['query'] = $result['detected'];
        }

        return response()->json([
            'redirect' => route('search', $params),
            'detected' => $result['detected'],
        ]);
    }
}
