<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserCredentialController extends Controller
{
    public function sendCredentials(Request $request, User $user): RedirectResponse
    {
        // Ensure only admin can send credentials
        if (! auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $customPassword = $request->input('custom_password');

        $success = $user->sendCredentials($customPassword);

        if ($success) {
            return redirect()->back()->with('success', 'Credential berhasil dikirim ke '.$user->email);
        } else {
            return redirect()->back()->with('error', 'Gagal mengirim credential. Silakan coba lagi.');
        }
    }
}
