<?php

namespace App\Http\Controllers;

use App\Services\DemoChatService;
use Illuminate\Http\Request;

class DemoChatController extends Controller
{
    protected $demoChatService;

    public function __construct(DemoChatService $demoChatService)
    {
        $this->demoChatService = $demoChatService;
    }

    public function index()
    {
        return view('demo');
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $message = $request->input('message');
        $response = $this->demoChatService->getResponse($message);

        return response()->json($response);
    }

    public function captureLead(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'whatsapp' => 'required|string|max:20',
            'company' => 'nullable|string|max:255',
        ]);

        // Aqui poderíamos salvar no banco de dados
        // Por agora, apenas simulamos o sucesso
        
        return response()->json([
            'success' => true,
            'message' => 'Obrigado! Entraremos em contato em breve.',
        ]);
    }
}
