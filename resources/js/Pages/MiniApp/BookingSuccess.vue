<script setup>
import { router } from '@inertiajs/vue3';
import MiniAppLayout from '@/Layouts/MiniAppLayout.vue';

defineOptions({ layout: MiniAppLayout });

const props = defineProps({
    order: Object,
});

const formatPrice = (price) => new Intl.NumberFormat('uz-UZ').format(price);

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    const dayNames = ['Yakshanba', 'Dushanba', 'Seshanba', 'Chorshanba', 'Payshanba', 'Juma', 'Shanba'];
    const monthNames = ['Yanvar', 'Fevral', 'Mart', 'Aprel', 'May', 'Iyun', 'Iyul', 'Avgust', 'Sentabr', 'Oktabr', 'Noyabr', 'Dekabr'];
    return `${d.getDate()}-${monthNames[d.getMonth()]} ${d.getFullYear()}, ${dayNames[d.getDay()]}`;
};

const slotDisplay = (start) => {
    if (!start) return '';
    const [hours, minutes] = start.split(':').map(Number);
    const endMinutes = minutes + 30;
    const endHours = hours + Math.floor(endMinutes / 60);
    const endMins = endMinutes % 60;
    return `${start} - ${String(endHours).padStart(2, '0')}:${String(endMins).padStart(2, '0')}`;
};
</script>

<template>
    <div class="success-page">
        <!-- Header -->
        <header class="success-header">
            <h1 class="success-title">Tasdiqlash</h1>
        </header>

        <!-- Success Icon -->
        <div class="success-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="20,6 9,17 4,12"/>
            </svg>
        </div>

        <!-- Success Message -->
        <h2 class="success-message">Buyurtma qabul qilindi!</h2>
        <p class="order-number">HM-{{ order?.order_number || order?.id }}</p>
        <p class="success-hint">Tez orada operator siz bilan bog'lanadi</p>

        <!-- Order Details Box -->
        <div class="details-box">
            <h3 class="details-title">Buyurtma tafsilotlari</h3>
            
            <div class="detail-row">
                <span class="detail-label">Sana:</span>
                <span class="detail-value">{{ formatDate(order?.booking_date) }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Vaqt:</span>
                <span class="detail-value">{{ slotDisplay(order?.arrival_window_start) }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Usta:</span>
                <span class="detail-value">{{ order?.master?.name || 'Tayinlanadi' }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Xizmat:</span>
                <span class="detail-value">{{ order?.service_type?.name }} ({{ order?.duration || 60 }} daq)</span>
            </div>
            
            <div class="detail-total">
                <span class="detail-label">Jami:</span>
                <span class="total-price">{{ formatPrice(order?.total_price || 0) }} UZS</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button class="btn-outline" @click="router.visit('/app')">
                Bosh sahifaga
            </button>
            <button class="btn-primary" @click="router.visit('/app/orders')">
                Buyurtmalarim
            </button>
        </div>

        <!-- Contact Info -->
        <div class="contact-info">
            <p class="contact-label">Savollar uchun:</p>
            <a href="tel:+998901234567" class="contact-phone">+998 90 123 45 67</a>
        </div>
    </div>
</template>

<style scoped>
.success-page {
    min-height: 100vh;
    background: #F8F6F3;
    padding: 24px 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* Header */
.success-header {
    width: 100%;
    text-align: center;
    margin-bottom: 32px;
}

.success-title {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin: 0;
}

/* Success Icon */
.success-icon {
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #B8A369;
    border-radius: 50%;
    color: #fff;
    margin-bottom: 24px;
}

/* Success Message */
.success-message {
    font-size: 24px;
    font-weight: 700;
    color: #333;
    margin: 0 0 8px;
    text-align: center;
}

.order-number {
    font-size: 16px;
    font-weight: 600;
    color: #B8A369;
    margin: 0 0 8px;
}

.success-hint {
    font-size: 14px;
    color: #888;
    margin: 0 0 32px;
    text-align: center;
}

/* Details Box */
.details-box {
    width: 100%;
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 32px;
}

.details-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin: 0 0 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid #E5E5E5;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
}

.detail-label {
    font-size: 14px;
    color: #888;
}

.detail-value {
    font-size: 14px;
    font-weight: 500;
    color: #333;
    text-align: right;
}

.detail-total {
    display: flex;
    justify-content: space-between;
    padding-top: 12px;
    margin-top: 8px;
    border-top: 1px solid #E5E5E5;
}

.total-price {
    font-size: 18px;
    font-weight: 700;
    color: #B8A369;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 12px;
    width: 100%;
    margin-bottom: 32px;
}

.btn-outline {
    flex: 1;
    padding: 14px;
    background: transparent;
    border: 2px solid #B8A369;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    color: #B8A369;
    cursor: pointer;
}

.btn-primary {
    flex: 1;
    padding: 14px;
    background: #B8A369;
    border: none;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    color: #fff;
    cursor: pointer;
}

/* Contact Info */
.contact-info {
    text-align: center;
}

.contact-label {
    font-size: 12px;
    color: #888;
    margin: 0 0 4px;
}

.contact-phone {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    text-decoration: none;
}
</style>
