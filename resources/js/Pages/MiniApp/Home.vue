<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import MiniAppLayout from '@/Layouts/MiniAppLayout.vue';

defineOptions({ layout: MiniAppLayout });

const props = defineProps({
    services: Array,
    user: Object,
});

const tg = ref(null);
const telegramUser = ref(null);

onMounted(() => {
    if (window.Telegram?.WebApp) {
        tg.value = window.Telegram.WebApp;
        telegramUser.value = tg.value.initDataUnsafe?.user;
    }
});

const greeting = computed(() => {
    if (telegramUser.value?.first_name) {
        return `Salom, ${telegramUser.value.first_name}!`;
    }
    return 'Salom!';
});

const logout = () => {
    router.post('/app/logout');
};
</script>

<template>
    <div class="miniapp-home">
        <!-- Background -->
        <div class="bg-gradient"></div>
        <div class="bg-circles">
            <div class="circle circle-1"></div>
            <div class="circle circle-2"></div>
        </div>

        <!-- Header -->
        <header class="app-header">
            <div class="header-top">
                <div class="logo">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path d="M9 22V12h6v10"/>
                    </svg>
                </div>
                <button class="logout-btn" @click="logout">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                        <polyline points="16,17 21,12 16,7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                </button>
            </div>
            <h1 class="app-title">Home Massage</h1>
            <p class="app-subtitle">{{ greeting }}</p>
        </header>

        <!-- Services -->
        <section class="services-section">
            <h2 class="section-title">Xizmatlar</h2>
            <div class="services-grid">
                <div 
                    v-for="service in services" 
                    :key="service.id"
                    class="service-card"
                >
                    <div class="service-image" v-if="service.image_url">
                        <img :src="service.image_url" :alt="service.name" />
                    </div>
                    <div class="service-info">
                        <h3 class="service-name">{{ service.name }}</h3>
                        <p class="service-description">{{ service.description }}</p>
                        <div class="service-durations" v-if="service.durations?.length">
                            <span 
                                v-for="dur in service.durations" 
                                :key="dur.id"
                                class="duration-chip"
                            >
                                {{ dur.duration }} min - {{ (dur.price / 1000).toFixed(0) }}K
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Book Button -->
        <div class="book-button-container">
            <button class="book-button" @click="$inertia.visit('/app/book')">
                Buyurtma berish
            </button>
        </div>
    </div>
</template>

<style scoped>
.miniapp-home {
    min-height: 100vh;
    padding: 16px;
    padding-bottom: 100px;
    background: linear-gradient(135deg, #1a2a3a 0%, #2d4a5e 50%, #1a2a3a 100%);
    position: relative;
}

.bg-gradient {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #1a2a3a 0%, #2d4a5e 50%, #1a2a3a 100%);
    z-index: 0;
}

.bg-circles {
    position: absolute;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
    z-index: 0;
}

.circle {
    position: absolute;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(107, 139, 164, 0.3), rgba(255, 107, 74, 0.2));
    filter: blur(40px);
}

.circle-1 {
    width: 150px;
    height: 150px;
    top: -30px;
    right: -30px;
}

.circle-2 {
    width: 100px;
    height: 100px;
    bottom: 150px;
    left: -20px;
}

.app-header {
    position: relative;
    z-index: 1;
    text-align: center;
    margin-bottom: 24px;
}

.header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.logo {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    color: #FF6B4A;
}

.logout-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: none;
    border-radius: 10px;
    color: rgba(255, 255, 255, 0.7);
    cursor: pointer;
}

.app-title {
    font-size: 24px;
    font-weight: 700;
    margin: 0 0 4px 0;
    color: #ffffff;
}

.app-subtitle {
    font-size: 15px;
    color: rgba(255, 255, 255, 0.6);
    margin: 0;
}

.services-section {
    position: relative;
    z-index: 1;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    margin: 0 0 16px 0;
    color: #ffffff;
}

.services-grid {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.service-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 16px;
    overflow: hidden;
    display: flex;
    gap: 12px;
}

.service-image {
    width: 90px;
    height: 90px;
    flex-shrink: 0;
}

.service-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.service-info {
    padding: 12px 12px 12px 0;
    flex: 1;
}

.service-name {
    font-size: 16px;
    font-weight: 600;
    margin: 0 0 4px 0;
    color: #ffffff;
}

.service-description {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.5);
    margin: 0 0 8px 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.service-durations {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.duration-chip {
    font-size: 11px;
    padding: 4px 10px;
    background: linear-gradient(135deg, #FF6B4A, #FF8F6B);
    color: #ffffff;
    border-radius: 12px;
}

.book-button-container {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 16px;
    background: rgba(26, 42, 58, 0.95);
    backdrop-filter: blur(20px);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    z-index: 10;
}

.book-button {
    width: 100%;
    padding: 16px;
    font-size: 16px;
    font-weight: 600;
    background: linear-gradient(135deg, #FF6B4A, #FF8F6B);
    color: #ffffff;
    border: none;
    border-radius: 14px;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(255, 107, 74, 0.3);
}
</style>
