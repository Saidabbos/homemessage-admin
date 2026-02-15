<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const { t } = useI18n()

const props = defineProps({
    serviceTypes: {
        type: Array,
        default: () => []
    }
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('uz-UZ').format(price)
}

// Service icons mapping
const getServiceIcon = (id) => {
    const icons = {
        1: '💆', // Klassik
        2: '🧘', // Relaks
        3: '💪', // Sport
        4: '🙏', // Tailand
        5: '🪨', // Issiq tosh
        6: '🔙', // Orqa va bo'yin
        7: '🦶', // Oyoq
        8: '✨', // Anti-sellyulit
    }
    return icons[id] || '💆'
}
</script>

<template>
    <PublicLayout>
        <Head :title="t('public.services.title')" />
        
        <div class="services-page">
            <!-- Header -->
            <header class="page-header">
                <div class="header-content">
                    <Link href="/" class="back-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m15 18-6-6 6-6"/>
                        </svg>
                    </Link>
                    <h1 class="page-title">{{ t('public.services.title') }}</h1>
                    <div class="header-spacer"></div>
                </div>
            </header>

            <!-- Services List -->
            <main class="services-content">
                <div v-if="serviceTypes.length === 0" class="empty-state">
                    <div class="empty-icon">💆</div>
                    <p>{{ t('public.services.empty') }}</p>
                </div>

                <div v-else class="services-list">
                    <Link
                        v-for="service in serviceTypes"
                        :key="service.id"
                        :href="`/services/${service.slug}`"
                        class="service-card"
                    >
                        <div class="service-icon">
                            {{ getServiceIcon(service.id) }}
                        </div>
                        
                        <div class="service-info">
                            <h2 class="service-name">{{ service.name }}</h2>
                            
                            <p v-if="service.description" class="service-description">
                                {{ service.description }}
                            </p>
                            
                            <div class="service-meta">
                                <div class="meta-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/>
                                        <polyline points="12,6 12,12 16,14"/>
                                    </svg>
                                    <span>{{ service.duration_list }}</span>
                                </div>
                            </div>

                            <!-- Durations & Prices -->
                            <div v-if="service.durations && service.durations.length" class="durations-list">
                                <div 
                                    v-for="duration in service.durations.slice(0, 3)" 
                                    :key="duration.id"
                                    class="duration-badge"
                                >
                                    <span class="duration-time">{{ duration.duration }} min</span>
                                    <span class="duration-price">{{ formatPrice(duration.price) }}</span>
                                </div>
                                <div v-if="service.durations.length > 3" class="duration-badge more">
                                    +{{ service.durations.length - 3 }}
                                </div>
                            </div>
                        </div>

                        <div class="card-arrow">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m9 18 6-6-6-6"/>
                            </svg>
                        </div>
                    </Link>
                </div>

                <!-- CTA Section -->
                <div class="cta-section">
                    <h3>{{ t('public.services.cta_title') }}</h3>
                    <p>{{ t('public.services.cta_text') }}</p>
                    <Link href="/booking" class="cta-button">
                        {{ t('common.book_now') }}
                    </Link>
                </div>
            </main>
        </div>
    </PublicLayout>
</template>

<style scoped>
.services-page {
    min-height: 100vh;
    background: var(--cream, #faf8f5);
}

.page-header {
    position: sticky;
    top: 0;
    z-index: 50;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.page-header .header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.5rem;
    max-width: 1200px;
    margin: 0 auto;
}

.back-button {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: rgba(0, 0, 0, 0.05);
    color: #1a1a2e;
    transition: all 0.2s ease;
}

.back-button:hover {
    background: rgba(0, 0, 0, 0.1);
}

.page-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.25rem;
    font-weight: 500;
    color: #1a1a2e;
}

.header-spacer {
    width: 40px;
}

/* Services Content */
.services-content {
    padding: 1.5rem;
    max-width: 1200px;
    margin: 0 auto;
}

.services-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.service-card {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.25rem;
    background: white;
    border-radius: 16px;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.service-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}

.service-icon {
    flex-shrink: 0;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    background: linear-gradient(135deg, rgba(201, 165, 92, 0.1) 0%, rgba(212, 183, 106, 0.1) 100%);
    border-radius: 16px;
}

.service-info {
    flex: 1;
    min-width: 0;
}

.service-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.125rem;
    font-weight: 500;
    color: #1a1a2e;
    margin-bottom: 0.375rem;
}

.service-description {
    font-size: 0.875rem;
    color: #6a6a7a;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: 0.75rem;
}

.service-meta {
    display: flex;
    gap: 1rem;
    margin-bottom: 0.75rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.8125rem;
    color: #6a6a7a;
}

.meta-item svg {
    color: #C9A55C;
}

.durations-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.duration-badge {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 0.5rem 0.75rem;
    background: rgba(201, 165, 92, 0.08);
    border-radius: 10px;
    min-width: 80px;
}

.duration-time {
    font-size: 0.75rem;
    font-weight: 500;
    color: #6a6a7a;
}

.duration-price {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #C9A55C;
}

.duration-badge.more {
    background: rgba(0, 0, 0, 0.05);
    color: #6a6a7a;
    font-size: 0.8125rem;
    font-weight: 500;
    justify-content: center;
}

.card-arrow {
    flex-shrink: 0;
    color: #C9A55C;
    margin-top: 1rem;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6a6a7a;
}

.empty-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

/* CTA Section */
.cta-section {
    margin-top: 2rem;
    padding: 2rem;
    background: linear-gradient(135deg, #1a1a2e 0%, #2d2d4a 100%);
    border-radius: 20px;
    text-align: center;
    color: white;
}

.cta-section h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    font-weight: 500;
    margin-bottom: 0.75rem;
}

.cta-section p {
    font-size: 0.9375rem;
    opacity: 0.8;
    margin-bottom: 1.5rem;
}

.cta-button {
    display: inline-block;
    padding: 0.875rem 2rem;
    background: linear-gradient(135deg, #C9A55C 0%, #D4B76A 100%);
    color: white;
    font-weight: 600;
    border-radius: 50px;
    text-decoration: none;
    transition: all 0.2s ease;
}

.cta-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(201, 165, 92, 0.3);
}

/* Responsive */
@media (min-width: 768px) {
    .services-list {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .services-list {
        grid-template-columns: repeat(3, 1fr);
    }
}
</style>
