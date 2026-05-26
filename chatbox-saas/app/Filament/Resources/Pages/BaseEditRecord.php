<?php

namespace App\Filament\Resources\Pages;

use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;

/**
 * Base para todas as páginas "Editar" do painel: largura confortável,
 * barra de ações fixa ao percorrer formulários longos e botões alinhados à direita.
 */
abstract class BaseEditRecord extends EditRecord
{
    protected ?string $maxContentWidth = '7xl';

    public static bool $formActionsAreSticky = true;

    public static string | Alignment $formActionsAlignment = Alignment::End;
}
