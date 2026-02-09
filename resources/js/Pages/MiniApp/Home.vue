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
        <!-- Background circles -->
        <div class="bg-circles">
            <div class="circle c1"></div>
            <div class="circle c2"></div>
            <div class="circle c3"></div>
        </div>

        <!-- Header -->
        <header class="home-header">
            <div class="header-top">
                <div class="logo glass">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path d="M9 22V12h6v10"/>
                    </svg>
                </div>
                <button class="logout-btn glass" @click="logout">
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
                    v-for="(service, index) in services" 
                    :key="service.id"
                    class="service-card glass"
                    :style="{ animationDelay: `${index * 0.1}s` }"
                >
                    <div class="service-img">
                        <img v-if="service.image_url" :src="service.image_url" :alt="service.name" />
                        <div v-else class="service-img-placeholder">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
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
        <div class="book-container glass">
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
    background: linear-gradient(135deg, #1a2a3a 0%, #2d4a5e 50%, #1a2a3a 100%);
    position: relative;
    overflow-x: hidden;
}

/* Background circles */
.bg-circles {
    position: fixed;
    inset: 0;
    pointer-events: none;
    overflow: hidden;
}

.circle {
    position: absolute;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(184, 163, 105, 0.3), rgba(107, 139, 164, 0.2));
    filter: blur(60px);
    animation: float 8s ease-in-out infinite;
}

.c1 { width: 200px; height: 200px; top: -50px; right: -50px; }
.c2 { width: 150px; height: 150px; bottom: 200px; left: -40px; animation-delay: -2s; }
.c3 { width: 100px; height: 100px; top: 40%; right: 10%; animation-delay: -4s; }

@keyframes float {
    0%, 100% { transform: translateY(0) scale(1); opacity: 0.6; }
    50% { transform: translateY(-30px) scale(1.1); opacity: 0.8; }
}

/* Glass effect */
.glass {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.12);
}

/* Header */
.home-header {
    margin-bottom: 24px;
    position: relative;
    z-index: 1;
    animation: fadeInDown 0.5s ease;
}

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

.header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.logo {
    width: 52px;
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
    color: #B8A369;
    transition: all 0.3s ease;
}

.logo:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 20px rgba(184, 163, 105, 0.3);
}

.logout-btn {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    color: rgba(255, 255, 255, 0.6);
    cursor: pointer;
    transition: all 0.3s ease;
}

.logout-btn:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.15);
}

.home-title {
    font-size: 28px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 4px;
}

.home-greeting {
    font-size: 15px;
    color: rgba(255, 255, 255, 0.5);
    margin: 0;
}

/* Services */
.services-section {
    margin-bottom: 24px;
    position: relative;
    z-index: 1;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    color: #fff;
    margin: 0 0 16px;
}

.services-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.service-card {
    display: flex;
    gap: 14px;
    padding: 14px;
    border-radius: 20px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    animation: fadeInUp 0.5s ease both;
    position: relative;
    overflow: hidden;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.service-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(184, 163, 105, 0.1), transparent);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.service-card:hover::before {
    opacity: 1;
}

.service-card:hover {
    transform: translateX(5px);
    border-color: rgba(184, 163, 105, 0.3);
}

.service-img {
    width: 80px;
    height: 80px;
    border-radius: 16px;
    overflow: hidden;
    flex-shrink: 0;
    background: rgba(255, 255, 255, 0.1);
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
    color: rgba(255, 255, 255, 0.3);
}

.service-info {
    flex: 1;
    min-width: 0;
}

.service-name {
    font-size: 17px;
    font-weight: 600;
    color: #fff;
    margin: 0 0 4px;
}

.service-desc {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.5);
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
    z-index: 100;
    border-radius: 24px 24px 0 0;
}

.book-btn {
    width: 100%;
    padding: 18px;
    font-size: 16px;
    font-weight: 600;
    background: linear-gradient(135deg, #B8A369, #D4C89A);
    color: #1a2a3a;
    border: none;
    border-radius: 16px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 20px rgba(184, 163, 105, 0.4);
}

.book-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(184, 163, 105, 0.5);
}
</style>
