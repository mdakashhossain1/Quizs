<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Public Terms & Conditions / Privacy Policy pages, linked to directly from
 * the Flutter app's profile screen (see ApiClient.baseUrl — this ships at
 * the domain root, e.g. https://quizs.in/terms, via the admin/.htaccess
 * root bridge).
 */
class LegalController extends Controller
{
    public function terms(): View
    {
        return view('legal.terms');
    }

    public function privacy(): View
    {
        return view('legal.privacy');
    }
}
