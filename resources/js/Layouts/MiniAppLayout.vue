<script setup>
import { onMounted, onUnmounted } from 'vue';

onMounted(() => {
    // Initialize Telegram WebApp
    if (window.Telegram?.WebApp) {
        const tg = window.Telegram.WebApp;
        tg.ready();
        tg.expand();
        
        // Set theme colors
        document.documentElement.style.setProperty('--tg-theme-bg-color', tg.themeParams.bg_color || '#ffffff');
        document.documentElement.style.setProperty('--tg-theme-text-color', tg.themeParams.text_color || '#000000');
        document.documentElement.style.setProperty('--tg-theme-hint-color', tg.themeParams.hint_color || '#999999');
        document.documentElement.style.setProperty('--tg-theme-button-color', tg.themeParams.button_color || '#3390ec');
        document.documentElement.style.setProperty('--tg-theme-button-text-color', tg.themeParams.button_text_color || '#ffffff');
    }
});
</script>

<template>
    <div class="miniapp-container">
        <slot />
    </div>
</template>

<style>
:root {
    --tg-theme-bg-color: #1a2a3a;
    --tg-theme-text-color: #ffffff;
    --tg-theme-hint-color: rgba(255, 255, 255, 0.6);
    --tg-theme-button-color: #FF6B4A;
    --tg-theme-button-text-color: #ffffff;
}

.miniapp-container {
    min-height: 100vh;
    background: linear-gradient(135deg, #1a2a3a 0%, #2d4a5e 50%, #1a2a3a 100%);
    color: var(--tg-theme-text-color);
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
</style>
