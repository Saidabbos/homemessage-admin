<script setup>
import { onMounted } from 'vue';

onMounted(() => {
    // Initialize Telegram WebApp
    if (window.Telegram?.WebApp) {
        const tg = window.Telegram.WebApp;
        tg.ready();
        tg.expand();
        
        // Light theme for new design
        tg.setHeaderColor('#FFFFFF');
        tg.setBackgroundColor('#F8F6F3');
    }
    
    // Set light backgrounds
    document.documentElement.style.background = '#F8F6F3';
    document.body.style.background = '#F8F6F3';
    document.body.style.backgroundColor = '#F8F6F3';
    
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
    background: #F8F6F3;
    color: #333333;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
</style>

<style>
/* Global styles for light theme */
html, body, #app {
    background: #F8F6F3;
    background-color: #F8F6F3;
}
</style>
