<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PlatformLegalController extends Controller
{
    public function terms()
    {
        return view('legal.terms');
    }

    public function privacy()
    {
        return view('legal.privacy');
    }

    public function cookies()
    {
        return view('legal.cookies');
    }

    public function lgpdCentral()
    {
        return view('legal.lgpd-central');
    }

    public function submitLgpdRequest(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'request_type' => 'required|in:access,correction,deletion,portability,revoke',
            'details' => 'nullable|string',
        ]);

        $protocol = 'LGPD-' . strtoupper(uniqid());

        \App\Models\PlatformLgpdRequest::create([
            ...$validated,
            'protocol' => $protocol,
        ]);

        return back()->with('success', 'Sua solicitação foi registrada com sucesso! Protocolo: ' . $protocol);
    }

    public function pendingAcceptance()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) {
            return redirect()->route('filament.admin.auth.login');
        }

        $activeDocuments = \App\Models\PlatformLegalDocument::where('is_active', true)->get();
        $pendingDocuments = [];

        foreach ($activeDocuments as $doc) {
            $hasAccepted = \App\Models\PlatformLegalConsent::where('user_id', $user->id)
                ->where('platform_legal_document_id', $doc->id)
                ->exists();

            if (!$hasAccepted) {
                $pendingDocuments[] = $doc;
            }
        }

        if (count($pendingDocuments) === 0) {
            return redirect('/admin'); // Or whatever the dashboard route is
        }

        return view('legal.pending-acceptance', compact('pendingDocuments'));
    }

    public function acceptPending(\Illuminate\Http\Request $request)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) {
            return redirect()->route('filament.admin.auth.login');
        }

        $documentIds = $request->input('documents', []);

        foreach ($documentIds as $docId) {
            $doc = \App\Models\PlatformLegalDocument::find($docId);
            if ($doc && $doc->is_active) {
                \App\Models\PlatformLegalConsent::firstOrCreate([
                    'user_id' => $user->id,
                    'platform_legal_document_id' => $doc->id,
                ], [
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'accepted_at' => now(),
                ]);
            }
        }

        return redirect('/admin');
    }
}
