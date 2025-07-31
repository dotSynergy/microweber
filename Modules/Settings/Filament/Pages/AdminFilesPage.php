<?php

namespace Modules\Settings\Filament\Pages;

use Filament\Forms\Components\Card;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use MicroweberPackages\Admin\Filament\Pages\Abstract\AdminSettingsPage;
use MicroweberPackages\Filament\Forms\Components\MwFileUpload;

class AdminFilesPage extends AdminSettingsPage
{
    protected static ?string $navigationIcon = 'mw-files';

    protected static string $view = 'modules.settings::filament.admin.pages.settings-filebrowser';

    protected static ?string $title = 'Files';

    protected static string $description = 'Configure your file settings';

    protected static ?string $navigationGroup = 'Other';


}
