<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
    ratings: { type: Object, required: true },
    tab: { type: String, default: 'received' },
    summary: { type: Object, default: () => ({ average: null, count: 0 }) },
    counts: { type: Object, default: () => ({ received: 0, given: 0 }) },
    customer: { type: Object, required: true },
})

const activeTab = ref(props.tab)

const switchTab = (tab) => {
    activeTab.value = tab
    router.get('/customer/ratings', { tab }, { preserveState: true, preserveScroll: true })
}

const goToPage = (url) => {
    if (url) router.get(url, {}, { preserveState: true, preserveScroll: true })
}

const logout = () => {
    router.post('/customer/logout')
}
</script>

<template>
    <Head :title="t('customer.ratings.title')" />

    <div class="cr-page">
        <!-- Sidebar -->
        <aside class="cr-sidebar">
            <div class="cr-sidebar-top">
                <Link href="/" class="cr-sidebar-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m3 4.5a4.5 4.5 0 1 1-4.5-4.5M12 16.5V15m4.5-3a4.5 4.5 0 1 0-4.5-4.5M16.5 12H15"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <span>HOMEMASSAGE</span>
                </Link>

                <nav class="cr-nav">
                    <Link href="/customer/dashboard" class="cr-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                        <span>{{ t('customer.navDashboard') }}</span>
                    </Link>
                    <Link href="/customer/orders" class="cr-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>{{ t('customer.navBookings') }}</span>
                    </Link>
                    <Link href="/masters" class="cr-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span>{{ t('customer.navMasters') }}</span>
                    </Link>
                    <Link href="/customer/ratings" class="cr-nav-item active">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <span>{{ t('customer.navRatings') }}</span>
                    </Link>
                    <Link href="/customer/favorites" class="cr-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                        <span>{{ t('customer.navFavorites') }}</span>
                    </Link>
                    <Link href="/customer/profile" class="cr-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>{{ t('customer.navProfile') }}</span>
                    </Link>
                </nav>
            </div>

            <div class="cr-sidebar-bottom">
                <div class="cr-sidebar-divider"></div>
                <div class="cr-user-profile">
                    <Link href="/customer/profile" class="cr-user-link">
                        <div class="cr-user-avatar">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <div class="cr-user-info">
                            <span class="cr-user-name">{{ customer.name }}</span>
                            <span class="cr-user-phone">{{ customer.phone }}</span>
                        </div>
                    </Link>
                    <button class="cr-logout-btn" @click="logout" :title="t('customer.logout')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Area -->
        <main class="cr-main">
            <div class="cr-topbar">
                <div>
                    <h1 class="cr-page-title">{{ t('customer.ratings.title') }}</h1>
                    <p class="cr-page-subtitle">{{ t('customer.ratings.subtitle') }}</p>
                </div>
            </div>

            <div class="cr-content">
                <!-- Summary Card -->
                <div class="cr-summary">
                    <div class="cr-summary-rating">
                        <span class="cr-summary-num">{{ summary.average ?? '-' }}</span>
                        <div class="cr-summary-stars">
                            <svg v-for="s in 5" :key="s" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" :fill="s <= Math.round(summary.average || 0) ? '#C8A951' : '#e5e7eb'" :stroke="s <= Math.round(summary.average || 0) ? '#C8A951' : '#e5e7eb'" stroke-width="1"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        </div>
                        <span class="cr-summary-count">{{ summary.count }} {{ t('customer.ratings.totalReviews').toLowerCase() }}</span>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="cr-tabs">
                    <button class="cr-tab" :class="{ active: activeTab === 'received' }" @click="switchTab('received')">
                        {{ t('customer.ratings.tabReceived') }} ({{ counts.received }})
                    </button>
                    <button class="cr-tab" :class="{ active: activeTab === 'given' }" @click="switchTab('given')">
                        {{ t('customer.ratings.tabGiven') }} ({{ counts.given }})
                    </button>
                </div>

                <!-- Ratings List -->
                <div v-if="ratings.data.length > 0" class="cr-list">
                    <div v-for="rating in ratings.data" :key="rating.id" class="cr-card">
                        <div class="cr-card-top">
                            <div class="cr-card-left">
                                <div class="cr-avatar">
                                    <img v-if="rating.master_photo" :src="rating.master_photo" :alt="rating.master_name" />
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </div>
                                <div>
                                    <span class="cr-name">{{ rating.master_name }}</span>
                                    <span class="cr-meta">{{ rating.service_name }} &middot; #{{ rating.order_number }}</span>
                                </div>
                            </div>
                            <span class="cr-date">{{ rating.rated_at }}</span>
                        </div>

                        <div class="cr-stars-row">
                            <div class="cr-stars">
                                <svg v-for="s in 5" :key="s" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" :fill="s <= rating.overall_rating ? '#C8A951' : '#e5e7eb'" :stroke="s <= rating.overall_rating ? '#C8A951' : '#e5e7eb'" stroke-width="1"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            </div>
                            <div v-if="rating.punctuality_rating" class="cr-sub-ratings">
                                <span>{{ t('customer.ratings.punctuality') }}: {{ rating.punctuality_rating }}/5</span>
                                <span>{{ t('customer.ratings.professionalism') }}: {{ rating.professionalism_rating }}/5</span>
                                <span>{{ t('customer.ratings.cleanliness') }}: {{ rating.cleanliness_rating }}/5</span>
                            </div>
                        </div>

                        <p v-if="rating.feedback" class="cr-feedback">{{ rating.feedback }}</p>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="cr-empty">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <p>{{ activeTab === 'received' ? t('customer.ratings.noRatingsReceived') : t('customer.ratings.noRatingsGiven') }}</p>
                </div>

                <!-- Pagination -->
                <div v-if="ratings.data.length && ratings.last_page > 1" class="cr-pagination">
                    <button class="cr-pag-btn" :disabled="!ratings.prev_page_url" @click="goToPage(ratings.prev_page_url)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                    </button>
                    <span class="cr-pag-info">{{ ratings.current_page }} / {{ ratings.last_page }}</span>
                    <button class="cr-pag-btn" :disabled="!ratings.next_page_url" @click="goToPage(ratings.next_page_url)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                </div>
            </div>
        </main>
    </div>
</template>
