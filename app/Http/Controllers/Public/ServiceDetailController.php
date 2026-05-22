<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Service;

class ServiceDetailController extends Controller
{
    public function __invoke(string $slug)
    {
        $service = Service::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $faqs = Faq::where('is_published', true)
            ->where('category', $slug)
            ->orderBy('display_order')
            ->limit(4)
            ->get();

        return view('public.services.show', compact('service', 'faqs'));
    }
}
