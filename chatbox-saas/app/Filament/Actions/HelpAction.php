<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Illuminate\Contracts\View\View;
use App\Models\HelpArticle;
use App\Models\HelpLog;

class HelpAction extends Action
{
    protected ?string $moduleName = null;

    public static function getDefaultName(): ?string
    {
        return 'help';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Como funciona')
            ->icon('heroicon-o-question-mark-circle')
            ->color('gray')
            ->slideOver()
            ->modalHeading(fn () => 'Ajuda: ' . ($this->moduleName ?? 'Geral'))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar')
            ->modalContent(function (): View {
                $article = HelpArticle::where('module', $this->moduleName)->where('is_active', true)->first();
                
                if ($article && auth()->check()) {
                    HelpLog::create([
                        'help_article_id' => $article->id,
                        'user_id' => auth()->id(),
                        'company_id' => filament()->getTenant()?->id,
                        'action' => 'view',
                    ]);
                }

                return view('filament.components.help-article-content', [
                    'article' => $article,
                    'moduleName' => $this->moduleName
                ]);
            });
    }

    public function module(string $moduleName): static
    {
        $this->moduleName = $moduleName;

        return $this;
    }
}
