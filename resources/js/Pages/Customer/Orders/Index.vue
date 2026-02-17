<script setup>
import { computed, ref, watch } from 'vue'
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const page = usePage()

const props = defineProps({
    orders: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statusCounts: { type: Object, default: () => ({}) },
    customer: { type: Object, required: true },
})

const activeTab = ref(props.filters.status || null)
const searchQuery = ref(props.filters.search || '')
let searchTimeout = null

const statusTabs = computed(() => [
    { key: null, label: t('customer.orders.tabAll') },
    { key: 'NEW', label: t('customer.orders.tabPending') },
    { key: 'CONFIRMED', label: t('customer.orders.tabConfirmed') },
    { key: 'COMPLETED', label: t('customer.orders.tabCompleted') },
    { key: 'CANCELLED', label: t('customer.orders.tabCancelled') },
])

const totalOrders = computed(() => {
    return Object.values(props.statusCounts).reduce((sum, c) => sum + c, 0)
})

const tabCount = (key) => {
    if (!key) return totalOrders.value
    if (key === 'NEW') {
        return (props.statusCounts['NEW'] || 0) +
               (props.statusCounts['CONFIRMING'] || 0)
    }
    if (key === 'CONFIRMED') {
        return (props.statusCounts['CONFIRMED'] || 0) +
               (props.statusCounts['IN_PROGRESS'] || 0)
    }
    return props.statusCounts[key] || 0
}

const applyFilters = () => {
    const params = {}
    if (activeTab.value) params.status = activeTab.value
    if (searchQuery.value.trim()) params.search = searchQuery.value.trim()
    router.get('/customer/orders', params, { preserveState: true, preserveScroll: true })
}

const selectTab = (key) => {
    activeTab.value = key
    applyFilters()
}

watch(searchQuery, () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => applyFilters(), 400)
})

const goToPage = (url) => {
    if (url) router.get(url, {}, { preserveState: true, preserveScroll: true })
}

const formatPrice = (price) => {
    return new Intl.NumberFormat('uz-UZ').format(price)
}

const statusConfig = {
    NEW: { color: '#1B2B5A', bg: '#1B2B5A10' },
    CONFIRMING: { color: '#1B2B5A', bg: '#1B2B5A10' },
    CONFIRMED: { color: '#C8A951', bg: '#C8A95120' },
    IN_PROGRESS: { color: '#3B82F6', bg: '#3B82F615' },
    COMPLETED: { color: '#22C55E', bg: '#22C55E15' },
    CANCELLED: { color: '#EF4444', bg: '#EF444415' },
}

const statusLabel = (status) => {
    const map = {
        NEW: t('customer.orders.statusNew'),
        CONFIRMING: t('customer.orders.statusConfirming'),
        CONFIRMED: t('customer.orders.statusConfirmed'),
        IN_PROGRESS: t('customer.orders.statusInProgress'),
        COMPLETED: t('customer.orders.statusCompleted'),
        CANCELLED: t('customer.orders.statusCancelled'),
    }
    return map[status] || status
}

const userInitial = computed(() => {
    return props.customer.name ? props.customer.name.charAt(0).toUpperCase() : '?'
})

const logout = () => {
    router.post('/customer/logout')
}

// Pagination info text
const paginationInfo = computed(() => {
    const { from, to, total } = props.orders
    if (!total) return ''
    return `${from}-${to} / ${total} ${t('customer.orders.ordersCount')}`
})
</script>

