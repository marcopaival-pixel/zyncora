<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Chatbot;
use App\Models\ChatbotLicense;
use App\Models\ChatbotSecurityToken;
use App\Services\WidgetSecurityService;
use Illuminate\Support\Str;

class MigrateLegacyChatbotDomains extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'widget:migrate-legacy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate legacy chatbots to the new licensing and security system';

    /**
     * Execute the console command.
     */
    public function handle(WidgetSecurityService $securityService)
    {
        $this->info('Starting legacy chatbot migration...');

        // Obter chatbots sem licença
        $chatbots = Chatbot::whereDoesntHave('license')->get();
        $count = $chatbots->count();

        $this->info("Found {$count} legacy chatbots to migrate.");

        $bar = $this->output->createProgressBar($count);

        foreach ($chatbots as $chatbot) {
            // 1. Criar Licença Ativa
            ChatbotLicense::create([
                'chatbot_id' => $chatbot->id,
                'company_id' => $chatbot->company_id,
                'license_key' => Str::uuid()->toString(),
                'status' => 'active',
                'expires_at' => null, // Legacy starts without expiration initially
            ]);

            // 2. Gerar Tokens de Segurança
            if (!ChatbotSecurityToken::where('chatbot_id', $chatbot->id)->exists()) {
                $securityService->generateTokens($chatbot);
            }

            // O modo de aprendizado será automático:
            // Quando a API receber a primeira requisição sem domínio aprovado, se o domínio
            // do chatbot estiver vazio, ele auto-aprovará o primeiro domínio que chegar (via log listener ou middleware futuro).
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Migration completed successfully!');
    }
}
