<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class OfficeController extends Controller
{
    public function __invoke()
    {
        $practice = Setting::practiceInfo();

        return view('public.contact', compact('practice'));
    }
}
