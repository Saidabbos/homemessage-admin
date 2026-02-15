<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
    favorites: { type: Array, default: () => [] },
    serviceTypes: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    customer: { type: Object, required: true },
})

const activeFilter = ref(props.filters.service_type ? Number(props.filters.service_type) : null)

const selectFilter = (id) => {
    activeFilter.value = id
    const params = {}
    if (id) params.service_type = id
    router.get('/customer/favorites', params, { preserveState: true, preserveScroll: true })
}

const removeFavorite = (masterId) => {
    router.post(`/customer/favorites/${masterId}/toggle`, {}, {
        preserveScroll: true,
        onSuccess: () => {},
    })
}

const formatPrice = (price) => {
    if (!price) return '-'
    return new Intl.NumberFormat('uz-UZ').format(price)
}

const logout = () => {
    router.post('/customer/logout')
}
</script>

<template>
    <Head :title="t('customer.favorites.title')" />

    <div class="cf-page">
        <!-- Sidebar -->
        <aside class="cf-sidebar">
            <div class="cf-sidebar-top">
                <Link href="/" class="cf-sidebar-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m3 4.5a4.5 4.5 0 1 1-4.5-4.5M12 16.5V15m4.5-3a4.5 4.5 0 1 0-4.5-4.5M16.5 12H15"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <span>HOMEMASSAGE</span>
                </Link>

                <nav class="cf-nav">
                    <Link href="/customer/dashboard" class="cf-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                        <span>{{ t('customer.navDashboard') }}</span>
                    </Link>
                    <Link href="/customer/orders" class="cf-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>{{ t('customer.navBookings') }}</span>
                    </Link>
                    <Link href="/masters" class="cf-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span>{{ t('customer.navMasters') }}</span>
                    </Link>
                    <span class="cf-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/></svg>
                        <span>{{ t('customer.navHistory') }}</span>
                    </span>
                    <Link href="/customer/favorites" class="cf-nav-item active">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                        <span>{{ t('customer.navFavorites') }}</span>
                    </Link>
                    <span class="cf-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                        <span>{{ t('customer.navSettings') }}</span>
                    </span>
                </nav>
            </div>

            <div class="cf-sidebar-bottom">
                <div class="cf-sidebar-divider"></div>
                <div class="cf-user-profile">
                    <div class="cf-user-avatar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div class="cf-user-info">
                        <span class="cf-user-name">{{ customer.name }}</span>
                        <span class="cf-user-phone">{{ customer.phone }}</span>
                    </div>
                    <button class="cf-logout-btn" @click="logout" :title="t('customer.logout')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Area -->
        <main class="cf-main">
            <!-- Top Bar -->
            <div class="cf-topbar">
                <div class="cf-topbar-left">
                    <h1 class="cf-page-title">{{ t('customer.favorites.title') }}</h1>
                    <p class="cf-page-subtitle">{{ t('customer.favorites.subtitle') }}</p>
                </div>
            </div>

            <!-- Content Area -->
            <div class="cf-content">
                <!-- Top Section: count + filters -->
                <div class="cf-top-section">
                    <div class="cf-fav-count">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="#C8A951" stroke="#C8A951" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                        <span>{{ favorites.length }} {{ t('customer.favorites.count') }}</span>
                    </div>
                    <div class="cf-filters">
                        <button
                            class="cf-filter-btn"
                            :class="{ active: !activeFilter }"
                            @click="selectFilter(null)"
                        >
                            {{ t('customer.orders.tabAll') }}
                        </button>
                        <button
                            v-for="st in serviceTypes"
                            :key="st.id"
                            class="cf-filter-btn"
                            :class="{ active: activeFilter === st.id }"
                            @click="selectFilter(st.id)"
                        >
                            {{ st.name }}
                        </button>
                    </div>
                </div>

                <!-- Masters Grid -->
                <div v-if="favorites.length" class="cf-grid">
                    <div v-for="master in favorites" :key="master.id" class="cf-card">
                        <div class="cf-card-img">
                            <img
                                :src="master.photo_url || '/images/master-placeholder.svg'"
                                :alt="master.full_name"
                            />
                        </div>
                        <div class="cf-card-body">
                            <div class="cf-card-top">
                                <div class="cf-card-name-block">
                                    <h3 class="cf-card-name">{{ master.full_name }}</h3>
                                    <p class="cf-card-spec">
                                        {{ master.service_types.map(st => st.name).join(', ') }}
                                    </p>
                                </div>
                                <button class="cf-heart-btn" @click="removeFavorite(master.id)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="#C8A951" stroke="#C8A951" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                                </button>
                            </div>
                            <div class="cf-card-rating">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="#C8A951" stroke="#C8A951" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                <span class="cf-rating-text">5.0 ({{ master.completed_orders }} {{ t('customer.favorites.reviews') }})</span>
                                <span v-if="master.min_price" class="cf-price-text">{{ formatPrice(master.min_price) }} {{ t('customer.favorites.perHour') }}</span>
                            </div>
                            <Link :href="`/booking?master=${master.id}`" class="cf-book-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                <span>{{ t('customer.favorites.book') }}</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="cf-empty">
                    <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                    <p>{{ t('customer.favorites.empty') }}</p>
                    <Link href="/masters" class="cf-empty-cta">{{ t('customer.browseMasters') }}</Link>
                </div>
            </div>
        </main>
    </div>
</template>
