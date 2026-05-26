<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RagScraperService
{
    /**
     * Busca o conteúdo de uma URL e extrai apenas o texto útil (sem scripts, estilos, header, footer).
     */
    public function scrapeUrl(string $url): ?string
    {
        try {
            $response = Http::timeout(15)->get($url);

            if (! $response->successful()) {
                Log::warning("RagScraperService: Falha ao acessar URL {$url}. Status: " . $response->status());
                return null;
            }

            $html = $response->body();
            return $this->extractTextFromHtml($html);
        } catch (\Exception $e) {
            Log::error("RagScraperService: Erro ao raspar URL {$url} - " . $e->getMessage());
            return null;
        }
    }

    /**
     * Extrai texto limpo de uma string HTML.
     */
    protected function extractTextFromHtml(string $html): string
    {
        // Se o HTML estiver vazio, retorna string vazia
        if (empty(trim($html))) {
            return '';
        }

        // Suprime warnings do DOMDocument relacionados a HTML malformado
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOAUTODTD | LIBXML_NOWARNING);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        // Removemos as tags que contêm conteúdo que não é útil para contexto de IA (Scripts, estilos, navegação, rodapé)
        $nodesToDelete = $xpath->query('//script | //style | //nav | //footer | //header | //noscript | //iframe | //svg');
        foreach ($nodesToDelete as $node) {
            $node->parentNode->removeChild($node);
        }

        // Focamos em extrair parágrafos, cabeçalhos, listas, e blocos de artigo.
        // Opcionalmente podemos pegar o body inteiro
        $bodyNodes = $xpath->query('//body');
        
        if ($bodyNodes->length > 0) {
            $body = $bodyNodes->item(0);
            $textContent = $body->textContent;
        } else {
            $textContent = $dom->textContent;
        }

        // Limpeza do texto extraído: remover múltiplos espaços e quebras de linha inúteis
        $textContent = preg_replace("/\s+/", " ", $textContent); // Converte quebras e tabs em espaço único
        $textContent = trim($textContent);

        return $textContent;
    }
}
