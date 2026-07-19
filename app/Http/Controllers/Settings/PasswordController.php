<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

use function back;

class PasswordController extends Controller
{
    #[Middleware('throttle:6,1')]
    public function update(Request $request): RedirectResponse
    {
        $user = $this->authenticatedUser($request);
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back();
    }
}
