<x-mail::message>
# Período de graça da assinatura

A assinatura da empresa **{{ $companyName }}** expirou, mas a conta permanece activa durante o **período de graça**.

@if($graceEndsAt)
O acesso será limitado após: **{{ $graceEndsAt->timezone(config('app.timezone'))->format('d/m/Y H:i') }}**
@endif

Restam **{{ $daysRemaining }}** dia(s) para renovar antes da degradação ao plano básico.

<x-mail::button :url="$billingUrl">
Renovar assinatura
</x-mail::button>

Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>
