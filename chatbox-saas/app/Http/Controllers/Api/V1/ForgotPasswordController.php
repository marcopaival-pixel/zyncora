<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetLink(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        // Check if user exists
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // We return a success response even if the user doesn't exist for security reasons
            // to prevent email enumeration.
            return response()->json([
                'message' => __('We have emailed your password reset link!')
            ]);
        }

        // Generate token using Laravel's built-in broker
        $token = Password::createToken($user);

        // Send custom notification
        $user->notify(new ResetPasswordNotification($token));

        return response()->json([
            'message' => __('We have emailed your password reset link!')
        ]);
    }
}
