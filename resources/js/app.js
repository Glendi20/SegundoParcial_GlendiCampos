import './bootstrap';
import Alpine from 'alpinejs';
import { registerAppCitas } from './calendario';

registerAppCitas(Alpine);

window.Alpine = Alpine;
Alpine.start();
