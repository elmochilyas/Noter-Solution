<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PreferenceController extends Controller
{
    public function edit(): View
    {
        $client = auth('client')->user();

        return view('portal.preferences', compact('client'));
    }

    public function update(Request $request): RedirectResponse
    {
        $client = auth('client')->user();

        $data = $request->validate([
            'preferred_locale' => ['required', 'in:fr,ar'],
            'preferred_channel' => ['required', 'in:email,sms,whatsapp'],
            'phone' => ['nullable', 'string', 'max:20'],
            'full_name' => ['required', 'string', 'max:255'],
        ]);

        $client->update($data);

        activity()
            ->causedBy($client)
            ->withProperties(['changes' => array_keys($data)])
            ->log('client_preferences_updated');

        session()->flash('success', __('portal.preferences_saved'));

        return back();
    }
}
