import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import type { WebApp, TelegramUser, ThemeParams, MainButton, BackButton } from '@/types/telegram';

// State
const isInitialized = ref(false);
const isTelegramEnvironment = ref(false);
const webApp = ref<WebApp | null>(null);
const user = ref<TelegramUser | null>(null);
const themeParams = ref<ThemeParams>({});
const colorScheme = ref<'light' | 'dark'>('light');
const viewportHeight = ref(0);
const isExpanded = ref(false);

/**
 * Telegram Mini App Composable
 * Provides reactive access to Telegram WebApp API
 */
export function useTelegramMiniApp() {
  /**
   * Initialize Telegram Mini App
   */
  const init = () => {
    if (isInitialized.value) return;

    const tg = window.Telegram?.WebApp;

    if (tg) {
      webApp.value = tg;
      isTelegramEnvironment.value = true;

      // Set initial values
      user.value = tg.initDataUnsafe?.user || null;
      themeParams.value = tg.themeParams || {};
      colorScheme.value = tg.colorScheme || 'light';
      viewportHeight.value = tg.viewportHeight || window.innerHeight;
      isExpanded.value = tg.isExpanded || false;

      // Listen for theme changes
      tg.onEvent('themeChanged', handleThemeChange);
      tg.onEvent('viewportChanged', handleViewportChange);

      // Tell Telegram we're ready
      tg.ready();

      isInitialized.value = true;
    } else {
      // Not in Telegram environment - use defaults for development
      isTelegramEnvironment.value = false;
      viewportHeight.value = window.innerHeight;
      isInitialized.value = true;
    }
  };

  /**
   * Handle theme changes from Telegram
   */
  const handleThemeChange = () => {
    if (!webApp.value) return;
    themeParams.value = webApp.value.themeParams || {};
    colorScheme.value = webApp.value.colorScheme || 'light';
  };

  /**
   * Handle viewport changes
   */
  const handleViewportChange = () => {
    if (!webApp.value) return;
    viewportHeight.value = webApp.value.viewportHeight || window.innerHeight;
    isExpanded.value = webApp.value.isExpanded || false;
  };

  /**
   * Expand the Mini App to full height
   */
  const expand = () => {
    webApp.value?.expand();
  };

  /**
   * Close the Mini App
   */
  const close = () => {
    webApp.value?.close();
  };

  /**
   * Send data to the bot
   */
  const sendData = (data: string | object) => {
    const dataStr = typeof data === 'string' ? data : JSON.stringify(data);
    webApp.value?.sendData(dataStr);
  };

  /**
   * Show a popup alert
   */
  const showAlert = (message: string): Promise<void> => {
    return new Promise((resolve) => {
      if (webApp.value) {
        webApp.value.showAlert(message, resolve);
      } else {
        alert(message);
        resolve();
      }
    });
  };

  /**
   * Show a confirmation dialog
   */
  const showConfirm = (message: string): Promise<boolean> => {
    return new Promise((resolve) => {
      if (webApp.value) {
        webApp.value.showConfirm(message, resolve);
      } else {
        resolve(confirm(message));
      }
    });
  };

  /**
   * Show a popup with buttons
   */
  const showPopup = (params: {
    title?: string;
    message: string;
    buttons?: Array<{
      id?: string;
      type?: 'default' | 'ok' | 'close' | 'cancel' | 'destructive';
      text?: string;
    }>;
  }): Promise<string | undefined> => {
    return new Promise((resolve) => {
      if (webApp.value) {
        webApp.value.showPopup(params, resolve);
      } else {
        alert(params.message);
        resolve('ok');
      }
    });
  };

  /**
   * Open a link in browser
   */
  const openLink = (url: string, tryInstantView = false) => {
    if (webApp.value) {
      webApp.value.openLink(url, { try_instant_view: tryInstantView });
    } else {
      window.open(url, '_blank');
    }
  };

  /**
   * Open a Telegram link (t.me)
   */
  const openTelegramLink = (url: string) => {
    if (webApp.value) {
      webApp.value.openTelegramLink(url);
    } else {
      window.open(url, '_blank');
    }
  };

  /**
   * Haptic feedback - impact
   */
  const hapticImpact = (style: 'light' | 'medium' | 'heavy' | 'rigid' | 'soft' = 'medium') => {
    webApp.value?.HapticFeedback?.impactOccurred(style);
  };

  /**
   * Haptic feedback - notification
   */
  const hapticNotification = (type: 'error' | 'success' | 'warning') => {
    webApp.value?.HapticFeedback?.notificationOccurred(type);
  };

  /**
   * Haptic feedback - selection
   */
  const hapticSelection = () => {
    webApp.value?.HapticFeedback?.selectionChanged();
  };

  /**
   * Set header color
   */
  const setHeaderColor = (color: string) => {
    webApp.value?.setHeaderColor(color);
  };

  /**
   * Set background color
   */
  const setBackgroundColor = (color: string) => {
    webApp.value?.setBackgroundColor(color);
  };

  /**
   * Enable closing confirmation
   */
  const enableClosingConfirmation = () => {
    webApp.value?.enableClosingConfirmation();
  };

  /**
   * Disable closing confirmation
   */
  const disableClosingConfirmation = () => {
    webApp.value?.disableClosingConfirmation();
  };

  // Computed values
  const initData = computed(() => webApp.value?.initData || '');
  const initDataUnsafe = computed(() => webApp.value?.initDataUnsafe || {});
  const platform = computed(() => webApp.value?.platform || 'unknown');
  const version = computed(() => webApp.value?.version || '0.0');

  // MainButton helpers
  const mainButton = computed(() => webApp.value?.MainButton);

  const setMainButton = (options: {
    text: string;
    color?: string;
    textColor?: string;
    isActive?: boolean;
    isVisible?: boolean;
  }) => {
    const btn = webApp.value?.MainButton;
    if (!btn) return;

    btn.setParams({
      text: options.text,
      color: options.color,
      text_color: options.textColor,
      is_active: options.isActive ?? true,
      is_visible: options.isVisible ?? true,
    });
  };

  const showMainButton = (text: string, onClick: () => void) => {
    const btn = webApp.value?.MainButton;
    if (!btn) return;

    btn.setText(text);
    btn.onClick(onClick);
    btn.show();
  };

  const hideMainButton = () => {
    webApp.value?.MainButton?.hide();
  };

  // BackButton helpers
  const backButton = computed(() => webApp.value?.BackButton);

  const showBackButton = (onClick: () => void) => {
    const btn = webApp.value?.BackButton;
    if (!btn) return;

    btn.onClick(onClick);
    btn.show();
  };

  const hideBackButton = () => {
    webApp.value?.BackButton?.hide();
  };

  // CSS variables for theme sync
  const cssVariables = computed(() => {
    const params = themeParams.value;
    return {
      '--tg-theme-bg-color': params.bg_color || '#ffffff',
      '--tg-theme-text-color': params.text_color || '#000000',
      '--tg-theme-hint-color': params.hint_color || '#999999',
      '--tg-theme-link-color': params.link_color || '#2481cc',
      '--tg-theme-button-color': params.button_color || '#2481cc',
      '--tg-theme-button-text-color': params.button_text_color || '#ffffff',
      '--tg-theme-secondary-bg-color': params.secondary_bg_color || '#f0f0f0',
      '--tg-theme-header-bg-color': params.header_bg_color || '#ffffff',
      '--tg-theme-accent-text-color': params.accent_text_color || '#2481cc',
      '--tg-theme-section-bg-color': params.section_bg_color || '#ffffff',
      '--tg-theme-section-header-text-color': params.section_header_text_color || '#6c757d',
      '--tg-theme-subtitle-text-color': params.subtitle_text_color || '#6c757d',
      '--tg-theme-destructive-text-color': params.destructive_text_color || '#dc3545',
      '--tg-viewport-height': `${viewportHeight.value}px`,
    };
  });

  // Apply CSS variables to document
  const applyCssVariables = () => {
    const vars = cssVariables.value;
    Object.entries(vars).forEach(([key, value]) => {
      document.documentElement.style.setProperty(key, value);
    });
  };

  // Cleanup
  const cleanup = () => {
    if (webApp.value) {
      webApp.value.offEvent('themeChanged', handleThemeChange);
      webApp.value.offEvent('viewportChanged', handleViewportChange);
    }
  };

  return {
    // State
    isInitialized,
    isTelegramEnvironment,
    webApp,
    user,
    themeParams,
    colorScheme,
    viewportHeight,
    isExpanded,

    // Computed
    initData,
    initDataUnsafe,
    platform,
    version,
    mainButton,
    backButton,
    cssVariables,

    // Methods
    init,
    expand,
    close,
    sendData,
    showAlert,
    showConfirm,
    showPopup,
    openLink,
    openTelegramLink,
    hapticImpact,
    hapticNotification,
    hapticSelection,
    setHeaderColor,
    setBackgroundColor,
    enableClosingConfirmation,
    disableClosingConfirmation,
    setMainButton,
    showMainButton,
    hideMainButton,
    showBackButton,
    hideBackButton,
    applyCssVariables,
    cleanup,
  };
}

/**
 * Plugin to auto-initialize TMA on app mount
 */
export const TelegramMiniAppPlugin = {
  install(app: any) {
    const tma = useTelegramMiniApp();

    // Initialize on app mount
    app.mixin({
      mounted() {
        if (this.$root === this) {
          tma.init();
          tma.applyCssVariables();
        }
      },
      unmounted() {
        if (this.$root === this) {
          tma.cleanup();
        }
      },
    });

    // Provide globally
    app.provide('telegramMiniApp', tma);
    app.config.globalProperties.$tma = tma;
  },
};

export default useTelegramMiniApp;
