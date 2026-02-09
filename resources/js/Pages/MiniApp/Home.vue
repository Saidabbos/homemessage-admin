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

const formatPrice = (price) => new Intl.NumberFormat('uz-UZ').format(price);

const logout = () => {
    router.post('/app/logout');
};
</script>

<template>
    <div class="home-page">
        <!-- Header -->
        <header class="home-header">
            <div class="header-top">
                <div class="logo">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
            <h1 class="home-title">Home Massage</h1>
            <p class="home-greeting">{{ greeting }}</p>
        </header>

        <!-- Services Section -->
        <section class="services-section">
            <h2 class="section-title">Xizmatlar</h2>
            <div class="services-list">
                <div 
                    v-for="service in services" 
                    :key="service.id"
                    class="service-card"
                >
                    <div class="service-img">
                        <img v-if="service.image_url" :src="service.image_url" :alt="service.name" />
                        <div v-else class="service-img-placeholder">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="service-info">
                        <h3 class="service-name">{{ service.name }}</h3>
                        <p class="service-desc">{{ service.description }}</p>
                        <span class="service-price">
                            {{ formatPrice(service.durations?.[0]?.price || 0) }} - {{ formatPrice(service.durations?.[service.durations.length - 1]?.price || 0) }} UZS
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Book Button -->
        <div class="book-container">
            <button class="book-btn" @click="$inertia.visit('/app/book')">
                Buyurtma berish
            </button>
        </div>
    </div>
</template>

<style scoped>
.home-page {
    min-height: 100vh;
    padding: 16px;
    padding-bottom: 100px;
    background: #F8F6F3;
}

/* Header */
.home-header {
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
    background: #B8A369;
    border-radius: 12px;
    color: #fff;
}

.logout-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    border: 1px solid #E5E5E5;
    border-radius: 10px;
    color: #888;
    cursor: pointer;
}

.home-title {
    font-size: 24px;
    font-weight: 700;
    color: #333;
    margin: 0 0 4px;
}

.home-greeting {
    font-size: 15px;
    color: #888;
    margin: 0;
}

/* Services */
.services-section {
    margin-bottom: 24px;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin: 0 0 16px;
}

.services-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.service-card {
    display: flex;
    gap: 12px;
    padding: 12px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.service-img {
    width: 80px;
    height: 80px;
    border-radius: 12px;
    overflow: hidden;
    flex-shrink: 0;
    background: #F5F5F5;
}

.service-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.service-img-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #CCC;
}

.service-info {
    flex: 1;
    min-width: 0;
}

.service-name {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin: 0 0 4px;
}

.service-desc {
    font-size: 13px;
    color: #888;
    margin: 0 0 8px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.service-price {
    font-size: 14px;
    font-weight: 600;
    color: #B8A369;
}

/* Book Button */
.book-container {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 16px;
    background: #fff;
    border-top: 1px solid #E5E5E5;
    z-index: 10;
}

.book-btn {
    width: 100%;
    padding: 16px;
    font-size: 16px;
    font-weight: 600;
    background: #B8A369;
    color: #fff;
    border: none;
    border-radius: 12px;
    cursor: pointer;
}
</style>
