<script setup>
import { computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
    master: { type: Object, required: true },
    stats: { type: Object, default: () => ({ totalOrders: 0, completedOrders: 0, todaysOrders: 0, totalEarnings: 0 }) },
    ratingSummary: { type: Object, default: () => ({ average: null, count: 0 }) },
    recentRatings: { type: Array, default: () => [] },
    upcomingOrders: { type: Array, default: () => [] },
})

const currentDate = computed(() => {
    const d = new Date()
    const days = ['Yakshanba', 'Dushanba', 'Seshanba', 'Chorshanba', 'Payshanba', 'Juma', 'Shanba']
    const months = ['yanvar', 'fevral', 'mart', 'aprel', 'may', 'iyun', 'iyul', 'avgust', 'sentabr', 'oktabr', 'noyabr', 'dekabr']
    return `${days[d.getDay()]}, ${d.getDate()}-${months[d.getMonth()]}, ${d.getFullYear()}`
})

const formatPrice = (amount) => {
    return new Intl.NumberFormat('uz-UZ').format(amount)
}

const logout = () => {
    router.post('/master/logout')
}
</script>

<template>
    <Head :title="t('master.dashboard')" />

    <div class="md-page">
        <!-- Sidebar -->
        <aside class="md-sidebar">
            <div class="md-sidebar-top">
                <Link href="/" class="md-sidebar-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m3 4.5a4.5 4.5 0 1 1-4.5-4.5M12 16.5V15m4.5-3a4.5 4.5 0 1 0-4.5-4.5M16.5 12H15"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <span>HOMEMASSAGE</span>
                </Link>

                <nav class="md-nav">
                    <Link href="/master/dashboard" class="md-nav-item active">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                        <span>{{ t('master.navDashboard') }}</span>
                    </Link>
                    <Link href="/master/orders" class="md-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>{{ t('master.navOrders') }}</span>
                    </Link>
                    <Link href="/master/ratings" class="md-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <span>{{ t('master.navRatings') }}</span>
                    </Link>
                    <Link href="/master/profile" class="md-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>{{ t('master.navProfile') }}</span>
                    </Link>
                </nav>
            </div>

            <div class="md-sidebar-bottom">
                <div class="md-sidebar-divider"></div>
                <div class="md-user-profile">
                    <Link href="/master/dashboard" class="md-user-link">
                        <div class="md-user-avatar">
                            <img v-if="master.photo_url" :src="master.photo_url" :alt="master.name" />
                            <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <div class="md-user-info">
                            <span class="md-user-name">{{ master.name }}</span>
                            <span class="md-user-phone">{{ master.phone }}</span>
                        </div>
                    </Link>
                    <button class="md-logout-btn" @click="logout" :title="t('master.logout')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Area -->
        <main class="md-main">
            <!-- Top Bar -->
            <div class="md-topbar">
                <div class="md-topbar-left">
                    <h1 class="md-greeting">{{ t('master.welcome') }}, {{ master.name }}!</h1>
                    <p class="md-date">{{ currentDate }}</p>
                </div>
            </div>

            <!-- Content Area -->
            <div class="md-content">
                <!-- Left Column -->
                <div class="md-left-col">
                    <!-- Stats Row -->
                    <div class="md-stats-row">
                        <div class="md-stat-card">
                            <div class="md-stat-top">
                                <span class="md-stat-label">{{ t('master.totalOrders') }}</span>
                                <div class="md-stat-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                </div>
                            </div>
                            <span class="md-stat-val">{{ stats.totalOrders }}</span>
                        </div>
                        <div class="md-stat-card">
                            <div class="md-stat-top">
                                <span class="md-stat-label">{{ t('master.completedOrders') }}</span>
                                <div class="md-stat-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                </div>
                            </div>
                            <span class="md-stat-val">{{ stats.completedOrders }}</span>
                        </div>
                        <div class="md-stat-card">
                            <div class="md-stat-top">
                                <span class="md-stat-label">{{ t('master.todaysOrders') }}</span>
                                <div class="md-stat-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                </div>
                            </div>
                            <span class="md-stat-val">{{ stats.todaysOrders }}</span>
                        </div>
                        <div class="md-stat-card">
                            <div class="md-stat-top">
                                <span class="md-stat-label">{{ t('master.totalEarnings') }}</span>
                                <div class="md-stat-icon md-stat-icon-gold">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                </div>
                            </div>
                            <span class="md-stat-val">{{ formatPrice(stats.totalEarnings) }}</span>
                            <span class="md-stat-sub">UZS</span>
                        </div>
                    </div>

                    <!-- Upcoming Orders -->
                    <div class="md-card">
                        <div class="md-card-head">
                            <h2 class="md-card-title">{{ t('master.upcomingOrders') }}</h2>
                            <Link v-if="upcomingOrders.length > 0" href="/master/orders" class="md-card-link">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </Link>
                        </div>
                        <div v-if="upcomingOrders.length > 0" class="md-booking-list">
                            <Link v-for="order in upcomingOrders" :key="order.id" :href="`/master/orders/${order.id}`" class="md-booking-item">
                                <div class="md-booking-avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </div>
                                <div class="md-booking-info">
                                    <span class="md-booking-service">{{ order.service_name }}</span>
                                    <span class="md-booking-meta">{{ order.customer_name }} &middot; {{ order.booking_date }} &middot; {{ order.arrival_time }}</span>
                                </div>
                                <span class="md-booking-status" :class="`md-status-${order.status.toLowerCase()}`">{{ order.status }}</span>
                            </Link>
                        </div>
                        <div v-else class="md-empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <p>{{ t('master.noUpcomingOrders') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="md-right-col">
                    <!-- Rating Summary -->
                    <div class="md-rating-card">
                        <span class="md-rating-num">{{ ratingSummary.average ?? '-' }}</span>
                        <div class="md-rating-stars">
                            <svg v-for="s in 5" :key="s" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" :fill="s <= Math.round(ratingSummary.average || 0) ? '#fff' : 'rgba(255,255,255,0.3)'" :stroke="s <= Math.round(ratingSummary.average || 0) ? '#fff' : 'rgba(255,255,255,0.3)'" stroke-width="1"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        </div>
                        <span class="md-rating-count">{{ ratingSummary.count }} {{ t('master.reviews') }}</span>
                        <Link href="/master/ratings" class="md-rating-link">
                            <span>{{ t('master.viewAllRatings') }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </Link>
                    </div>

                    <!-- Recent Ratings -->
                    <div class="md-card">
                        <div class="md-card-head">
                            <h2 class="md-card-title md-card-title-sm">{{ t('master.recentRatings') }}</h2>
                            <Link v-if="recentRatings.length > 0" href="/master/ratings" class="md-card-link">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </Link>
                        </div>
                        <div v-if="recentRatings.length > 0" class="md-reviews-list">
                            <div v-for="rating in recentRatings" :key="rating.id" class="md-review-item">
                                <div class="md-review-top">
                                    <span class="md-review-name">{{ rating.customer_name }}</span>
                                    <span class="md-review-date">{{ rating.rated_at }}</span>
                                </div>
                                <div class="md-review-stars">
                                    <svg v-for="s in 5" :key="s" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" :fill="s <= rating.overall_rating ? '#C8A951' : '#e5e7eb'" :stroke="s <= rating.overall_rating ? '#C8A951' : '#e5e7eb'" stroke-width="1"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                </div>
                                <p v-if="rating.feedback" class="md-review-text">{{ rating.feedback }}</p>
                            </div>
                        </div>
                        <div v-else class="md-empty-state md-empty-sm">
                            <p>{{ t('master.noRatings') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
