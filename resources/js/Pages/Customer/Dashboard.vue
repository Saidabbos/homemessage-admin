<script setup>
import { computed } from 'vue'
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const page = usePage()

const props = defineProps({
    customer: { type: Object, required: true },
    stats: { type: Object, default: () => ({ totalSessions: 0, upcoming: 0, totalSpent: 0 }) },
    ratingSummary: { type: Object, default: () => ({ average: null, count: 0 }) },
    recentRatings: { type: Array, default: () => [] },
    upcomingBookings: { type: Array, default: () => [] },
    completedOrders: { type: Array, default: () => [] },
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

const goToRate = (orderId) => {
    router.post(`/customer/orders/${orderId}/rate`)
}

const logout = () => {
    router.post('/customer/logout')
}
</script>

<template>
    <Head :title="t('customer.dashboard')" />

    <div class="cd-page">
        <!-- Sidebar -->
        <aside class="cd-sidebar">
            <div class="cd-sidebar-top">
                <Link href="/" class="cd-sidebar-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m3 4.5a4.5 4.5 0 1 1-4.5-4.5M12 16.5V15m4.5-3a4.5 4.5 0 1 0-4.5-4.5M16.5 12H15"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <span>HOMEMASSAGE</span>
                </Link>

                <nav class="cd-nav">
                    <Link href="/customer/dashboard" class="cd-nav-item active">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                        <span>{{ t('customer.navDashboard') }}</span>
                    </Link>
                    <Link href="/customer/orders" class="cd-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>{{ t('customer.navBookings') }}</span>
                    </Link>
                    <Link href="/masters" class="cd-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span>{{ t('customer.navMasters') }}</span>
                    </Link>
                    <Link href="/customer/ratings" class="cd-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <span>{{ t('customer.navRatings') }}</span>
                    </Link>
                    <Link href="/customer/favorites" class="cd-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                        <span>{{ t('customer.navFavorites') }}</span>
                    </Link>
                    <Link href="/customer/profile" class="cd-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>{{ t('customer.navProfile') }}</span>
                    </Link>
                </nav>
            </div>

            <div class="cd-sidebar-bottom">
                <div class="cd-sidebar-divider"></div>
                <div class="cd-user-profile">
                    <Link href="/customer/profile" class="cd-user-link">
                        <div class="cd-user-avatar">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <div class="cd-user-info">
                            <span class="cd-user-name">{{ customer.name }}</span>
                            <span class="cd-user-phone">{{ customer.phone }}</span>
                        </div>
                    </Link>
                    <button class="cd-logout-btn" @click="logout" :title="t('customer.logout')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Area -->
        <main class="cd-main">
            <!-- Top Bar -->
            <div class="cd-topbar">
                <div class="cd-topbar-left">
                    <h1 class="cd-greeting">{{ t('customer.welcome') }}, {{ customer.name }}!</h1>
                    <p class="cd-date">{{ currentDate }}</p>
                </div>
            </div>

            <!-- Content Area -->
            <div class="cd-content">
                <!-- Left Column -->
                <div class="cd-left-col">
                    <!-- Stats Row -->
                    <div class="cd-stats-row">
                        <div class="cd-stat-card">
                            <div class="cd-stat-top">
                                <span class="cd-stat-label">{{ t('customer.totalSessions') }}</span>
                                <div class="cd-stat-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                </div>
                            </div>
                            <span class="cd-stat-val">{{ stats.totalSessions }}</span>
                        </div>
                        <div class="cd-stat-card">
                            <div class="cd-stat-top">
                                <span class="cd-stat-label">{{ t('customer.upcoming') }}</span>
                                <div class="cd-stat-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                </div>
                            </div>
                            <span class="cd-stat-val">{{ stats.upcoming }}</span>
                        </div>
                        <div class="cd-stat-card">
                            <div class="cd-stat-top">
                                <span class="cd-stat-label">{{ t('customer.totalSpent') }}</span>
                                <div class="cd-stat-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                </div>
                            </div>
                            <span class="cd-stat-val">{{ formatPrice(stats.totalSpent) }}</span>
                        </div>
                        <div class="cd-stat-card">
                            <div class="cd-stat-top">
                                <span class="cd-stat-label">{{ t('customer.myRating') }}</span>
                                <div class="cd-stat-icon cd-stat-icon-gold">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                </div>
                            </div>
                            <span class="cd-stat-val">{{ ratingSummary.average ?? '-' }}</span>
                            <span v-if="ratingSummary.count" class="cd-stat-sub">{{ ratingSummary.count }} {{ t('customer.favorites.reviews') }}</span>
                        </div>
                    </div>

                    <!-- Upcoming Bookings -->
                    <div class="cd-card">
                        <div class="cd-card-head">
                            <h2 class="cd-card-title">{{ t('customer.upcomingSessions') }}</h2>
                        </div>
                        <div v-if="upcomingBookings.length > 0" class="cd-booking-list">
                            <Link v-for="booking in upcomingBookings" :key="booking.id" :href="`/customer/orders/${booking.id}`" class="cd-booking-item">
                                <div class="cd-booking-avatar">
                                    <img v-if="booking.master_photo" :src="booking.master_photo" :alt="booking.master_name" />
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </div>
                                <div class="cd-booking-info">
                                    <span class="cd-booking-service">{{ booking.service_name }}</span>
                                    <span class="cd-booking-meta">{{ booking.master_name }} &middot; {{ booking.booking_date }} &middot; {{ booking.arrival_time }}</span>
                                </div>
                                <span class="cd-booking-status" :class="`cd-status-${booking.status.toLowerCase()}`">{{ booking.status }}</span>
                            </Link>
                        </div>
                        <div v-else class="cd-empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <p>{{ t('customer.noUpcoming') }}</p>
                            <Link href="/booking" class="cd-empty-cta">{{ t('customer.bookFirst') }}</Link>
                        </div>
                    </div>

                    <!-- Completed Orders -->
                    <div class="cd-card cd-card-grow">
                        <div class="cd-card-head">
                            <h2 class="cd-card-title">{{ t('customer.recentHistory') }}</h2>
                            <Link v-if="completedOrders.length > 0" href="/customer/orders?status=COMPLETED" class="cd-card-link">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </Link>
                        </div>
                        <div v-if="completedOrders.length > 0" class="cd-booking-list">
                            <div v-for="order in completedOrders" :key="order.id" class="cd-booking-item">
                                <Link :href="`/customer/orders/${order.id}`" class="cd-booking-link">
                                    <div class="cd-booking-avatar">
                                        <img v-if="order.master_photo" :src="order.master_photo" :alt="order.master_name" />
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    </div>
                                    <div class="cd-booking-info">
                                        <span class="cd-booking-service">{{ order.service_name }}</span>
                                        <span class="cd-booking-meta">{{ order.master_name }} &middot; {{ order.booking_date }}</span>
                                    </div>
                                </Link>
                                <button v-if="order.can_rate" class="cd-rate-btn" @click="goToRate(order.id)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                    {{ t('customer.rateSession') }}
                                </button>
                                <div v-else-if="order.rating" class="cd-rated-stars">
                                    <svg v-for="s in 5" :key="s" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" :fill="s <= order.rating ? '#C8A951' : '#e5e7eb'" :stroke="s <= order.rating ? '#C8A951' : '#e5e7eb'" stroke-width="1"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                </div>
                                <Link v-else :href="`/customer/orders/${order.id}`" class="cd-view-btn">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                                </Link>
                            </div>
                        </div>
                        <div v-else class="cd-empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/></svg>
                            <p>{{ t('customer.noHistory') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="cd-right-col">
                    <!-- Quick Book -->
                    <div class="cd-quick-book">
                        <div class="cd-qb-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        </div>
                        <h3 class="cd-qb-title">{{ t('customer.newBooking') }}</h3>
                        <p class="cd-qb-desc">{{ t('customer.newBookingDesc') }}</p>
                        <Link href="/booking" class="cd-qb-btn">
                            <span>{{ t('landing.nav.bookNow') }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </Link>
                    </div>

                    <!-- Recent Reviews -->
                    <div class="cd-card">
                        <div class="cd-card-head">
                            <h2 class="cd-card-title cd-card-title-sm">{{ t('customer.recentReviews') }}</h2>
                            <Link v-if="recentRatings.length > 0" href="/customer/ratings" class="cd-card-link">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </Link>
                        </div>
                        <div v-if="recentRatings.length > 0" class="cd-reviews-list">
                            <div v-for="rating in recentRatings" :key="rating.id" class="cd-review-item">
                                <div class="cd-review-top">
                                    <span class="cd-review-name">{{ rating.master_name }}</span>
                                    <span class="cd-review-date">{{ rating.rated_at }}</span>
                                </div>
                                <div class="cd-review-stars">
                                    <svg v-for="s in 5" :key="s" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" :fill="s <= rating.overall_rating ? '#C8A951' : '#e5e7eb'" :stroke="s <= rating.overall_rating ? '#C8A951' : '#e5e7eb'" stroke-width="1"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                </div>
                                <p v-if="rating.feedback" class="cd-review-text">{{ rating.feedback }}</p>
                            </div>
                        </div>
                        <div v-else class="cd-empty-state cd-empty-sm">
                            <p>{{ t('customer.noReviews') }}</p>
                            <Link href="/customer/ratings" class="cd-empty-cta">{{ t('customer.navRatings') }}</Link>
                        </div>
                    </div>

                    <!-- Favorite Masters -->
                    <div class="cd-card cd-card-grow">
                        <div class="cd-card-head">
                            <h2 class="cd-card-title cd-card-title-sm">{{ t('customer.favoriteMasters') }}</h2>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="cd-heart-icon"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                        </div>
                        <div class="cd-empty-state cd-empty-sm">
                            <p>{{ t('customer.noFavorites') }}</p>
                            <Link href="/masters" class="cd-empty-cta">{{ t('customer.browseMasters') }}</Link>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
