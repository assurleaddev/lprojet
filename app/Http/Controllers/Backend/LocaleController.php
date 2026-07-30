<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;

class LocaleController extends Controller
{
    public function switch(string $lang): RedirectResponse
    {
        if (! preg_match('/^[a-z]{2}(?:[_-][A-Za-z]{2,4})?$/', $lang)
            || ! File::exists(resource_path("lang/{$lang}.json"))) {
            abort(404);
        }

        session()->put('locale', $lang);
        App::setLocale($lang);

        session()->flash('success', 'Language changed successfully!');

        return redirect()->back();
    }
}
