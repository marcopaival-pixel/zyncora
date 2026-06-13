<?php

namespace App\Traits;

use App\Models\Company;
use App\Services\TenantService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToCompany
{
    public static function bootBelongsToCompany(): void
    {
        static::creating(function (Model $model) {
            if (! $model->company_id && $companyId = app(TenantService::class)->getCompanyId()) {
                $model->company_id = $companyId;
            }
        });

        static::addGlobalScope('company', function (Builder $builder) {
            $tenantService = app(TenantService::class);
            $table = $builder->getQuery()->from;

            // 1. Prioridade: Contexto definido explicitamente no TenantService (Middleware/API/Jobs)
            if ($companyId = $tenantService->getCompanyId()) {
                $builder->where("{$table}.company_id", $companyId);

                return;
            }

            // 2. Contexto de Usuário Autenticado (Painel Admin Filament)
            if (auth()->check()) {
                $user = auth()->user();

                // Super-admins da plataforma veem todos os dados
                if ($user->isPlatformAdmin()) {
                    return;
                }

                // Usuários vinculados a uma empresa veem apenas os seus dados
                if ($user->company_id) {
                    $builder->where("{$table}.company_id", $user->company_id);

                    return;
                }
            }

            // 3. SEGURANÇA (Fail-Safe): Se não houver contexto identificado, o padrão SaaS é NADA.
            // Isso evita vazamento acidental de dados de múltiplos inquilinos (Data Leakage).
            $builder->whereRaw('1 = 0');
        });
    }

    /**
     * Relacionamento com a empresa proprietária do registo.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
