<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImpersonationLog;
use Illuminate\Http\Request;

class ImpersonationController extends Controller
{
    public function leave(Request $request)
    {
        if (session()->has('impersonated_company_id')) {
            $logId = session('impersonation_log_id');

            if ($logId) {
                ImpersonationLog::where('id', $logId)->update([
                    'ended_at' => now(),
                ]);
            }

            session()->forget([
                'impersonated_company_id',
                'impersonation_level',
                'impersonation_started_at',
                'impersonation_reason',
                'impersonation_log_id',
            ]);
        }

        return redirect()->route('filament.admin.pages.dashboard');
    }
}
