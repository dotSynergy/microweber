let mix = require('laravel-mix');
let path = require('path');
let fs = require('fs-extra');




mix.copy('./resources/assets/js/sortableMenu.js', '../../public/modules/menu/js/sortableMenu.js');
mix.copy('./resources/assets/js/menu-quick-settings.js', '../../public/modules/menu/js/menu-quick-settings.js');
