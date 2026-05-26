<?php

namespace App\Filament\Resources\PermissionResource\Pages;

use App\Filament\Resources\PermissionResource;
use App\Models\Permission;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManagePermissions extends ManageRecords
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('export_csv')
                ->label('Exportar CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->tooltip('Exporta o catálogo completo de permissões com os perfis associados.')
                ->action(function (): StreamedResponse {
                    $permissions = Permission::with('roles')
                        ->orderBy('module')
                        ->orderBy('name')
                        ->get();

                    return response()->streamDownload(function () use ($permissions) {
                        $handle = fopen('php://output', 'w');

                        // BOM UTF-8 para compatibilidade com Excel
                        fwrite($handle, "\xEF\xBB\xBF");

                        fputcsv($handle, ['Módulo', 'Código', 'Descrição', 'Perfis Associados', 'Total de Perfis'], ';');

                        foreach ($permissions as $permission) {
                            fputcsv($handle, [
                                $permission->module ?? '',
                                $permission->name,
                                $permission->description ?? '',
                                $permission->roles->pluck('name')->join('; '),
                                $permission->roles->count(),
                            ], ';');
                        }

                        fclose($handle);
                    }, 'permissoes-' . now()->format('Ymd-His') . '.csv', [
                        'Content-Type' => 'text/csv; charset=UTF-8',
                    ]);
                }),
        ];
    }
}

