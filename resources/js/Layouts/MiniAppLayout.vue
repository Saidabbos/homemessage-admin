<script setup>
import { onMounted } from 'vue';

onMounted(() => {
    // Initialize Telegram WebApp
    if (window.Telegram?.WebApp) {
        const tg = window.Telegram.WebApp;
        tg.ready();
        tg.expand();
        
        // Force dark theme
        tg.setHeaderColor('#1a2a3a');
        tg.setBackgroundColor('#1a2a3a');
    }
    
    // Force all backgrounds to dark
    document.documentElement.style.background = '#1a2a3a';
    document.body.style.background = '#1a2a3a';
    document.body.style.backgroundColor = '#1a2a3a';
    
    // Remove any white backgrounds from Telegram wrappers
    const root = document.getElementById('app');
    if (root) {
        root.style.background = 'transparent';
    }
});
</script>

<template>
    <div class="miniapp-container">
        <slot />
    </div>
</template>

<style scoped>
.miniapp-container {
    min-height: 100vh;
    background: linear-gradient(135deg, #1a2a3a 0%, #2d4a5e 50%, #1a2a3a 100%);
    color: #ffffff;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
</style>

<style>
/* Global styles to override Telegram WebApp backgrounds */
html, body, #app {
    background: #1a2a3a;
    background-color: #1a2a3a;
}
</style>
