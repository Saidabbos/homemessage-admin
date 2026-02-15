<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
    ratings: { type: Object, required: true },
    summary: { type: Object, default: () => ({ average: null, count: 0 }) },
    master: { type: Object, required: true },
})

const goToPage = (url) => {
    if (url) router.get(url, {}, { preserveState: true, preserveScroll: true })
}

const logout = () => {
    router.post('/master/logout')
}
</script>

<template>
    <Head :title="t('master.ratings.title')" />

    <div class="mrt-page">
        <!-- Sidebar -->
        <aside class="mrt-sidebar">
            <div class="mrt-sidebar-top">
                <Link href="/" class="mrt-sidebar-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m3 4.5a4.5 4.5 0 1 1-4.5-4.5M12 16.5V15m4.5-3a4.5 4.5 0 1 0-4.5-4.5M16.5 12H15"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <span>HOMEMASSAGE</span>
                </Link>

                <nav class="mrt-nav">
                    <Link href="/master/dashboard" class="mrt-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                        <span>{{ t('master.navDashboard') }}</span>
                    </Link>
                    <Link href="/master/orders" class="mrt-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>{{ t('master.navOrders') }}</span>
                    </Link>
                    <Link href="/master/ratings" class="mrt-nav-item active">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <span>{{ t('master.navRatings') }}</span>
                    </Link>
                    <Link href="/master/profile" class="mrt-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>{{ t('master.navProfile') }}</span>
                    </Link>
                </nav>
            </div>

            <div class="mrt-sidebar-bottom">
                <div class="mrt-sidebar-divider"></div>
                <div class="mrt-user-profile">
                    <Link href="/master/dashboard" class="mrt-user-link">
                        <div class="mrt-user-avatar">
                            <img v-if="master.photo_url" :src="master.photo_url" :alt="master.name" />
                            <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <div class="mrt-user-info">
                            <span class="mrt-user-name">{{ master.name }}</span>
                            <span class="mrt-user-phone">{{ master.phone }}</span>
                        </div>
                    </Link>
                    <button class="mrt-logout-btn" @click="logout" :title="t('master.logout')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Area -->
        <main class="mrt-main">
            <div class="mrt-topbar">
                <div>
                    <h1 class="mrt-page-title">{{ t('master.ratings.title') }}</h1>
                    <p class="mrt-page-subtitle">{{ t('master.ratings.subtitle') }}</p>
                </div>
            </div>

            <div class="mrt-content">
                <!-- Summary Card -->
                <div class="mrt-summary">
                    <div class="mrt-summary-rating">
                        <span class="mrt-summary-num">{{ summary.average ?? '-' }}</span>
                        <div class="mrt-summary-stars">
                            <svg v-for="s in 5" :key="s" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" :fill="s <= Math.round(summary.average || 0) ? '#C8A951' : '#e5e7eb'" :stroke="s <= Math.round(summary.average || 0) ? '#C8A951' : '#e5e7eb'" stroke-width="1"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        </div>
                        <span class="mrt-summary-count">{{ summary.count }} {{ t('master.ratings.totalReviews') }}</span>
                    </div>
                </div>

                <!-- Ratings List -->
                <div v-if="ratings.data.length > 0" class="mrt-list">
                    <div v-for="rating in ratings.data" :key="rating.id" class="mrt-card">
                        <div class="mrt-card-top">
                            <div class="mrt-card-left">
                                <div class="mrt-avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </div>
                                <div>
                                    <span class="mrt-name">{{ rating.customer_name }}</span>
                                    <span class="mrt-meta">{{ rating.service_name }} &middot; #{{ rating.order_number }}</span>
                                </div>
                            </div>
                            <span class="mrt-date">{{ rating.rated_at }}</span>
                        </div>

                        <div class="mrt-stars-row">
                            <div class="mrt-stars">
                                <svg v-for="s in 5" :key="s" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" :fill="s <= rating.overall_rating ? '#C8A951' : '#e5e7eb'" :stroke="s <= rating.overall_rating ? '#C8A951' : '#e5e7eb'" stroke-width="1"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            </div>
                            <div v-if="rating.punctuality_rating" class="mrt-sub-ratings">
                                <span>{{ t('master.ratings.punctuality') }}: {{ rating.punctuality_rating }}/5</span>
                                <span>{{ t('master.ratings.professionalism') }}: {{ rating.professionalism_rating }}/5</span>
                                <span>{{ t('master.ratings.cleanliness') }}: {{ rating.cleanliness_rating }}/5</span>
                            </div>
                        </div>

                        <p v-if="rating.feedback" class="mrt-feedback">{{ rating.feedback }}</p>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="mrt-empty">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <p>{{ t('master.ratings.noRatings') }}</p>
                </div>

                <!-- Pagination -->
                <div v-if="ratings.data.length && ratings.last_page > 1" class="mrt-pagination">
                    <button class="mrt-pag-btn" :disabled="!ratings.prev_page_url" @click="goToPage(ratings.prev_page_url)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                    </button>
                    <span class="mrt-pag-info">{{ ratings.current_page }} / {{ ratings.last_page }}</span>
                    <button class="mrt-pag-btn" :disabled="!ratings.next_page_url" @click="goToPage(ratings.next_page_url)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                </div>
            </div>
        </main>
    </div>
</template>
