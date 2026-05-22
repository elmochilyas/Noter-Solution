<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ShortLink;
use Illuminate\Http\Request;

class ShortLinkController extends Controller
{
    public function __invoke(Request $request, string $hash)
    {
        $link = ShortLink::valid()->where('hash', $hash)->first();

        if (! $link) {
            abort(404);
        }

        return redirect($link->target_url, 301);
    }
}
