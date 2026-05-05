import './bootstrap';

import Alpine from 'alpinejs';
import mask from '@alpinejs/mask';
import focus from '@alpinejs/focus';

Alpine.plugin(focus);
Alpine.plugin(mask);



window.Alpine = Alpine;

Alpine.start();
