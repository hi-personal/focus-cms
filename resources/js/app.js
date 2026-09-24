import Alpine from 'alpinejs';
import Cookies from 'js-cookie';
import jQuery from 'jquery';

import './md-editor.js';
import './uppy.js';

window.Alpine = Alpine;
window.$ = window.jQuery = jQuery;
window.Cookies = Cookies;

Alpine.start();

console.log('✅ FocusCMS Admin loaded!');