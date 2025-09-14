<?php

namespace Modules\Ai\Filament\Resources\AgentChatResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Ai\Filament\Resources\AgentChatResource;

class EditAgentChat extends EditRecord
{
    protected static string $resource = AgentChatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('View Chat'),
            Actions\DeleteAction::make(),
        ];
    }
}
