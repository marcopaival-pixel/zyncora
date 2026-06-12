<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class BillingCluster extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Administração';
    protected static ?string $navigationLabel = 'Assinatura e Faturamento';
    protected static ?int $navigationSort = 1;
}
