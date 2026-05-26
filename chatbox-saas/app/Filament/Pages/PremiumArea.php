<?php

namespace App\Filament\Pages;

use App\Models\Company;
use Filament\Pages\Page;

class PremiumArea extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Ferramentas';

    protected static ?string $title = 'Área Premium';

    protected static ?string $navigationLabel = 'Premium';

    protected static string $view = 'filament.pages.premium-area';

    protected static ?int $navigationSort = 1;

    public ?Company $company = null;

    public function mount(): void
    {
        $user = auth()->user();
        
        if ($user && $user->isPlatformAdmin()) {
            return; // Admin vê tudo liberado
        }

        $company = $user?->company;

        if ($company === null) {
            abort(403, 'Acesso restrito a empresas.');
        }

        $this->company = $company;
    }
}
