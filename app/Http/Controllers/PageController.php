<?php

namespace App\Http\Controllers;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Services\Content\PostType;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = Post::where('slug', $slug)
            ->where('post_type', PostType::PAGE)
            ->where('status', PostStatus::PUBLISHED->value)
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->firstOrFail();

        return view('frontend.pages.page', compact('page'));
    }
}
