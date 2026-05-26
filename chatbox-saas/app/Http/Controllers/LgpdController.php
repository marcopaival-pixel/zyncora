<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\LgpdConsent;
use App\Models\LgpdSetting;
use App\Support\LgpdConsentToken;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LgpdController extends Controller
{
    /**
     * Exibe a política de privacidade da empresa
     */
    public function showPrivacyPolicy(string $companySlug)
    {
        $company = Company::where('slug', $companySlug)->firstOrFail();

        $setting = LgpdSetting::where('company_id', $company->id)->first();

        if (! $setting || ! $setting->is_active) {
            abort(404, 'Política de privacidade não configurada.');
        }

        return view('lgpd.privacy-policy', [
            'company' => $company,
            'policy' => $setting->privacy_policy,
            'consentToken' => LgpdConsentToken::make($company),
        ]);
    }

    /**
     * Registra o consentimento via API (para o chatbot)
     */
    public function submitConsent(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'consent_token' => 'required|string',
            'customer_id' => 'nullable|string',
            'consent' => 'required|boolean',
        ]);

        $company = Company::query()
            ->whereKey($data['company_id'])
            ->where('status', 'active')
            ->firstOrFail();

        if (! LgpdConsentToken::isValid($company, $data['consent_token'])) {
            throw ValidationException::withMessages([
                'consent_token' => 'Token de consentimento inválido.',
            ]);
        }

        if ($data['consent']) {
            $lgpdConsent = LgpdConsent::create([
                'company_id' => $company->id,
                'customer_id' => $data['customer_id'] ?? null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'consent_given' => true,
                'consent_at' => now(),
            ]);

            return response()->json(['success' => true, 'id' => $lgpdConsent->id]);
        }

        return response()->json(['success' => false], 400);
    }
}
