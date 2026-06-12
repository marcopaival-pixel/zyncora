<x-mail::message>
# Pagamento Confirmado!

Olá,

Recebemos com sucesso o pagamento da sua assinatura.
Obrigado por utilizar a Zincora!

**Detalhes:**
- **Valor:** R$ {{ number_format($paymentHistory->amount, 2, ',', '.') }}
- **Data:** {{ $paymentHistory->paid_at->format('d/m/Y H:i') }}
- **Empresa:** {{ $paymentHistory->company->name }}

<x-mail::button :url="url('/admin/billing')">
Acessar Minha Conta
</x-mail::button>

Atenciosamente,<br>
{{ config('app.name') }}
</x-mail::message>
