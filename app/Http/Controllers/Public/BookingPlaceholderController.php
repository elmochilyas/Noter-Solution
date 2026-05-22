<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

class BookingPlaceholderController extends Controller
{
    public function __invoke()
    {
        return view('public.book-coming-soon');
    }
}
