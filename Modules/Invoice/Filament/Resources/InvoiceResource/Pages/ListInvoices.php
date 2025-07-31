<?php

namespace Modules\Invoice\Filament\Resources\InvoiceResource\Pages;

use Modules\Invoice\Filament\Pages\AdminShopInvoicesPage;
use Modules\Invoice\Filament\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;



    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('AdminShopInvoicesPage')
                ->label('Invoices Settings')
                ->color('gray')
                ->icon('heroicon-o-cog')
                ->url(AdminShopInvoicesPage::getUrl()),
            Actions\CreateAction::make()
                ->label('Create Invoice')
                ->icon('heroicon-o-plus'),

        ];
    }
}
