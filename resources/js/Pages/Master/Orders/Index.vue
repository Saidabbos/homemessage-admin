<script setup>
import { computed, ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
    orders: { type: Object, required: true },
    filters: { type: Object, default: () => ({}) },
    statusCounts: { type: Object, default: () => ({}) },
    master: { type: Object, required: true },
})

const activeTab = ref(props.filters.status || null)
const searchQuery = ref(props.filters.search || '')
let searchTimeout = null

const statusTabs = computed(() => [
    { key: null, label: t('master.orders.tabAll') },
    { key: 'ACTIVE', label: t('master.orders.tabActive') },
    { key: 'COMPLETED', label: t('master.orders.tabCompleted') },
    { key: 'CANCELLED', label: t('master.orders.tabCancelled') },
])

const totalOrders = computed(() => {
    return Object.values(props.statusCounts).reduce((sum, c) => sum + c, 0)
})

const tabCount = (key) => {
    if (!key) return totalOrders.value
    if (key === 'ACTIVE') {
        return (props.statusCounts['NEW'] || 0) +
               (props.statusCounts['CONFIRMING'] || 0) +
               (props.statusCounts['CONFIRMED'] || 0) +
               (props.statusCounts['IN_PROGRESS'] || 0)
    }
    return props.statusCounts[key] || 0
}

const applyFilters = () => {
    const params = {}
    if (activeTab.value) params.status = activeTab.value
    if (searchQuery.value.trim()) params.search = searchQuery.value.trim()
    router.get('/master/orders', params, { preserveState: true, preserveScroll: true })
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
    NEW: { color: '#2D6A4F', bg: '#2D6A4F10' },
    CONFIRMING: { color: '#2D6A4F', bg: '#2D6A4F10' },
    CONFIRMED: { color: '#C8A951', bg: '#C8A95120' },
    IN_PROGRESS: { color: '#3B82F6', bg: '#3B82F615' },
    COMPLETED: { color: '#22C55E', bg: '#22C55E15' },
    CANCELLED: { color: '#EF4444', bg: '#EF444415' },
}

const statusLabel = (status) => {
    const map = {
        NEW: t('master.orders.statusNew'),
        CONFIRMING: t('master.orders.statusConfirming'),
        CONFIRMED: t('master.orders.statusConfirmed'),
        IN_PROGRESS: t('master.orders.statusInProgress'),
        COMPLETED: t('master.orders.statusCompleted'),
        CANCELLED: t('master.orders.statusCancelled'),
    }
    return map[status] || status
}

const paginationInfo = computed(() => {
    const { from, to, total } = props.orders
    if (!total) return ''
    return `${from}-${to} / ${total} ${t('master.orders.ordersCount')}`
})

const logout = () => {
    router.post('/master/logout')
}
</script>

