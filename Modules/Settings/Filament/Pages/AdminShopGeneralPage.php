<?php

namespace Modules\Settings\Filament\Pages;

use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\HtmlString;
use MicroweberPackages\Admin\Filament\Pages\Abstract\AdminSettingsPage;
use Modules\Payment\Filament\Admin\Resources\PaymentProviderResource;
use Modules\Shipping\Filament\Admin\Resources\ShippingProviderResource;

class AdminShopGeneralPage extends AdminSettingsPage
{
    protected static ?string $navigationIcon = 'mw-shop2';

    protected static string $view = 'modules.settings::filament.admin.pages.settings-form';

    protected static ?string $title = 'Main Shop Settings';

    protected static string $description = 'Configure your shop general settings';

    protected static ?string $navigationGroup = 'Shop Settings';

    public array $optionGroups = [
        'website',
        'payments',
    ];


    public function form(Form $form): Form
    {
        return $form
            ->schema([


                Section::make('Currency settings')
                    ->view('filament-forms::sections.section')
                    ->description('Set the default currency and symbol for your shop.')
                    ->schema([

                        Select::make('options.payments.currency')
                            ->label('Set default currency')
                            ->live()
                            ->options([
                                'USD' => 'USD',
                                'EUR' => 'EUR',
                                'GBP' => 'GBP',
                                'AUD' => 'AUD',
                                'CAD' => 'CAD',
                                'JPY' => 'JPY',
                                'CNY' => 'CNY',
                                'INR' => 'INR',
                                'RUB' => 'RUB',
                                'UAH' => 'UAH',
                                'PLN' => 'PLN',
                                'CHF' => 'CHF',
                                'SEK' => 'SEK',
                                'NOK' => 'NOK',
                                'DKK' => 'DKK',
                                'CZK' => 'CZK',
                                'HUF' => 'HUF',
                                'HRK' => 'HRK',
                                'BGN' => 'BGN',


                            ])
                            ->helperText(function () {
                                return new HtmlString('<small class="mb-2 text-muted">Default currency with which you will accept payments.</small>');
                            }),

                        Select::make('options.payments.currency_symbol_position')
                            ->label('Currency symbol position')
                            ->live()
                            ->options([
                                '' => 'Default',
                                'before' => 'Before amount',
                                'after' => 'After amount',
                            ])
                            ->helperText(function () {
                                return new HtmlString('<small class="mb-2 text-muted">Where to display the currency symbol before, after or by default relative to the amount.</small>');
                            }),

                        Select::make('options.payments.currency_symbol_decimal')
                            ->label('Show Decimals')
                            ->live()
                            ->options([
                                '' => 'Always',
                                'when_needed' => 'When needed',
                            ]),

                    ]),

                Section::make('Other settings')
                    ->view('filament-forms::sections.section')
                    ->description('Configure additional shop settings.')
                    ->schema([

                        Select::make('options.website.shop_require_terms')
                            ->label('Require Terms and Conditions')
                            ->helperText(function () {
                                return new HtmlString('<small class="mb-2 text-muted">If enabled, users must agree to the terms and conditions before completing a purchase.</small>');
                            })
                            ->live()
                            ->options([
                                '' => 'Not required',
                                '1' => 'Required',
                            ]),


                        Actions::make([

                            Actions\Action::make('payment_provider_settings')
                                ->label('Payment Provider Settings')
                                ->url(PaymentProviderResource::getUrl('index'))
                                ->icon('heroicon-o-cog-6-tooth'),


                            Actions\Action::make('shipping_provider_settings')
                                ->label('Shipping Provider Settings')
                                ->url(ShippingProviderResource::getUrl('index'))
                                ->icon('heroicon-o-cog-6-tooth'),


                        ]),


                    ]),


            ]);
    }

}
