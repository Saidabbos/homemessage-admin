<script setup>
import { onMounted, onUnmounted, watch } from 'vue';
import { useTelegramMiniApp } from '@/composables/useTelegramMiniApp';

const {
  init,
  cleanup,
  applyCssVariables,
  isTelegramEnvironment,
  colorScheme,
  themeParams,
  viewportHeight,
  expand,
} = useTelegramMiniApp();

onMounted(() => {
  init();
  applyCssVariables();

  // Auto-expand in Telegram
  if (isTelegramEnvironment.value) {
    expand();
  }
});

onUnmounted(() => {
  cleanup();
});

// Watch for theme changes
watch([colorScheme, themeParams], () => {
  applyCssVariables();
});
</script>

<template>
  <div
    class="tma-layout"
    :class="{
      'tma-dark': colorScheme === 'dark',
      'tma-light': colorScheme === 'light',
      'tma-telegram': isTelegramEnvironment,
    }"
    :style="{ minHeight: viewportHeight + 'px' }"
  >
    <slot />
  </div>
</template>

<style>
/* Telegram Theme CSS Variables */
:root {
  --tg-theme-bg-color: #ffffff;
  --tg-theme-text-color: #000000;
  --tg-theme-hint-color: #999999;
  --tg-theme-link-color: #2481cc;
  --tg-theme-button-color: #2481cc;
  --tg-theme-button-text-color: #ffffff;
  --tg-theme-secondary-bg-color: #f0f0f0;
  --tg-theme-header-bg-color: #ffffff;
  --tg-theme-accent-text-color: #2481cc;
  --tg-theme-section-bg-color: #ffffff;
  --tg-theme-section-header-text-color: #6c757d;
  --tg-theme-subtitle-text-color: #6c757d;
  --tg-theme-destructive-text-color: #dc3545;
  --tg-viewport-height: 100vh;
}

.tma-layout {
  background-color: var(--tg-theme-bg-color);
  color: var(--tg-theme-text-color);
  min-height: 100vh;
  min-height: var(--tg-viewport-height);
  transition: background-color 0.2s, color 0.2s;
}

.tma-dark {
  color-scheme: dark;
}

.tma-light {
  color-scheme: light;
}

/* Utility classes for TMA theming */
.tma-bg-primary {
  background-color: var(--tg-theme-bg-color);
}

.tma-bg-secondary {
  background-color: var(--tg-theme-secondary-bg-color);
}

.tma-text-primary {
  color: var(--tg-theme-text-color);
}

.tma-text-hint {
  color: var(--tg-theme-hint-color);
}

.tma-text-link {
  color: var(--tg-theme-link-color);
}

.tma-text-accent {
  color: var(--tg-theme-accent-text-color);
}

.tma-text-destructive {
  color: var(--tg-theme-destructive-text-color);
}

.tma-button {
  background-color: var(--tg-theme-button-color);
  color: var(--tg-theme-button-text-color);
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: opacity 0.2s;
}

.tma-button:active {
  opacity: 0.8;
}

.tma-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.tma-card {
  background-color: var(--tg-theme-section-bg-color);
  border-radius: 12px;
  padding: 16px;
}

.tma-section-header {
  color: var(--tg-theme-section-header-text-color);
  font-size: 13px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 8px;
  padding-left: 16px;
}

.tma-subtitle {
  color: var(--tg-theme-subtitle-text-color);
  font-size: 14px;
}
</style>
