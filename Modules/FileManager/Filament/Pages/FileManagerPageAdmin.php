<?php

namespace Modules\FileManager\Filament\Pages;

use MicroweberPackages\Admin\Filament\Pages\Abstract\AdminSettingsPage;

class FileManagerPageAdmin extends AdminSettingsPage
{
    protected static ?string $navigationIcon = 'mw-files';

    protected static string $view = 'modules.settings::filament.admin.pages.settings-filebrowser';

    protected static ?string $title = 'Files';

    protected static string $description = 'Configure your file settings';

    protected static ?string $navigationGroup = 'Other';


}
