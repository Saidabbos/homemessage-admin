<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
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
</script>

<template>
    <div class="miniapp-home">
        <!-- Header -->
        <header class="app-header">
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
                    <div class="service-content">
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
    padding: 16px;
    padding-bottom: 100px;
}

.app-header {
    text-align: center;
    margin-bottom: 24px;
}

.app-title {
    font-size: 24px;
    font-weight: 700;
    margin: 0 0 4px 0;
}

.app-subtitle {
    font-size: 16px;
    color: var(--tg-theme-hint-color);
    margin: 0;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    margin: 0 0 16px 0;
}

.services-grid {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.service-card {
    background: var(--tg-theme-bg-color);
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    gap: 12px;
}

.service-image {
    width: 80px;
    height: 80px;
    flex-shrink: 0;
}

.service-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.service-content {
    padding: 12px 12px 12px 0;
    flex: 1;
}

.service-name {
    font-size: 16px;
    font-weight: 600;
    margin: 0 0 4px 0;
}

.service-description {
    font-size: 13px;
    color: var(--tg-theme-hint-color);
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
    padding: 4px 8px;
    background: var(--tg-theme-button-color);
    color: var(--tg-theme-button-text-color);
    border-radius: 12px;
}

.book-button-container {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 16px;
    background: var(--tg-theme-bg-color);
    border-top: 1px solid rgba(0, 0, 0, 0.1);
}

.book-button {
    width: 100%;
    padding: 14px;
    font-size: 16px;
    font-weight: 600;
    background: var(--tg-theme-button-color);
    color: var(--tg-theme-button-text-color);
    border: none;
    border-radius: 12px;
    cursor: pointer;
}
</style>
