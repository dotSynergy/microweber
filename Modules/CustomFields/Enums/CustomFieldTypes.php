<?php

namespace Modules\CustomFields\Enums;

use Filament\Support\Contracts\HasLabel;
use JaOcero\RadioDeck\Contracts\HasDescriptions;
use JaOcero\RadioDeck\Contracts\HasIcons;

enum CustomFieldTypes: string implements HasLabel, HasDescriptions, HasIcons
{
    // Most commonly used fields
    case TEXT = 'text';
    case EMAIL = 'email';


    // Form interaction fields
    case DROPDOWN = 'dropdown';
    case RADIO = 'radio';
    case CHECKBOX = 'checkbox';


    // number fields
    case PHONE = 'phone';
    case NUMBER = 'number';

    // E-commerce related
    case PRICE = 'price';

    // Date and time
    case DATE = 'date';
    case TIME = 'time';

    // Location and address
    case ADDRESS = 'address';
    case COUNTRY = 'country';

    // Web and media
    case SITE = 'site';
    case UPLOAD = 'upload';
    case COLOR = 'color';



    // Utility fields
    case HIDDEN = 'hidden';
    case PROPERTY = 'property';
    case BREAKLINE = 'breakline';

//    case BUTTON = 'button';


    public function getLabel(): ?string
    {
        return match ($this) {
            // Most commonly used fields
            self::TEXT => 'Text Field',
            self::EMAIL => 'E-mail',
            self::PHONE => 'Phone',
            self::NUMBER => 'Number',

            // Form interaction fields
            self::DROPDOWN => 'Dropdown',
            self::RADIO => 'Single Choice',
            self::CHECKBOX => 'Multiple choices',

            // E-commerce related
            self::PRICE => 'Price',

            // Date and time
            self::DATE => 'Date',
            self::TIME => 'Time',

            // Location and address
            self::ADDRESS => 'Address',
            self::COUNTRY => 'Country',

            // Web and media
            self::SITE => 'Web Site',
            self::UPLOAD => 'File Upload',
            self::COLOR => 'Color',

            // Utility fields
            self::HIDDEN => 'Hidden Field',
            self::PROPERTY => 'Property',
            self::BREAKLINE => 'Break Line',
       //     self::BUTTON => 'Button',
        };
    }

    public function getDescriptions(): ?string
    {
        return match ($this) {
            // Most commonly used fields
            self::TEXT => 'Text field',
            self::EMAIL => 'E-mail field',
            self::PHONE => 'Phone field',
            self::NUMBER => 'Number field',

            // Form interaction fields
            self::DROPDOWN => 'Dropdown field',
            self::RADIO => 'Single choice field',
            self::CHECKBOX => 'Multiple choices field',

            // E-commerce related
            self::PRICE => 'Price field',

            // Date and time
            self::DATE => 'Date field',
            self::TIME => 'Time field',

            // Location and address
            self::ADDRESS => 'Address field',
            self::COUNTRY => 'Country field',

            // Web and media
            self::SITE => 'Web Site field',
            self::UPLOAD => 'File Upload field',
            self::COLOR => 'Color field',

            // Utility fields
            self::HIDDEN => 'Hidden field',
            self::PROPERTY => 'Property field',
            self::BREAKLINE => 'Break Line field',
//            self::BUTTON => 'Button field',
        };
    }

    public function getIcons(): ?string
    {
        return match ($this) {
            // Most commonly used fields
            self::TEXT => 'mw-text',
            self::EMAIL => 'heroicon-o-at-symbol',
            self::PHONE => 'heroicon-o-phone',
            self::NUMBER => 'mw-numbers',

            // Form interaction fields
            self::DROPDOWN => 'mw-dropdown',
            self::RADIO => 'mw-radio-checked',
            self::CHECKBOX => 'mw-checkbox',

            // E-commerce related
            self::PRICE => 'heroicon-o-currency-dollar',

            // Date and time
            self::DATE => 'heroicon-o-calendar-days',
            self::TIME => 'heroicon-o-clock',

            // Location and address
            self::ADDRESS => 'heroicon-o-map-pin',
            self::COUNTRY => 'heroicon-o-home',

            // Web and media
            self::SITE => 'heroicon-o-globe-europe-africa',
            self::UPLOAD => 'heroicon-o-arrow-up-tray',
            self::COLOR => 'heroicon-o-paint-brush',

            // Utility fields
            self::HIDDEN => 'mw-hidden',
            self::PROPERTY => 'mw-info',
            self::BREAKLINE => 'heroicon-o-pencil',
//            self::BUTTON => 'heroicon-o-pencil',

        };
    }
}
