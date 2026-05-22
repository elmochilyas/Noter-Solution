<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Faq;

class FaqController extends Controller
{
    public function __invoke()
    {
        $categories = Faq::where('is_published', true)
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $faqs = Faq::where('is_published', true)
            ->orderBy('category')
            ->orderBy('display_order')
            ->get()
            ->groupBy('category');

        return view('public.faq', compact('categories', 'faqs'));
    }
}
