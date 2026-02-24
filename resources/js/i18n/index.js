import { createI18n } from 'vue-i18n';
import uz from './locales/uz.json';
import ru from './locales/ru.json';
import en from './locales/en.json';

// Detect user's preferred language from browser/device
const getDefaultLocale = () => {
  // First check localStorage
  const saved = localStorage.getItem('locale');
  if (saved && ['uz', 'ru', 'en'].includes(saved)) {
    return saved;
  }
  
  // Check Telegram WebApp language
  if (window.Telegram?.WebApp?.initDataUnsafe?.user?.language_code) {
    const tgLang = window.Telegram.WebApp.initDataUnsafe.user.language_code;
    if (tgLang === 'uz') return 'uz';
    if (tgLang === 'ru') return 'ru';
    if (tgLang.startsWith('en')) return 'en';
  }
  
  // Check browser language
  const browserLang = navigator.language || navigator.userLanguage || '';
  const lang = browserLang.split('-')[0].toLowerCase();
  
  if (lang === 'uz') return 'uz';
  if (lang === 'ru') return 'ru';
  if (lang === 'en') return 'en';
  
  // Default to Uzbek for Uzbekistan users
  return 'uz';
};

const i18n = createI18n({
  legacy: false,
  locale: getDefaultLocale(),
  fallbackLocale: 'uz',
  messages: {
    uz,
    ru,
    en,
  },
});

export default i18n;
