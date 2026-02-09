<script setup>
import { onMounted } from 'vue';

onMounted(() => {
    if (window.Telegram?.WebApp) {
        const tg = window.Telegram.WebApp;
        tg.ready();
        tg.expand();
        tg.setHeaderColor('#1a2a3a');
        tg.setBackgroundColor('#1a2a3a');
    }
    
    document.documentElement.style.background = '#1a2a3a';
    document.body.style.background = '#1a2a3a';
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
html, body, #app {
    background: #1a2a3a;
}
</style>
