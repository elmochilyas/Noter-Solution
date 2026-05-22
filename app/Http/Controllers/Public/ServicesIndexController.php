<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;

class ServicesIndexController extends Controller
{
    public function __invoke()
    {
        $services = Service::where('is_active', true)
            ->orderBy('display_order')
            ->get();

        return view('public.services.index', compact('services'));
    }
}
