<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Request;

class SecurityService
{
    /**
     * Registra uma atividade no sistema
     */
    public function log(string $event, string $description = null, $subject = null, array $properties = []): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => auth()->id(),
            'company_id' => auth()->user()?->company_id,
            'event' => $event,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->id : null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'properties' => $properties,
        ]);
    }

    /**
     * Valida se o upload é seguro
     */
    public function validateUpload($file, array $allowedMimes = ['image/jpeg', 'image/png', 'application/pdf'], int $maxSizeKb = 5120): bool
    {
        $mime = $file->getMimeType();
        $size = $file->getSize() / 1024;

        if (!in_array($mime, $allowedMimes)) {
            return false;
        }

        if ($size > $maxSizeKb) {
            return false;
        }

        // Adicional: Verificar se não há scripts no arquivo (simplificado)
        $content = file_get_contents($file->getRealPath());
        if (preg_match('/<\?php/i', $content) || preg_match('/<script/i', $content)) {
            return false;
        }

        return true;
    }
}
