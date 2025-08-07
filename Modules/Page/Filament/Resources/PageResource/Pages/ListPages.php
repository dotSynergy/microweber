<?php

namespace Modules\Page\Filament\Resources\PageResource\Pages;

use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Modules\Content\Models\Content;
use Modules\Page\Filament\Resources\PageResource;
use Modules\Page\Models\Page;

class ListPages extends \Modules\Content\Filament\Admin\ContentResource\Pages\ListContents
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        $actions = parent::getHeaderActions();

        // Check if there's no homepage set
        $hasHomepage = Content::where('content_type', 'page')
            ->where('is_home', 1)
            ->exists();

        $hasPages = Content::where('content_type', 'page')
            ->exists();

        if (!$hasHomepage) {
            $actions[] = Actions\Action::make('setup_homepage')
                ->visible($hasPages)
                ->label('Setup Homepage')
                ->icon('heroicon-o-home')
                ->color('warning')
                ->modalHeading('Select a page to be your homepage')
                ->modalDescription('Choose which page should be displayed as your website\'s homepage.')
                ->slideOver()
                ->form([
                    Forms\Components\Select::make('page_id')
                        ->label('Select Page')
                        ->placeholder('Choose a page to set as homepage')
                        ->options(function () {
                            return Page::where('content_type', 'page')
                                ->where('is_active', 1)
                                ->pluck('title', 'id')
                                ->toArray();
                        })
                        ->required()
                        ->searchable()
                        ->helperText('Select an active page to set as your homepage. Only published pages are shown.')
                ])
                ->action(function (array $data) {
                    // First, unset any existing homepage
                    Content::where('is_home', 1)->update(['is_home' => 0]);

                    // Set the selected page as homepage
                    $page = Content::find($data['page_id']);
                    if ($page) {
                        $page->update(['is_home' => 1]);

                        Notification::make()
                            ->title('Homepage Set Successfully')
                            ->body("'{$page->title}' has been set as your homepage.")
                            ->success()
                            ->send();
                    }
                });
        }

        return $actions;
    }
}