<template>
    <Head :title="t('master.orders.title')" />

    <div class="mo-page">
        <!-- Sidebar -->
        <aside class="mo-sidebar">
            <div class="mo-sidebar-top">
                <Link href="/" class="mo-sidebar-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m3 4.5a4.5 4.5 0 1 1-4.5-4.5M12 16.5V15m4.5-3a4.5 4.5 0 1 0-4.5-4.5M16.5 12H15"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <span>HOMEMASSAGE</span>
                </Link>

                <nav class="mo-nav">
                    <Link href="/master/dashboard" class="mo-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                        <span>{{ t('master.navDashboard') }}</span>
                    </Link>
                    <Link href="/master/orders" class="mo-nav-item active">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>{{ t('master.navOrders') }}</span>
                    </Link>
                    <Link href="/master/ratings" class="mo-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <span>{{ t('master.navRatings') }}</span>
                    </Link>
                    <Link href="/master/profile" class="mo-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>{{ t('master.navProfile') }}</span>
                    </Link>
                </nav>
            </div>

            <div class="mo-sidebar-bottom">
                <div class="mo-sidebar-divider"></div>
                <div class="mo-user-profile">
                    <Link href="/master/dashboard" class="mo-user-link">
                        <div class="mo-user-avatar">
                            <img v-if="master.photo_url" :src="master.photo_url" :alt="master.name" />
                            <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <div class="mo-user-info">
                            <span class="mo-user-name">{{ master.name }}</span>
                            <span class="mo-user-phone">{{ master.phone }}</span>
                        </div>
                    </Link>
                    <button class="mo-logout-btn" @click="logout" :title="t('master.logout')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Area -->
        <main class="mo-main">
            <!-- Top Bar -->
            <div class="mo-topbar">
                <div class="mo-topbar-left">
                    <h1 class="mo-page-title">{{ t('master.orders.title') }}</h1>
                    <p class="mo-page-subtitle">{{ t('master.orders.subtitle') }}</p>
                </div>
            </div>

            <!-- Content Area -->
            <div class="mo-content">
                <!-- Filter Bar -->
                <div class="mo-filter-bar">
                    <div class="mo-tabs">
                        <button
                            v-for="tab in statusTabs"
                            :key="tab.key"
                            class="mo-tab"
                            :class="{ active: activeTab === tab.key }"
                            @click="selectTab(tab.key)"
                        >
                            {{ tab.label }}
                        </button>
                    </div>
                    <div class="mo-filter-right">
                        <div class="mo-search-box">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            <input
                                v-model="searchQuery"
                                type="text"
                                :placeholder="t('master.orders.searchPlaceholder')"
                            />
                        </div>
                    </div>
                </div>

                <!-- Orders Table Card -->
                <div class="mo-table-card">
                    <!-- Table Header -->
                    <div class="mo-table-header">
                        <div class="mo-th mo-col-id">{{ t('master.orders.colId') }}</div>
                        <div class="mo-th mo-col-date">{{ t('master.orders.colDate') }}</div>
                        <div class="mo-th mo-col-customer">{{ t('master.orders.colCustomer') }}</div>
                        <div class="mo-th mo-col-service">{{ t('master.orders.colService') }}</div>
                        <div class="mo-th mo-col-time">{{ t('master.orders.colTime') }}</div>
                        <div class="mo-th mo-col-price">{{ t('master.orders.colPrice') }}</div>
                        <div class="mo-th mo-col-status">{{ t('master.orders.colStatus') }}</div>
                    </div>

                    <!-- Table Rows -->
                    <Link
                        v-for="order in orders.data"
                        :key="order.id"
                        :href="`/master/orders/${order.id}`"
                        class="mo-table-row"
                    >
                        <div class="mo-td mo-col-id">
                            <span class="mo-order-id">#{{ order.order_number }}</span>
                        </div>
                        <div class="mo-td mo-col-date">{{ order.booking_date_formatted }}</div>
                        <div class="mo-td mo-col-customer">
                            <span class="mo-customer-name">{{ order.customer_name }}</span>
                        </div>
                        <div class="mo-td mo-col-service">
                            <span class="mo-service-name">{{ order.service_name }}</span>
                        </div>
                        <div class="mo-td mo-col-time">{{ order.arrival_time || '-' }}</div>
                        <div class="mo-td mo-col-price">
                            <span class="mo-price">{{ formatPrice(order.total_amount) }}</span>
                        </div>
                        <div class="mo-td mo-col-status">
                            <span
                                class="mo-status-badge"
                                :style="{
                                    color: statusConfig[order.status]?.color || '#2D6A4F',
                                    backgroundColor: statusConfig[order.status]?.bg || '#2D6A4F10',
                                }"
                            >
                                {{ statusLabel(order.status) }}
                            </span>
                        </div>
                    </Link>

                    <!-- Empty State -->
                    <div v-if="!orders.data.length" class="mo-empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <p>{{ t('master.orders.empty') }}</p>
                    </div>

                    <!-- Pagination -->
                    <div v-if="orders.data.length && orders.last_page > 1" class="mo-pagination">
                        <span class="mo-pag-info">{{ paginationInfo }}</span>
                        <div class="mo-pag-btns">
                            <button
                                class="mo-pag-btn mo-pag-nav"
                                :disabled="!orders.prev_page_url"
                                @click="goToPage(orders.prev_page_url)"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            </button>
                            <button
                                v-for="link in orders.links.slice(1, -1)"
                                :key="link.label"
                                class="mo-pag-btn"
                                :class="{ active: link.active }"
                                :disabled="!link.url"
                                @click="goToPage(link.url)"
                                v-html="link.label"
                            ></button>
                            <button
                                class="mo-pag-btn mo-pag-nav"
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
