<?php

namespace Modules\Ai\Filament\Resources\AgentChatResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Ai\Filament\Resources\AgentChatResource;

class CreateAgentChat extends CreateRecord
{
    protected static string $resource = AgentChatResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set user_id to current user if not set
        if (!isset($data['user_id'])) {
            $data['user_id'] = auth()->id();
        }

        return $data;
    }
}
