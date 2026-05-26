<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class ResetPasswordController extends Controller
{
    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reset(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        // Use Laravel's password broker to handle the reset logic (token validation, etc.)
        $response = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $this->resetPassword($user, $password);
            }
        );

        if ($response == Password::PASSWORD_RESET) {
            return response()->json([
                'message' => __($response)
            ]);
        }

        return response()->json([
            'message' => __($response)
        ], 400);
    }

    /**
     * Reset the given user's password.
     *
     * @param  \App\Models\User  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword(User $user, string $password): void
    {
        $user->forceFill([
            'password' => Hash::make($password),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));
    }
}
