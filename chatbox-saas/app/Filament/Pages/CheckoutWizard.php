<?php

namespace App\Filament\Pages;

use App\Models\Plan;
use App\Services\BillingCheckoutService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Forms\Form;

class CheckoutWizard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $title = 'Checkout';
    protected static ?string $slug = 'checkout/{plan}';
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.checkout-wizard';

    public ?array $data = [];
    public Plan $plan;

    public function mount(Plan $plan): void
    {
        $this->plan = $plan;

        $user = Auth::user();
        $company = $user?->company;

        if (!$company) {
            abort(403, 'Empresa não encontrada.');
        }

        $this->form->fill([
            'payment_method' => 'stripe',
            'document' => $company->cnpj ?? '',
            'address' => '',
            'city' => '',
            'state' => '',
            'zip' => '',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Resumo')
                        ->description('Confirme os detalhes do seu plano')
                        ->schema([
                            Forms\Components\ViewField::make('plan_summary')
                                ->view('filament.components.plan-summary')
                                ->viewData(['plan' => $this->plan]),
                        ]),
                    Forms\Components\Wizard\Step::make('Dados Fiscais')
                        ->description('Informações para nota fiscal')
                        ->schema([
                            Forms\Components\TextInput::make('document')
                                ->label('CPF / CNPJ')
                                ->required(),
                            Forms\Components\TextInput::make('address')
                                ->label('Endereço')
                                ->required(),
                            Forms\Components\TextInput::make('city')
                                ->label('Cidade')
                                ->required(),
                            Forms\Components\TextInput::make('state')
                                ->label('Estado')
                                ->required(),
                            Forms\Components\TextInput::make('zip')
                                ->label('CEP')
                                ->required(),
                        ]),
                    Forms\Components\Wizard\Step::make('Pagamento')
                        ->description('Escolha a forma de pagamento')
                        ->schema([
                            Forms\Components\Radio::make('payment_method')
                                ->label('Método de Pagamento')
                                ->options([
                                    'stripe' => 'Cartão de Crédito',
                                    'mercadopago' => 'PIX / Boleto',
                                ])
                                ->required(),
                        ]),
                ])->submitAction(
                    Forms\Components\Actions\Action::make('checkout')
                        ->label('Ir para o Pagamento')
                        ->submit('submit')
                ),
            ])
            ->statePath('data');
    }

    public function submit()
    {
        $data = $this->form->getState();
        $user = Auth::user();
        $company = $user->company;

        // Save fiscal data
        $company->update([
            'cnpj' => $data['document'] ?? $company->cnpj,
            // You might want to save address fields as well
        ]);

        // Route to the appropriate payment provider
        if ($data['payment_method'] === 'stripe') {
            config(['chatbox.payment_driver' => 'stripe']);
        } else {
            config(['chatbox.payment_driver' => 'mercadopago']);
        }

        try {
            $checkoutUrl = app(BillingCheckoutService::class)->checkoutUrl($company, $this->plan, $user);
            return redirect($checkoutUrl);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro ao processar o pagamento')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
