<script setup>
import { computed } from 'vue'
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const page = usePage()

const props = defineProps({
    order: Object,
    customer: Object,
})

const formatPrice = (amount) => {
    return new Intl.NumberFormat('uz-UZ').format(amount) + ' ' + t('common.sum')
}

const callMaster = () => {
    if (props.order.master?.phone) {
        window.location.href = `tel:${props.order.master.phone}`
    }
}

const goToRate = () => {
    router.post(`/customer/orders/${props.order.id}/rate`)
}

const paymentBadgeClass = (status) => {
    const map = {
        NOT_PAID: 'cos-pay-not-paid',
        PENDING: 'cos-pay-pending',
        PAID: 'cos-pay-paid',
        FAILED: 'cos-pay-failed',
        REFUNDED: 'cos-pay-refunded',
        CANCELLED: 'cos-pay-cancelled',
    }
    return map[status] || 'cos-pay-pending'
}

const userInitial = computed(() => {
    return props.customer?.name ? props.customer.name.charAt(0).toUpperCase() : '?'
})

const logout = () => {
    router.post('/customer/logout')
}
</script>

<template>
    <Head :title="order.order_number" />

    <div class="cos-page">
        <!-- Sidebar -->
        <aside class="cos-sidebar">
            <div class="cos-sidebar-top">
                <Link href="/" class="cos-sidebar-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m3 4.5a4.5 4.5 0 1 1-4.5-4.5M12 16.5V15m4.5-3a4.5 4.5 0 1 0-4.5-4.5M16.5 12H15"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <span>HOMEMASSAGE</span>
                </Link>

                <nav class="cos-nav">
                    <Link href="/customer/dashboard" class="cos-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                        <span>{{ t('customer.navDashboard') }}</span>
                    </Link>
                    <Link href="/customer/orders" class="cos-nav-item active">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>{{ t('customer.navBookings') }}</span>
                    </Link>
                    <Link href="/masters" class="cos-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span>{{ t('customer.navMasters') }}</span>
                    </Link>
                    <Link href="/customer/ratings" class="cos-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <span>{{ t('customer.navRatings') }}</span>
                    </Link>
                    <Link href="/customer/favorites" class="cos-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                        <span>{{ t('customer.navFavorites') }}</span>
                    </Link>
                </nav>
            </div>

            <div class="cos-sidebar-bottom">
                <div class="cos-sidebar-divider"></div>
                <div class="cos-user-profile">
                    <Link href="/customer/dashboard" class="cos-user-link">
                        <div class="cos-user-avatar">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <div class="cos-user-info">
                            <span class="cos-user-name">{{ customer?.name || 'Mijoz' }}</span>
                            <span class="cos-user-phone">{{ customer?.phone }}</span>
                        </div>
                    </Link>
                    <button @click="logout" class="cos-logout-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="cos-main">
            <!-- Top Bar -->
            <div class="cos-topbar">
                <div class="cos-topbar-left">
                    <Link href="/customer/orders" class="cos-back-btn">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </Link>
                    <div>
                        <h1 class="cos-page-title">{{ t('orders.orderDetails') }}</h1>
                        <p class="cos-order-number">{{ order.order_number }}</p>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="cos-content">
                <!-- Status Bar (full width) -->
                <div class="cos-full-row">
                    <div class="cos-status-bar">
                        <div class="cos-status-left">
                            <span class="cos-status-label">{{ t('orders.status') }}</span>
                            <span class="cos-status-value">{{ t(`orderStatuses.${order.status}`) }}</span>
                        </div>
                        <span :class="['cos-payment-badge', paymentBadgeClass(order.payment_status)]">
                            {{ t(`paymentStatuses.${order.payment_status}`) }}
                        </span>
                    </div>
                </div>

                <!-- Two Column Grid -->
                <div class="cos-grid">
                    <!-- Left Column: Date, Service, Address -->
                    <div class="cos-col">
                        <!-- Date & Time -->
                        <div class="cos-card">
                            <h3 class="cos-card-title">{{ t('orders.dateTime') }}</h3>
                            <div class="cos-info-rows">
                                <div class="cos-info-row">
                                    <span class="cos-info-label">{{ t('orders.date') }}</span>
                                    <span class="cos-info-value">{{ order.booking_date_display }}</span>
                                </div>
                                <div class="cos-info-row">
                                    <span class="cos-info-label">{{ t('orders.time') }}</span>
                                    <span class="cos-info-value">{{ order.arrival_window }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Service Details -->
                        <div class="cos-card">
                            <h3 class="cos-card-title">{{ t('orders.service') }}</h3>
                            <div class="cos-info-rows">
                                <div class="cos-info-row">
                                    <span class="cos-info-label">{{ t('orders.massageType') }}</span>
                                    <span class="cos-info-value">{{ order.service_type?.name || '-' }}</span>
                                </div>
                                <div class="cos-info-row">
                                    <span class="cos-info-label">{{ t('orders.duration') }}</span>
                                    <span class="cos-info-value">{{ order.duration?.minutes }} {{ t('common.min') }}</span>
                                </div>
                                <div v-if="order.oil" class="cos-info-row">
                                    <span class="cos-info-label">{{ t('orders.oil') }}</span>
                                    <span class="cos-info-value">{{ order.oil.name }}</span>
                                </div>
                                <div class="cos-info-row">
                                    <span class="cos-info-label">{{ t('orders.peopleCount') }}</span>
                                    <span class="cos-info-value">{{ order.people_count }}</span>
                                </div>
                                <div v-if="order.pressure_level" class="cos-info-row">
                                    <span class="cos-info-label">{{ t('orders.pressure') }}</span>
                                    <span class="cos-info-value">{{ t(`pressureLevels.${order.pressure_level}`) }}</span>
                                </div>
                                <div class="cos-info-row cos-info-total">
                                    <span class="cos-info-label">{{ t('orders.total') }}</span>
                                    <span class="cos-info-value">{{ formatPrice(order.total_amount) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Address -->
                        <div v-if="order.address" class="cos-card">
                            <h3 class="cos-card-title">{{ t('orders.address') }}</h3>
                            <p class="cos-address-text">{{ order.address }}</p>
                            <p v-if="order.entrance || order.floor || order.apartment" class="cos-address-detail">
                                <span v-if="order.entrance">{{ t('orders.entrance') }}: {{ order.entrance }}</span>
                                <span v-if="order.floor"> &middot; {{ t('orders.floor') }}: {{ order.floor }}</span>
                                <span v-if="order.apartment"> &middot; {{ t('orders.apartment') }}: {{ order.apartment }}</span>
                            </p>
                            <p v-if="order.landmark" class="cos-address-landmark">
                                {{ t('orders.landmark') }}: {{ order.landmark }}
                            </p>
                        </div>
                    </div>

                    <!-- Right Column: Master, Rating, Timeline -->
                    <div class="cos-col">
                        <!-- Master -->
                        <div v-if="order.master" class="cos-card">
                            <h3 class="cos-card-title">{{ t('orders.master') }}</h3>
                            <div class="cos-master-row">
                                <img
                                    :src="order.master.photo_url"
                                    :alt="order.master.name"
                                    class="cos-master-photo"
                                />
                                <div class="cos-master-info">
                                    <div class="cos-master-name">{{ order.master.name }}</div>
                                    <div class="cos-master-role">{{ t('customer.yourMasseur') }}</div>
                                </div>
                                <button
                                    v-if="order.status === 'CONFIRMED' || order.status === 'IN_PROGRESS'"
                                    @click="callMaster"
                                    class="cos-call-btn"
                                >
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Rating Display -->
                        <div v-if="order.customer_rating" class="cos-card">
                            <h3 class="cos-card-title">{{ t('customer.alreadyRated') }}</h3>
                            <div class="cos-rating-display">
                                <div class="cos-rating-overall">
                                    <div class="cos-rating-stars">
                                        <svg v-for="s in 5" :key="s" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" :fill="s <= order.customer_rating.overall_rating ? '#C8A951' : '#E8E5E0'" stroke="none">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                    </div>
                                    <span class="cos-rating-date">{{ order.customer_rating.rated_at }}</span>
                                </div>

                                <div v-if="order.customer_rating.punctuality_rating" class="cos-sub-ratings">
                                    <div class="cos-sub-item">
                                        <span class="cos-sub-label">{{ t('customer.ratings.punctuality') }}</span>
                                        <span class="cos-sub-value">{{ order.customer_rating.punctuality_rating }}/5</span>
                                    </div>
                                    <div class="cos-sub-item">
                                        <span class="cos-sub-label">{{ t('customer.ratings.professionalism') }}</span>
                                        <span class="cos-sub-value">{{ order.customer_rating.professionalism_rating }}/5</span>
                                    </div>
                                    <div class="cos-sub-item">
                                        <span class="cos-sub-label">{{ t('customer.ratings.cleanliness') }}</span>
                                        <span class="cos-sub-value">{{ order.customer_rating.cleanliness_rating }}/5</span>
                                    </div>
                                </div>

                                <p v-if="order.customer_rating.feedback" class="cos-rating-feedback">
                                    "{{ order.customer_rating.feedback }}"
                                </p>
                            </div>
                        </div>

                        <!-- Rate Button -->
                        <button v-else-if="order.can_rate" @click="goToRate" class="cos-rate-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                            {{ t('customer.rateSession') }}
                        </button>

                        <!-- Timeline -->
                        <div v-if="order.logs && order.logs.length > 0" class="cos-card">
                            <h3 class="cos-card-title">{{ t('customer.orderTimeline') }}</h3>
                            <div class="cos-timeline">
                                <div v-for="log in order.logs" :key="log.id" class="cos-timeline-item">
                                    <div class="cos-timeline-dot"></div>
                                    <div class="cos-timeline-content">
                                        <p class="cos-timeline-action">{{ t(`orderActions.${log.action}`, log.action) }}</p>
                                        <p class="cos-timeline-time">{{ log.created_at }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Info -->
                        <div class="cos-footer-info">
                            {{ t('customer.orderCreated') }}: {{ order.created_at }}
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
