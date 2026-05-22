<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Service;

class HomeController extends Controller
{
    public function __invoke()
    {
        $services = Service::where('is_active', true)
            ->orderBy('display_order')
            ->get();

        $faqs = Faq::where('is_published', true)
            ->orderBy('display_order')
            ->limit(3)
            ->get();

        return view('public.home', compact('services', 'faqs'));
    }
}
