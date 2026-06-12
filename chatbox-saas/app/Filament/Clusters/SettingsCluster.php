<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class SettingsCluster extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';
    protected static ?string $navigationGroup = 'Administração';
    protected static ?string $navigationLabel = 'Configurações e Segurança';
    protected static ?int $navigationSort = 3;
}
