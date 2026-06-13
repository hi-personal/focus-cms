import Alpine from 'alpinejs';
import Cookies from 'js-cookie';
import jQuery from 'jquery';
import 'boxicons';
import './md-editor.js';

window.Alpine = Alpine;
window.$ = window.jQuery = jQuery;
window.Cookies = Cookies;

Alpine.start();
