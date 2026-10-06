<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LocaleController extends Controller
{
    public function update(string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, SetLocale::SUPPORTED, true), 404);

        Auth::user()->update(['locale' => $locale]);

        return back();
    }
}
