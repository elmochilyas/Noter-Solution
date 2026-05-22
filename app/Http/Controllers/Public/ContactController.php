<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

class ContactController extends Controller
{
    public function __invoke()
    {
        return view('public.contact');
    }
}
