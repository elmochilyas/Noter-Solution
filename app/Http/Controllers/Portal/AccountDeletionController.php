<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AccountDeletionController extends Controller
{
    public function confirm(): View
    {
        return view('portal.account.delete');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'confirmation' => ['required', 'string'],
        ]);

        $expected = app()->getLocale() === 'ar' ? 'حذف' : 'SUPPRIMER';

        if (trim($data['confirmation']) !== $expected) {
            return back()->withErrors(['confirmation' => __('portal.deletion_confirmation_mismatch')]);
        }

        $client = auth('client')->user();

        DB::transaction(function () use ($client): void {
            $uuid = (string) Str::uuid();

            $client->documents()->delete();

            $client->update([
                'email' => "deleted-{$uuid}@anonymized",
                'full_name' => '('.__('portal.deleted').')',
                'phone' => null,
                'national_id' => null,
                'national_id_last4' => null,
            ]);

            activity()
                ->causedBy($client)
                ->log('client_account_deleted');
        });

        Auth::guard('client')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()
            ->route('portal.login', ['locale' => $request->route('locale')])
            ->with('deleted', true);
    }
}
