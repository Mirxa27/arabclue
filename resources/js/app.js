import { createApp } from 'vue';
import MobileFooterNav from './components/MobileFooterNav.vue';

const app = createApp({});

app.component('mobile-footer-nav', MobileFooterNav);

app.mount('#mobile-nav');