<template>
    <Head :title="t('customer.orders.title')" />

    <div class="co-page">
        <!-- Sidebar -->
        <aside class="co-sidebar">
            <div class="co-sidebar-top">
                <Link href="/" class="co-sidebar-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m3 4.5a4.5 4.5 0 1 1-4.5-4.5M12 16.5V15m4.5-3a4.5 4.5 0 1 0-4.5-4.5M16.5 12H15"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <span>HOMEMASSAGE</span>
                </Link>

                <nav class="co-nav">
                    <Link href="/customer/dashboard" class="co-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                        <span>{{ t('customer.navDashboard') }}</span>
                    </Link>
                    <Link href="/customer/orders" class="co-nav-item active">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>{{ t('customer.navBookings') }}</span>
                    </Link>
                    <Link href="/masters" class="co-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span>{{ t('customer.navMasters') }}</span>
                    </Link>
                    <Link href="/customer/ratings" class="co-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <span>{{ t('customer.navRatings') }}</span>
                    </Link>
                    <Link href="/customer/favorites" class="co-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                        <span>{{ t('customer.navFavorites') }}</span>
                    </Link>
                    <Link href="/customer/profile" class="co-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>{{ t('customer.navProfile') }}</span>
                    </Link>
                </nav>
            </div>

            <div class="co-sidebar-bottom">
                <div class="co-sidebar-divider"></div>
                <div class="co-user-profile">
                    <Link href="/customer/profile" class="co-user-link">
                        <div class="co-user-avatar">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <div class="co-user-info">
                            <span class="co-user-name">{{ customer.name }}</span>
                            <span class="co-user-phone">{{ customer.phone }}</span>
                        </div>
                    </Link>
                    <button class="co-logout-btn" @click="logout" :title="t('customer.logout')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Area -->
        <main class="co-main">
            <!-- Top Bar -->
            <div class="co-topbar">
                <div class="co-topbar-left">
                    <h1 class="co-page-title">{{ t('customer.orders.title') }}</h1>
                    <p class="co-page-subtitle">{{ t('customer.orders.subtitle') }}</p>
                </div>
            </div>

            <!-- Content Area -->
            <div class="co-content">
                <!-- Filter Bar -->
                <div class="co-filter-bar">
                    <div class="co-tabs">
                        <button
                            v-for="tab in statusTabs"
                            :key="tab.key"
                            class="co-tab"
                            :class="{ active: activeTab === tab.key }"
                            @click="selectTab(tab.key)"
                        >
                            {{ tab.label }}
                        </button>
                    </div>
                    <div class="co-filter-right">
                        <div class="co-search-box">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            <input
                                v-model="searchQuery"
                                type="text"
                                :placeholder="t('customer.orders.searchPlaceholder')"
                            />
                        </div>
                        <Link href="/booking" class="co-new-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            <span>{{ t('customer.orders.newOrder') }}</span>
                        </Link>
                    </div>
                </div>

                <!-- Orders Table Card -->
                <div class="co-table-card">
                    <!-- Table Header -->
                    <div class="co-table-header">
                        <div class="co-th co-col-id">{{ t('customer.orders.colId') }}</div>
                        <div class="co-th co-col-date">{{ t('customer.orders.colDate') }}</div>
                        <div class="co-th co-col-service">{{ t('customer.orders.colService') }}</div>
                        <div class="co-th co-col-master">{{ t('customer.orders.colMaster') }}</div>
                        <div class="co-th co-col-time">{{ t('customer.orders.colTime') }}</div>
                        <div class="co-th co-col-price">{{ t('customer.orders.colPrice') }}</div>
                        <div class="co-th co-col-status">{{ t('customer.orders.colStatus') }}</div>
                    </div>

                    <!-- Table Rows -->
                    <Link
                        v-for="order in orders.data"
                        :key="order.id"
                        :href="`/customer/orders/${order.id}`"
                        class="co-table-row"
                    >
                        <div class="co-td co-col-id">
                            <span class="co-order-id">#{{ order.order_number }}</span>
                        </div>
                        <div class="co-td co-col-date">{{ order.booking_date_formatted }}</div>
                        <div class="co-td co-col-service">
                            <span class="co-service-name">{{ order.service_name }}</span>
                        </div>
                        <div class="co-td co-col-master">{{ order.master_name }}</div>
                        <div class="co-td co-col-time">{{ order.arrival_time || '-' }}</div>
                        <div class="co-td co-col-price">
                            <span class="co-price">{{ formatPrice(order.total_amount) }}</span>
                        </div>
                        <div class="co-td co-col-status">
                            <span
                                class="co-status-badge"
                                :style="{
                                    color: statusConfig[order.status]?.color || '#1B2B5A',
                                    backgroundColor: statusConfig[order.status]?.bg || '#1B2B5A10',
                                }"
                            >
                                {{ statusLabel(order.status) }}
                            </span>
                        </div>
                    </Link>

                    <!-- Empty State -->
                    <div v-if="!orders.data.length" class="co-empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <p>{{ t('customer.orders.empty') }}</p>
                        <Link href="/booking" class="co-empty-cta">{{ t('customer.bookFirst') }}</Link>
                    </div>

                    <!-- Pagination -->
                    <div v-if="orders.data.length && orders.last_page > 1" class="co-pagination">
                        <span class="co-pag-info">{{ paginationInfo }}</span>
                        <div class="co-pag-btns">
                            <button
                                class="co-pag-btn co-pag-nav"
                                :disabled="!orders.prev_page_url"
                                @click="goToPage(orders.prev_page_url)"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            </button>
                            <button
                                v-for="link in orders.links.slice(1, -1)"
                                :key="link.label"
                                class="co-pag-btn"
                                :class="{ active: link.active }"
                                :disabled="!link.url"
                                @click="goToPage(link.url)"
                                v-html="link.label"
                            ></button>
                            <button
                                class="co-pag-btn co-pag-nav"
                                :disabled="!orders.next_page_url"
                                @click="goToPage(orders.next_page_url)"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
