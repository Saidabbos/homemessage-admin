<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    groupId: String,
    source: String, // 'miniapp' or null
    orders: Array,
    totalAmount: Number,
    providers: Array,
    paymentEnabled: Boolean,
    mockEnabled: Boolean,
    customer: Object,
});

// State: 'select' | 'processing' | 'success' | 'error'
const paymentState = ref('select');
const selectedProvider = ref(null);
const errorMessage = ref('');
const processingIndex = ref(-1);
const processedOrders = ref([]);

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const formatPrice = (amount) => {
    return Number(amount).toLocaleString('uz-UZ');
};

const selectProvider = (providerId) => {
    selectedProvider.value = providerId;
};

const canPay = computed(() => {
    return selectedProvider.value !== null;
});

const processPayment = async () => {
    if (!canPay.value) return;

    paymentState.value = 'processing';
    processingIndex.value = 0;
    processedOrders.value = [];
    errorMessage.value = '';

    try {
        for (let i = 0; i < props.orders.length; i++) {
            processingIndex.value = i;
            const order = props.orders[i];

            // Step 1: Create payment record
            const createResponse = await fetch('/api/public/payment/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    order_id: order.id,
                    provider: selectedProvider.value,
                }),
            });

            const createResult = await createResponse.json();

            if (!createResult.success) {
                throw new Error(createResult.message || t('bookingPayment.failedMessage'));
            }

            // Step 2: If mock mode, simulate payment
            if (props.mockEnabled) {
                const mockUrl = `/api/mock/${selectedProvider.value}/simulate`;
                const mockResponse = await fetch(mockUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        order_id: order.id,
                        scenario: 'success',
                    }),
                });

                const mockResult = await mockResponse.json();

                if (!mockResult.success) {
                    throw new Error(mockResult.message || t('bookingPayment.failedMessage'));
                }
            }

            processedOrders.value.push(order.id);
        }

        // All orders processed successfully
        paymentState.value = 'success';

    } catch (e) {
        console.error('Payment failed:', e);
        errorMessage.value = e.message || t('bookingPayment.failedMessage');
        paymentState.value = 'error';
    }
};

const retryPayment = () => {
    paymentState.value = 'select';
    errorMessage.value = '';
    processingIndex.value = -1;
    processedOrders.value = [];
};

const goToSuccess = () => {
    const sourceParam = props.source ? `&source=${props.source}` : '';
    router.visit('/booking/success?group_id=' + props.groupId + sourceParam);
};

const goToOrders = () => {
    // If from miniapp, go to miniapp orders
    if (props.source === 'miniapp') {
        router.visit('/app/orders');
    } else {
        router.visit('/customer/orders');
    }
};

const goHome = () => {
    router.visit('/');
};

const goToBooking = () => {
    router.visit('/booking');
};
</script>

