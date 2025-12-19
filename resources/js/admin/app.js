import { createApp } from 'vue';
import ThemePage from './ThemePage.vue';
import BrandingPage from './BrandingPage.vue';

window.mountOFATheme = function (el) {
  new ThemePage().$mount(el);
};

window.mountOFABranding = function (el) {
  new BrandingPage().$mount(el);
};

const app = createApp(ThemePage);
app.mount('#ofa-theme-app');
