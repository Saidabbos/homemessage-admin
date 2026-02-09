<script setup>
import { router } from '@inertiajs/vue3';
import MiniAppLayout from '@/Layouts/MiniAppLayout.vue';

defineOptions({ layout: MiniAppLayout });
</script>

<template>
    <div class="success-page">
        <div class="bg-gradient"></div>
        
        <div class="success-content">
            <div class="success-icon">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="16,8 10,14 8,12"/>
                </svg>
            </div>
            
            <h1>Buyurtma qabul qilindi!</h1>
            <p>Tez orada master siz bilan bog'lanadi.</p>
            
            <button class="home-btn" @click="router.visit('/app')">
                Bosh sahifaga
            </button>
        </div>
    </div>
</template>

<style scoped>
.success-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    background: linear-gradient(135deg, #1a2a3a 0%, #2d4a5e 50%, #1a2a3a 100%);
}

.bg-gradient {
    position: fixed;
    inset: 0;
    background: linear-gradient(135deg, #1a2a3a 0%, #2d4a5e 50%, #1a2a3a 100%);
    z-index: -1;
}

.success-content {
    text-align: center;
}

.success-icon {
    width: 120px;
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
    background: rgba(76, 175, 80, 0.2);
    border-radius: 50%;
    color: #4CAF50;
}

h1 {
    font-size: 24px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 12px;
}

p {
    font-size: 16px;
    color: rgba(255, 255, 255, 0.7);
    margin: 0 0 32px;
}

.home-btn {
    padding: 16px 48px;
    background: linear-gradient(135deg, #FF6B4A, #FF8F6B);
    border: none;
    border-radius: 16px;
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
}
</style>
