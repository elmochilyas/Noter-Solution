<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    public function index(): View
    {
        $client = auth('client')->user();

        $receipts = $client->bookings()
            ->whereHas('receipt')
            ->with('receipt', 'plan')
            ->orderBy('created_at', 'desc')
            ->get()
            ->pluck('receipt')
            ->sortByDesc('issued_at');

        return view('portal.receipts.index', compact('receipts'));
    }
}
