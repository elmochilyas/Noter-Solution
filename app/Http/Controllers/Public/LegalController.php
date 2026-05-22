<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

class LegalController extends Controller
{
    public function __invoke(string $page)
    {
        $pages = ['mentions-legales', 'politique-confidentialite', 'conditions-utilisation'];

        if (! in_array($page, $pages)) {
            abort(404);
        }

        return view('public.legal.show', compact('page'));
    }
}
