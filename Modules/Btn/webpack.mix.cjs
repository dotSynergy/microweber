let mix = require('laravel-mix');
let path = require('path');
let fs = require('fs-extra');




mix.copy('./resources/assets/js/quick-settings.js', '../../public/modules/btn/js/quick-settings.js');
