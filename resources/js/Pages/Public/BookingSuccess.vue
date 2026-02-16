<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    groupId: String,
    orders: Array,
});

const formatPrice = (amount) => {
    return Number(amount).toLocaleString('uz-UZ');
};

const getPaymentBadgeClass = (status) => {
    if (status === 'paid') return 'paid';
    if (status === 'pending' || status === 'not_paid') return 'pending';
    return 'failed';
};

const getPaymentLabel = (status) => {
    if (status === 'paid') return t('bookingPayment.paid');
    if (status === 'pending' || status === 'not_paid') return t('bookingPayment.pending');
    return t('bookingPayment.notPaid');
};
</script>

<template>
    <Head :title="t('bookingPayment.successTitle')" />

    <div class="bs-page">
        <!-- Top Bar -->
        <div class="bs-topbar">
            <Link href="/" class="bs-logo">GOLDEN TOUCH</Link>
        </div>

        <div class="bs-content">
            <!-- Success Icon -->
            <div class="bs-success-icon">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22,4 12,14.01 9,11.01"/>
                </svg>
            </div>

            <h1 class="bs-title">{{ t('bookingPayment.successTitle') }}</h1>
            <p class="bs-subtitle">{{ t('bookingPayment.successSubtitle') }}</p>

            <!-- Order Cards -->
            <div v-if="orders && orders.length" class="bs-orders">
                <div v-for="order in orders" :key="order.order_number" class="bs-order-card">
                    <div class="bs-order-top">
                        <span class="bs-order-number">{{ order.order_number }}</span>
                        <span class="bs-badge" :class="getPaymentBadgeClass(order.payment_status)">
                            {{ getPaymentLabel(order.payment_status) }}
                        </span>
                    </div>
                    <div class="bs-order-details">
                        <div class="bs-order-row">
                            <span class="bs-order-label">{{ t('bookingPayment.service') }}</span>
                            <span class="bs-order-value">{{ order.service_name }}</span>
                        </div>
                        <div class="bs-order-row">
                            <span class="bs-order-label">{{ t('bookingPayment.master') }}</span>
                            <span class="bs-order-value">{{ order.master_name }}</span>
                        </div>
                        <div class="bs-order-row">
                            <span class="bs-order-label">{{ t('bookingPayment.amount') }}</span>
                            <span class="bs-order-price">{{ formatPrice(order.total_amount) }} {{ t('bookingPayment.sum') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="bs-steps-card">
                <h3 class="bs-steps-title">{{ t('bookingPayment.nextSteps') }}</h3>
                <ul class="bs-steps-list">
                    <li class="bs-step-item">
                        <span class="bs-step-num">1</span>
                        <span>{{ t('bookingPayment.nextStep1') }}</span>
                    </li>
                    <li class="bs-step-item">
                        <span class="bs-step-num">2</span>
                        <span>{{ t('bookingPayment.nextStep2') }}</span>
                    </li>
                    <li class="bs-step-item">
                        <span class="bs-step-num">3</span>
                        <span>{{ t('bookingPayment.nextStep3') }}</span>
                    </li>
                </ul>
            </div>

            <!-- Actions -->
            <div class="bs-actions">
                <Link href="/customer/orders" class="bs-btn-primary">
                    {{ t('bookingPayment.goToOrders') }}
                </Link>
                <Link href="/booking" class="bs-btn-secondary">
                    {{ t('bookingPayment.newBooking') }}
                </Link>
            </div>
        </div>
    </div>
</template>
