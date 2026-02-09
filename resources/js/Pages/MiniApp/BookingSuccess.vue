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
        <!-- Background circles -->
        <div class="bg-circles">
            <div class="circle c1"></div>
            <div class="circle c2"></div>
            <div class="circle c3"></div>
        </div>

        <!-- Header -->
        <header class="success-header">
            <h1 class="header-title">Tasdiqlash</h1>
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
        <div class="details-box glass">
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
            <button class="btn-outline glass" @click="router.visit('/app')">
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
    background: linear-gradient(135deg, #1a2a3a 0%, #2d4a5e 50%, #1a2a3a 100%);
    padding: 24px 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    overflow: hidden;
}

/* Background circles */
.bg-circles {
    position: fixed;
    inset: 0;
    pointer-events: none;
    overflow: hidden;
}

.circle {
    position: absolute;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(184, 163, 105, 0.3), rgba(107, 139, 164, 0.2));
    filter: blur(60px);
    animation: float 8s ease-in-out infinite;
}

.c1 { width: 200px; height: 200px; top: -50px; right: -50px; }
.c2 { width: 150px; height: 150px; bottom: 200px; left: -40px; animation-delay: -2s; }
.c3 { width: 100px; height: 100px; top: 40%; right: 10%; animation-delay: -4s; }

@keyframes float {
    0%, 100% { transform: translateY(0) scale(1); opacity: 0.6; }
    50% { transform: translateY(-30px) scale(1.1); opacity: 0.8; }
}

/* Glass effect */
.glass {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.12);
}

/* Header */
.success-header {
    width: 100%;
    text-align: center;
    margin-bottom: 32px;
    position: relative;
    z-index: 1;
}

.header-title {
    font-size: 18px;
    font-weight: 600;
    color: #fff;
    margin: 0;
}

/* Success Icon */
.success-icon {
    width: 100px;
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #B8A369, #D4C89A);
    border-radius: 50%;
    color: #1a2a3a;
    margin-bottom: 24px;
    box-shadow: 0 8px 40px rgba(184, 163, 105, 0.4);
    animation: scaleIn 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    z-index: 1;
}

@keyframes scaleIn {
    0% { transform: scale(0); opacity: 0; }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); opacity: 1; }
}

/* Success Message */
.success-message {
    font-size: 26px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 8px;
    text-align: center;
    animation: fadeInUp 0.5s ease 0.2s both;
    position: relative;
    z-index: 1;
}

.order-number {
    font-size: 18px;
    font-weight: 600;
    color: #B8A369;
    margin: 0 0 8px;
    animation: fadeInUp 0.5s ease 0.3s both;
    position: relative;
    z-index: 1;
}

.success-hint {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.5);
    margin: 0 0 32px;
    text-align: center;
    animation: fadeInUp 0.5s ease 0.4s both;
    position: relative;
    z-index: 1;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Details Box */
.details-box {
    width: 100%;
    border-radius: 24px;
    padding: 24px;
    margin-bottom: 32px;
    animation: fadeInUp 0.5s ease 0.5s both;
    position: relative;
    z-index: 1;
}

.details-title {
    font-size: 16px;
    font-weight: 600;
    color: #fff;
    margin: 0 0 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
}

.detail-label {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.5);
}

.detail-value {
    font-size: 14px;
    font-weight: 500;
    color: #fff;
    text-align: right;
}

.detail-total {
    display: flex;
    justify-content: space-between;
    padding-top: 16px;
    margin-top: 8px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.total-price {
    font-size: 22px;
    font-weight: 700;
    color: #B8A369;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 12px;
    width: 100%;
    margin-bottom: 32px;
    animation: fadeInUp 0.5s ease 0.6s both;
    position: relative;
    z-index: 1;
}

.btn-outline {
    flex: 1;
    padding: 16px;
    border-radius: 16px;
    font-size: 14px;
    font-weight: 600;
    color: #B8A369;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-outline:hover {
    background: rgba(184, 163, 105, 0.2);
    transform: translateY(-2px);
}

.btn-primary {
    flex: 1;
    padding: 16px;
    background: linear-gradient(135deg, #B8A369, #D4C89A);
    border: none;
    border-radius: 16px;
    font-size: 14px;
    font-weight: 600;
    color: #1a2a3a;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 20px rgba(184, 163, 105, 0.4);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(184, 163, 105, 0.5);
}

/* Contact Info */
.contact-info {
    text-align: center;
    animation: fadeInUp 0.5s ease 0.7s both;
    position: relative;
    z-index: 1;
}

.contact-label {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.4);
    margin: 0 0 4px;
}

.contact-phone {
    font-size: 16px;
    font-weight: 600;
    color: #fff;
    text-decoration: none;
    transition: color 0.3s ease;
}

.contact-phone:hover {
    color: #B8A369;
}
</style>