<template>
    <Head :title="t('bookingPayment.title')" />

    <div class="bp-page">
        <!-- Top Bar -->
        <div class="bp-topbar">
            <Link href="/" class="bp-logo">GOLDEN TOUCH</Link>
            <span class="bp-title">{{ t('bookingPayment.title') }}</span>
            <Link href="/booking" class="bp-close-btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </Link>
        </div>

        <!-- Select State -->
        <div v-if="paymentState === 'select'" class="bp-content">
            <!-- Mock Banner -->
            <div v-if="mockEnabled" class="bp-mock-banner">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                {{ t('bookingPayment.mockMode') }}
            </div>

            <!-- Order Summary -->
            <div>
                <h3 class="bp-section-title">{{ t('bookingPayment.orderSummary') }}</h3>
                <div class="bp-orders">
                    <div v-for="order in orders" :key="order.id" class="bp-order-card">
                        <div class="bp-order-top">
                            <div class="bp-order-avatar">
                                <img v-if="order.master_photo" :src="order.master_photo" :alt="order.master_name" />
                                <div v-else class="bp-order-avatar-placeholder">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="bp-order-header">
                                <h4 class="bp-order-service">{{ order.service_name }}</h4>
                                <span class="bp-order-master">{{ order.master_name }}</span>
                            </div>
                        </div>
                        <div class="bp-order-details">
                            <div class="bp-order-row">
                                <span class="bp-order-label">{{ t('bookingPayment.dateTime') }}</span>
                                <span class="bp-order-value">{{ order.booking_date }} {{ order.arrival_window }}</span>
                            </div>
                            <div class="bp-order-row">
                                <span class="bp-order-label">{{ t('bookingPayment.duration') }}</span>
                                <span class="bp-order-value">{{ order.duration_minutes }} {{ t('bookingPayment.minutes') }}</span>
                            </div>
                            <div class="bp-order-row">
                                <span class="bp-order-label">{{ t('bookingPayment.amount') }}</span>
                                <span class="bp-order-price">{{ formatPrice(order.total_amount) }} {{ t('bookingPayment.sum') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total -->
            <div class="bp-total-card">
                <span class="bp-total-label">{{ t('bookingPayment.total') }}</span>
                <span class="bp-total-value">{{ formatPrice(totalAmount) }} {{ t('bookingPayment.sum') }}</span>
            </div>

            <!-- Provider Selection -->
            <div>
                <h3 class="bp-section-title">{{ t('bookingPayment.selectProvider') }}</h3>
                <div class="bp-provider-grid">
                    <button
                        v-for="provider in providers"
                        :key="provider.id"
                        class="bp-provider-btn"
                        :class="{ selected: selectedProvider === provider.id }"
                        @click="selectProvider(provider.id)"
                    >
                        <div class="bp-provider-logo">
                            <span class="bp-provider-logo-text" :class="provider.id">{{ provider.name }}</span>
                        </div>
                        <span class="bp-provider-name">{{ provider.name }}</span>
                    </button>
                </div>
            </div>

            <!-- Pay Button -->
            <button
                class="bp-pay-btn"
                :disabled="!canPay"
                @click="processPayment"
            >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                    <line x1="1" y1="10" x2="23" y2="10"/>
                </svg>
                {{ t('bookingPayment.payNow') }} — {{ formatPrice(totalAmount) }} {{ t('bookingPayment.sum') }}
            </button>
        </div>

        <!-- Processing State -->
        <div v-if="paymentState === 'processing'" class="bp-content">
            <div class="bp-processing">
                <div class="bp-spinner"></div>
                <h3 class="bp-processing-text">{{ t('bookingPayment.processing') }}</h3>
                <p class="bp-processing-sub">{{ t('bookingPayment.processingWait') }}</p>
                <div class="bp-processing-orders">
                    <div
                        v-for="(order, index) in orders"
                        :key="order.id"
                        class="bp-processing-item"
                        :class="{
                            active: index === processingIndex,
                            done: processedOrders.includes(order.id),
                        }"
                    >
                        <div v-if="processedOrders.includes(order.id)" class="bp-processing-item-check">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="20,6 9,17 4,12"/>
                            </svg>
                        </div>
                        <div v-else-if="index === processingIndex" class="bp-processing-item-spinner"></div>
                        <div v-else class="bp-processing-item-pending"></div>
                        <span>{{ order.service_name }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success State -->
        <div v-if="paymentState === 'success'" class="bp-content">
            <div class="bp-success">
                <div class="bp-success-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22,4 12,14.01 9,11.01"/>
                    </svg>
                </div>
                <h3 class="bp-success-title">{{ t('bookingPayment.success') }}</h3>
                <p class="bp-success-message">{{ t('bookingPayment.successMessage') }}</p>
                <div class="bp-success-actions">
                    <button class="bp-btn-primary" @click="goToOrders">
                        {{ t('bookingPayment.goToOrders') }}
                    </button>
                    <button class="bp-btn-secondary" @click="goHome">
                        {{ t('bookingPayment.goHome') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Error State -->
        <div v-if="paymentState === 'error'" class="bp-content">
            <div class="bp-error">
                <div class="bp-error-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                </div>
                <h3 class="bp-error-title">{{ t('bookingPayment.failed') }}</h3>
                <p class="bp-error-message">{{ errorMessage || t('bookingPayment.failedMessage') }}</p>
                <div class="bp-error-actions">
                    <button class="bp-btn-retry" @click="retryPayment">
                        {{ t('bookingPayment.retry') }}
                    </button>
                    <button class="bp-btn-secondary" @click="goHome">
                        {{ t('bookingPayment.goHome') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
