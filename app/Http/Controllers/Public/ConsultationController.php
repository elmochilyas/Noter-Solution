<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ConsultationPlan;

class ConsultationController extends Controller
{
    public function __invoke()
    {
        $plans = ConsultationPlan::where('is_active', true)
            ->orderBy('display_order')
            ->get();

        return view('public.consultation', compact('plans'));
    }
}
