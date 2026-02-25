<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
    master: { type: Object, required: true },
    customer: { type: Object, required: true },
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('uz-UZ').format(price)
}

const toggleFavorite = () => {
    router.post(`/customer/favorites/${props.master.id}/toggle`, {}, {
        preserveScroll: true,
    })
}

const logout = () => {
    router.post('/customer/logout')
}
</script>

<template>
    <Head :title="master.full_name" />

    <div class="cms-page">
        <!-- Sidebar -->
        <aside class="cms-sidebar">
            <div class="cms-sidebar-top">
                <Link href="/" class="cms-sidebar-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m3 4.5a4.5 4.5 0 1 1-4.5-4.5M12 16.5V15m4.5-3a4.5 4.5 0 1 0-4.5-4.5M16.5 12H15"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <span>HOMEMASSAGE</span>
                </Link>

                <nav class="cms-nav">
                    <Link href="/customer/dashboard" class="cms-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                        <span>{{ t('customer.navDashboard') }}</span>
                    </Link>
                    <Link href="/customer/orders" class="cms-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>{{ t('customer.navBookings') }}</span>
                    </Link>
                    <Link href="/customer/masters" class="cms-nav-item active">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span>{{ t('customer.navMasters') }}</span>
                    </Link>
                    <Link href="/customer/ratings" class="cms-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <span>{{ t('customer.navRatings') }}</span>
                    </Link>
                    <Link href="/customer/favorites" class="cms-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                        <span>{{ t('customer.navFavorites') }}</span>
                    </Link>
                    <Link href="/customer/addresses" class="cms-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span>{{ t('customer.navAddresses') }}</span>
                    </Link>
                    <Link href="/customer/profile" class="cms-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>{{ t('customer.navProfile') }}</span>
                    </Link>
                </nav>
            </div>

            <div class="cms-sidebar-bottom">
                <div class="cms-sidebar-divider"></div>
                <div class="cms-user-profile">
                    <Link href="/customer/profile" class="cms-user-link">
                        <div class="cms-user-avatar">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <div class="cms-user-info">
                            <span class="cms-user-name">{{ customer.name }}</span>
                            <span class="cms-user-phone">{{ customer.phone }}</span>
                        </div>
                    </Link>
                    <button class="cms-logout-btn" @click="logout" :title="t('customer.logout')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Area -->
        <main class="cms-main">
            <!-- Top Bar with back -->
            <div class="cms-topbar">
                <Link href="/customer/masters" class="cms-back-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                    <span>{{ t('customer.navMasters') }}</span>
                </Link>
            </div>

            <!-- Content -->
            <div class="cms-content">
                <!-- Hero Section -->
                <div class="cms-hero">
                    <div class="cms-hero-photo">
                        <img :src="master.photo_url || '/images/master-placeholder.svg'" :alt="master.full_name" />
                    </div>
                    <div class="cms-hero-info">
                        <div class="cms-hero-top">
                            <div>
                                <h1 class="cms-name">{{ master.full_name }}</h1>
                                <p v-if="master.service_types?.length" class="cms-role">
                                    {{ master.service_types.map(st => st.name).join(' & ') }}
                                </p>
                            </div>
                            <button class="cms-fav-btn" :class="{ active: master.is_favorite }" @click="toggleFavorite">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" :fill="master.is_favorite ? '#C8A951' : 'none'" :stroke="master.is_favorite ? '#C8A951' : 'currentColor'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                            </button>
                        </div>
                        <p v-if="master.bio" class="cms-bio">{{ master.bio }}</p>
                        <div class="cms-stats">
                            <div v-if="master.experience_years" class="cms-stat-item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
                                <div>
                                    <span class="cms-stat-val">{{ master.experience_years }} {{ t('public.masters.years') }}</span>
                                    <span class="cms-stat-label">{{ t('customer.masters.experience') }}</span>
                                </div>
                            </div>
                            <div class="cms-stat-item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="#C8A951" stroke="#C8A951" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                <div>
                                    <span class="cms-stat-val">5.0</span>
                                    <span class="cms-stat-label">{{ t('common.rating') }}</span>
                                </div>
                            </div>
                            <div class="cms-stat-item">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                <div>
                                    <span class="cms-stat-val">{{ master.completed_orders || 0 }}</span>
                                    <span class="cms-stat-label">{{ t('customer.masters.completedSessions') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Services Section -->
                <div v-if="master.service_types?.length" class="cms-section">
                    <h2 class="cms-section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                        {{ t('customer.masters.services') }}
                    </h2>
                    <div class="cms-services-grid">
                        <div v-for="service in master.service_types" :key="service.id" class="cms-service-card">
                            <h3 class="cms-service-name">{{ service.name }}</h3>
                            <p v-if="service.description" class="cms-service-desc">{{ service.description }}</p>
                            <div v-if="service.durations?.length" class="cms-durations">
                                <div v-for="dur in service.durations" :key="dur.id" class="cms-duration-row">
                                    <div class="cms-dur-left">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        <span>{{ dur.formatted_duration }}</span>
                                    </div>
                                    <span class="cms-dur-price">{{ formatPrice(dur.price) }} {{ t('common.sum') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Oils Section -->
                <div v-if="master.oils?.length" class="cms-section">
                    <h2 class="cms-section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2h8l4 10H4L8 2Z"/><path d="M12 12v6"/><path d="M8 22v-2a4 4 0 0 1 8 0v2"/></svg>
                        {{ t('customer.masters.oils') }}
                    </h2>
                    <div class="cms-tags">
                        <span v-for="oil in master.oils" :key="oil.id" class="cms-tag">{{ oil.name }}</span>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

