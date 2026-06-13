<?php

namespace App\Services;

use App\Http\Middleware\EnsureSingleSession;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;

/**
 * Sincroniza sessão web após login no Filament (depois do {@see session()->regenerate()}).
 * O evento {@see Login} dispara antes desse regenerate; gravar o ID
 * da sessão nessa altura invalidava {@see User::$current_session_id} e o
 * {@see EnsureSingleSession} terminava a sessão no pedido seguinte.
 */
class WebLoginSessionService
{
    public function syncAfterFilamentLogin(User $user): void
    {
        $sessionId = session()->getId();

        if ($user->current_session_id) {
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', '!=', $sessionId)
                ->delete();

            DB::table('user_session_logs')
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'logout_at' => now(),
                ]);
        }

        $user->forceFill(['current_session_id' => $sessionId])->save();

        $userAgent = request()->header('User-Agent');

        DB::table('user_session_logs')->insert([
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'ip_address' => request()->ip(),
            'user_agent' => $userAgent,
            'device_type' => $this->detectDeviceType($userAgent),
            'browser' => $this->detectBrowser($userAgent),
            'platform' => $this->detectPlatform($userAgent),
            'login_at' => now(),
            'last_activity_at' => now(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function detectDeviceType(?string $userAgent): string
    {
        if ($userAgent === null || $userAgent === '') {
            return 'desktop';
        }

        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $userAgent)) {
            return 'tablet';
        }

        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', $userAgent)) {
            return 'mobile';
        }

        return 'desktop';
    }

    private function detectBrowser(?string $userAgent): string
    {
        if ($userAgent === null || $userAgent === '') {
            return 'Unknown';
        }

        if (str_contains($userAgent, 'Opera') || str_contains($userAgent, 'OPR')) {
            return 'Opera';
        }
        if (str_contains($userAgent, 'Edge')) {
            return 'Edge';
        }
        if (str_contains($userAgent, 'Chrome')) {
            return 'Chrome';
        }
        if (str_contains($userAgent, 'Safari')) {
            return 'Safari';
        }
        if (str_contains($userAgent, 'Firefox')) {
            return 'Firefox';
        }
        if (str_contains($userAgent, 'MSIE') || str_contains($userAgent, 'Trident/7')) {
            return 'Internet Explorer';
        }

        return 'Unknown';
    }

    private function detectPlatform(?string $userAgent): string
    {
        if ($userAgent === null || $userAgent === '') {
            return 'Unknown';
        }

        if (preg_match('/linux/i', $userAgent)) {
            return 'Linux';
        }
        if (preg_match('/macintosh|mac os x/i', $userAgent)) {
            return 'Mac';
        }
        if (preg_match('/windows|win32/i', $userAgent)) {
            return 'Windows';
        }
        if (preg_match('/iphone/i', $userAgent)) {
            return 'iOS';
        }
        if (preg_match('/android/i', $userAgent)) {
            return 'Android';
        }

        return 'Unknown';
    }
}
