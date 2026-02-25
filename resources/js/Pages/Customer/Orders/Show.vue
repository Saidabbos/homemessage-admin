<script setup>
import { ref, computed } from 'vue'
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const page = usePage()

const props = defineProps({
    order: Object,
    customer: Object,
    payment: Object,
})

const showCancelModal = ref(false)
const cancelling = ref(false)

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

const payWithPayme = () => {
    // Redirect to Payme payment page
    window.location.href = `/booking/payment/${props.order.id}?provider=payme`
}

const payWithClick = () => {
    // Redirect to Click payment page
    window.location.href = `/booking/payment/${props.order.id}?provider=click`
}

const confirmCancel = () => {
    cancelling.value = true
    router.post(`/customer/orders/${props.order.id}/cancel`, {}, {
        onFinish: () => {
            cancelling.value = false
            showCancelModal.value = false
        }
    })
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
                    <Link href="/customer/masters" class="cos-nav-item">
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
                    <Link href="/customer/addresses" class="cos-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span>{{ t('customer.navAddresses') }}</span>
                    </Link>
                    <Link href="/customer/profile" class="cos-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>{{ t('customer.navProfile') }}</span>
                    </Link>
                </nav>
            </div>

            <div class="cos-sidebar-bottom">
                <div class="cos-sidebar-divider"></div>
                <div class="cos-user-profile">
                    <Link href="/customer/profile" class="cos-user-link">
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

                <!-- Action Buttons -->
                <div v-if="order.can_pay || order.can_cancel" class="cos-full-row">
                    <div class="cos-actions-bar">
                        <!-- Pay Button -->
                        <div v-if="order.can_pay && payment?.enabled" class="cos-pay-section">
                            <span class="cos-pay-label">{{ t('orders.payNow') }}</span>
                            <div class="cos-pay-buttons">
                                <button v-if="payment.payme_enabled" @click="payWithPayme" class="cos-pay-btn cos-pay-payme">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                                        <line x1="1" y1="10" x2="23" y2="10"/>
                                    </svg>
                                    Payme
                                </button>
                                <button v-if="payment.click_enabled" @click="payWithClick" class="cos-pay-btn cos-pay-click">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                                        <line x1="1" y1="10" x2="23" y2="10"/>
                                    </svg>
                                    Click
                                </button>
                            </div>
                        </div>

                        <!-- Cancel Button -->
                        <button v-if="order.can_cancel" @click="showCancelModal = true" class="cos-cancel-btn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                                <circle cx="12" cy="12" r="10"/>
                                <line x1="15" y1="9" x2="9" y2="15"/>
                                <line x1="9" y1="9" x2="15" y2="15"/>
                            </svg>
                            {{ t('orders.cancelOrder') }}
                        </button>
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

        <!-- Cancel Confirmation Modal -->
        <Teleport to="body">
            <div v-if="showCancelModal" class="cos-modal-overlay" @click.self="showCancelModal = false">
                <div class="cos-modal">
                    <div class="cos-modal-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                    </div>
                    <h3 class="cos-modal-title">{{ t('orders.cancelConfirmTitle') }}</h3>
                    <p class="cos-modal-text">{{ t('orders.cancelConfirmText') }}</p>
                    <div class="cos-modal-actions">
                        <button @click="showCancelModal = false" class="cos-modal-btn cos-modal-btn-secondary">
                            {{ t('common.no') }}
                        </button>
                        <button @click="confirmCancel" :disabled="cancelling" class="cos-modal-btn cos-modal-btn-danger">
                            <span v-if="cancelling">{{ t('common.loading') }}...</span>
                            <span v-else>{{ t('orders.yesCancel') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

<style scoped>
/* Action Buttons */
.cos-actions-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 16px 20px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.cos-pay-section {
    display: flex;
    align-items: center;
    gap: 12px;
}

.cos-pay-label {
    font-size: 14px;
    font-weight: 500;
    color: #64748B;
}

.cos-pay-buttons {
    display: flex;
    gap: 8px;
}

.cos-pay-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.cos-pay-payme {
    background: #00CDBE;
    color: white;
}

.cos-pay-payme:hover {
    background: #00B8AA;
}

.cos-pay-click {
    background: #0066FF;
    color: white;
}

.cos-pay-click:hover {
    background: #0052CC;
}

.cos-cancel-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    background: #FEF2F2;
    border: 1px solid #FECACA;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    color: #EF4444;
    cursor: pointer;
    transition: all 0.2s;
}

.cos-cancel-btn:hover {
    background: #FEE2E2;
}

/* Modal */
.cos-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 20px;
}

.cos-modal {
    background: white;
    border-radius: 20px;
    padding: 32px;
    max-width: 400px;
    width: 100%;
    text-align: center;
}

.cos-modal-icon {
    margin-bottom: 16px;
    color: #F59E0B;
}

.cos-modal-title {
    font-size: 18px;
    font-weight: 600;
    color: #1E293B;
    margin: 0 0 8px;
}

.cos-modal-text {
    font-size: 14px;
    color: #64748B;
    margin: 0 0 24px;
}

.cos-modal-actions {
    display: flex;
    gap: 12px;
}

.cos-modal-btn {
    flex: 1;
    padding: 12px 20px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.cos-modal-btn-secondary {
    background: #F1F5F9;
    border: none;
    color: #64748B;
}

.cos-modal-btn-secondary:hover {
    background: #E2E8F0;
}

.cos-modal-btn-danger {
    background: #EF4444;
    border: none;
    color: white;
}

.cos-modal-btn-danger:hover {
    background: #DC2626;
}

.cos-modal-btn-danger:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .cos-actions-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .cos-pay-section {
        flex-direction: column;
        align-items: stretch;
    }

    .cos-pay-buttons {
        flex-direction: column;
    }

    .cos-pay-btn,
    .cos-cancel-btn {
        justify-content: center;
    }
}
</style>
