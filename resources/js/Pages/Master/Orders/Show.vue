<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
    order: { type: Object, required: true },
    master: { type: Object, required: true },
})

const formatPrice = (amount) => {
    return new Intl.NumberFormat('uz-UZ').format(amount) + ' ' + t('common.sum')
}

const goToRate = () => {
    router.post(`/master/orders/${props.order.id}/rate`)
}

const paymentBadgeClass = (status) => {
    const map = {
        NOT_PAID: 'mos-pay-not-paid',
        PENDING: 'mos-pay-pending',
        PAID: 'mos-pay-paid',
        FAILED: 'mos-pay-failed',
        REFUNDED: 'mos-pay-refunded',
        CANCELLED: 'mos-pay-cancelled',
    }
    return map[status] || 'mos-pay-pending'
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

const logout = () => {
    router.post('/master/logout')
}
</script>

<template>
    <Head :title="`${t('master.orders.order')} ${order.order_number}`" />

    <div class="mos-page">
        <!-- Sidebar (reuses mo-* classes from master-orders.css) -->
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

        <!-- Main Content -->
        <main class="mos-main">
            <!-- Top Bar -->
            <div class="mos-topbar">
                <div class="mos-topbar-left">
                    <Link href="/master/orders" class="mos-back-btn">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </Link>
                    <div>
                        <h1 class="mos-page-title">{{ t('master.orders.order') }}</h1>
                        <p class="mos-order-number">{{ order.order_number }}</p>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="mos-content">
                <!-- Status Bar (full width) -->
                <div class="mos-full-row">
                    <div class="mos-status-bar">
                        <div class="mos-status-left">
                            <span class="mos-status-label">{{ t('master.orders.orderStatus') }}</span>
                            <span class="mos-status-value">{{ statusLabel(order.status) }}</span>
                        </div>
                        <span v-if="order.payment_status" :class="['mos-payment-badge', paymentBadgeClass(order.payment_status)]">
                            {{ order.payment_status }}
                        </span>
                    </div>
                </div>

                <!-- Two Column Grid -->
                <div class="mos-grid">
                    <!-- Left Column: Date/Time, Service, Address -->
                    <div class="mos-col">
                        <!-- Date & Time -->
                        <div class="mos-card">
                            <h3 class="mos-card-title">{{ t('master.orders.dateTime') }}</h3>
                            <div class="mos-info-rows">
                                <div class="mos-info-row">
                                    <span class="mos-info-label">{{ t('master.orders.date') }}</span>
                                    <span class="mos-info-value">{{ order.booking_date_display }}</span>
                                </div>
                                <div class="mos-info-row">
                                    <span class="mos-info-label">{{ t('master.orders.time') }}</span>
                                    <span class="mos-info-value">{{ order.arrival_window || order.arrival_time || '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Service Details -->
                        <div class="mos-card">
                            <h3 class="mos-card-title">{{ t('master.orders.serviceDetails') }}</h3>
                            <div class="mos-info-rows">
                                <div class="mos-info-row">
                                    <span class="mos-info-label">{{ t('master.orders.massageType') }}</span>
                                    <span class="mos-info-value">{{ order.service_type?.name || order.service_name || '-' }}</span>
                                </div>
                                <div v-if="order.duration" class="mos-info-row">
                                    <span class="mos-info-label">{{ t('master.orders.duration') }}</span>
                                    <span class="mos-info-value">{{ order.duration?.minutes || order.duration }} {{ t('common.min') }}</span>
                                </div>
                                <div v-if="order.oil" class="mos-info-row">
                                    <span class="mos-info-label">{{ t('master.orders.oil') }}</span>
                                    <span class="mos-info-value">{{ order.oil.name || order.oil }}</span>
                                </div>
                                <div v-if="order.pressure_level" class="mos-info-row">
                                    <span class="mos-info-label">{{ t('master.orders.pressure') }}</span>
                                    <span class="mos-info-value">{{ order.pressure_level }}</span>
                                </div>
                                <div v-if="order.people_count" class="mos-info-row">
                                    <span class="mos-info-label">{{ t('master.orders.peopleCount') }}</span>
                                    <span class="mos-info-value">{{ order.people_count }}</span>
                                </div>
                                <div class="mos-info-row mos-info-total">
                                    <span class="mos-info-label">{{ t('master.orders.total') }}</span>
                                    <span class="mos-info-value">{{ formatPrice(order.total_amount) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Address -->
                        <div v-if="order.address" class="mos-card">
                            <h3 class="mos-card-title">{{ t('master.orders.address') }}</h3>
                            <p class="mos-address-text">{{ order.address }}</p>
                            <p v-if="order.entrance || order.floor || order.apartment" class="mos-address-detail">
                                <span v-if="order.entrance">{{ t('master.orders.entrance') }}: {{ order.entrance }}</span>
                                <span v-if="order.floor"> &middot; {{ t('master.orders.floor') }}: {{ order.floor }}</span>
                                <span v-if="order.apartment"> &middot; {{ t('master.orders.apartment') }}: {{ order.apartment }}</span>
                            </p>
                            <p v-if="order.landmark" class="mos-address-landmark">
                                {{ t('master.orders.landmark') }}: {{ order.landmark }}
                            </p>
                        </div>
                    </div>

                    <!-- Right Column: Customer, Ratings -->
                    <div class="mos-col">
                        <!-- Customer Info -->
                        <div class="mos-card">
                            <h3 class="mos-card-title">{{ t('master.orders.customerInfo') }}</h3>
                            <div class="mos-info-rows">
                                <div class="mos-info-row">
                                    <span class="mos-info-label">{{ t('master.orders.customerName') }}</span>
                                    <span class="mos-info-value">{{ order.customer_name || '-' }}</span>
                                </div>
                                <div v-if="order.customer_phone" class="mos-info-row">
                                    <span class="mos-info-label">{{ t('master.orders.customerPhone') }}</span>
                                    <span class="mos-info-value">
                                        <a :href="`tel:${order.customer_phone}`">{{ order.customer_phone }}</a>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Rating (received from customer) -->
                        <div v-if="order.customer_rating" class="mos-card">
                            <h3 class="mos-card-title">{{ t('master.orders.customerRating') }}</h3>
                            <div class="mos-rating-display">
                                <div class="mos-rating-overall">
                                    <div class="mos-rating-stars">
                                        <svg v-for="s in 5" :key="s" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" :fill="s <= order.customer_rating.overall_rating ? '#C8A951' : '#E8E5E0'" stroke="none">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                    </div>
                                    <span class="mos-rating-date">{{ order.customer_rating.rated_at }}</span>
                                </div>

                                <div v-if="order.customer_rating.punctuality_rating" class="mos-sub-ratings">
                                    <div class="mos-sub-item">
                                        <span class="mos-sub-label">{{ t('master.orders.punctuality') }}</span>
                                        <span class="mos-sub-value">{{ order.customer_rating.punctuality_rating }}/5</span>
                                    </div>
                                    <div class="mos-sub-item">
                                        <span class="mos-sub-label">{{ t('master.orders.professionalism') }}</span>
                                        <span class="mos-sub-value">{{ order.customer_rating.professionalism_rating }}/5</span>
                                    </div>
                                    <div class="mos-sub-item">
                                        <span class="mos-sub-label">{{ t('master.orders.cleanliness') }}</span>
                                        <span class="mos-sub-value">{{ order.customer_rating.cleanliness_rating }}/5</span>
                                    </div>
                                </div>

                                <p v-if="order.customer_rating.feedback" class="mos-rating-feedback">
                                    "{{ order.customer_rating.feedback }}"
                                </p>
                            </div>
                        </div>

                        <!-- Master's Own Rating (already submitted) -->
                        <div v-if="order.master_rating" class="mos-card">
                            <h3 class="mos-card-title">{{ t('master.orders.myRating') }}</h3>
                            <div class="mos-rating-display">
                                <div class="mos-rating-overall">
                                    <div class="mos-rating-stars">
                                        <svg v-for="s in 5" :key="s" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" :fill="s <= order.master_rating.overall_rating ? '#C8A951' : '#E8E5E0'" stroke="none">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                    </div>
                                    <span class="mos-rating-date">{{ order.master_rating.rated_at }}</span>
                                </div>

                                <p v-if="order.master_rating.feedback" class="mos-rating-feedback">
                                    "{{ order.master_rating.feedback }}"
                                </p>
                            </div>
                        </div>

                        <!-- Rate Customer Button -->
                        <button v-else-if="order.can_rate" @click="goToRate" class="mos-rate-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            {{ t('master.orders.rateCustomer') }}
                        </button>

                        <!-- Footer Info -->
                        <div class="mos-footer-info">
                            {{ t('master.orders.created') }}: {{ order.created_at }}
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
