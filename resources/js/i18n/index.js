import { createI18n } from 'vue-i18n';
import uz from './locales/uz.json';
import ru from './locales/ru.json';
import en from './locales/en.json';

const i18n = createI18n({
  legacy: false,
  locale: localStorage.getItem('locale') || 'uz',
  fallbackLocale: 'uz',
  messages: {
    uz,
    ru,
    en,
  },
});

export default i18n;
