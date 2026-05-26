<x-mail::message>
# Assinatura a expirar

A assinatura da empresa **{{ $companyName }}** expira em **{{ $daysRemaining }}** dia(s).

@if($expiresAt)
Data de expiração: **{{ $expiresAt->timezone(config('app.timezone'))->format('d/m/Y H:i') }}**
@endif

Renove o plano para evitar interrupção do widget e dos limites da conta.

<x-mail::button :url="$billingUrl">
Gerir assinatura
</x-mail::button>

Obrigado,<br>
{{ config('app.name') }}
</x-mail::message>
