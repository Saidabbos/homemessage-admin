<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    order: Object,
    master_token: String,
    back_date: String,
});
</script>

<template>
    <Head :title="`Buyurtma #${order.order_number}`" />

    <div class="order-view-page">
        <!-- Header -->
        <header class="page-header">
            <Link 
                v-if="master_token" 
                :href="`/m/${master_token}/day/${back_date}`" 
                class="back-btn"
            >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
            </Link>
            <div class="header-content">
                <h1 class="page-title">Buyurtma</h1>
                <p class="order-number">{{ order.order_number }}</p>
            </div>
        </header>

        <!-- Status Badge -->
        <div class="status-banner" :class="order.payment_status === 'PAID' ? 'paid' : 'unpaid'">
            <span class="status-icon">{{ order.payment_status === 'PAID' ? '✅' : '⏳' }}</span>
            <span class="status-text">
                {{ order.payment_status === 'PAID' ? "TO'LANGAN" : "TO'LANMAGAN" }}
            </span>
            <span class="amount">{{ order.amount }} so'm</span>
        </div>

        <!-- Main Info Card -->
        <div class="info-card">
            <div class="card-section">
                <h3 class="section-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    Vaqt
                </h3>
                <div class="info-row">
                    <span class="info-label">Sana:</span>
                    <span class="info-value">{{ order.date }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Vaqt:</span>
                    <span class="info-value highlight">{{ order.time }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Davomiyligi:</span>
                    <span class="info-value">{{ order.duration }} daqiqa</span>
                </div>
            </div>

            <div class="card-section">
                <h3 class="section-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                    </svg>
                    Xizmat
                </h3>
                <div class="info-row">
                    <span class="info-label">Turi:</span>
                    <span class="info-value">{{ order.service }}</span>
                </div>
                <div v-if="order.oil" class="info-row">
                    <span class="info-label">Moy:</span>
                    <span class="info-value">{{ order.oil }}</span>
                </div>
            </div>
        </div>

        <!-- Contact Card -->
        <div class="info-card">
            <div class="card-section">
                <h3 class="section-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z"/>
                    </svg>
                    Aloqa
                </h3>
                <div class="info-row">
                    <span class="info-label">Mijoz:</span>
                    <span class="info-value">{{ order.customer_name }}</span>
                </div>
                <div class="info-row clickable">
                    <span class="info-label">Telefon:</span>
                    <a :href="`tel:${order.customer_phone}`" class="info-value phone">
                        {{ order.customer_phone }}
                    </a>
                </div>
                <div v-if="order.onsite_phone !== '-'" class="info-row clickable">
                    <span class="info-label">Joydagi tel:</span>
                    <a :href="`tel:${order.onsite_phone}`" class="info-value phone">
                        {{ order.onsite_phone }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Address Card -->
        <div class="info-card">
            <div class="card-section">
                <h3 class="section-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    Manzil
                </h3>
                <div class="address-main">{{ order.address }}</div>
                <div class="address-details">
                    <div class="detail-chip">
                        <span class="chip-label">Kirish:</span>
                        <span class="chip-value">{{ order.entrance }}</span>
                    </div>
                    <div class="detail-chip">
                        <span class="chip-label">Qavat:</span>
                        <span class="chip-value">{{ order.floor }}</span>
                    </div>
                    <div class="detail-chip">
                        <span class="chip-label">Lift:</span>
                        <span class="chip-value">{{ order.elevator ? 'Ha' : 'Yo\'q' }}</span>
                    </div>
                </div>
                <div v-if="order.parking !== '-'" class="info-row">
                    <span class="info-label">Parking:</span>
                    <span class="info-value">{{ order.parking }}</span>
                </div>
                <div v-if="order.landmark !== '-'" class="info-row">
                    <span class="info-label">Mo'ljal:</span>
                    <span class="info-value">{{ order.landmark }}</span>
                </div>
            </div>
        </div>

        <!-- Notes Card -->
        <div class="info-card">
            <div class="card-section">
                <h3 class="section-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                    Eslatmalar
                </h3>
                <div v-if="order.constraints !== '-'" class="note-item warning">
                    <span class="note-label">Cheklovlar:</span>
                    <span class="note-text">{{ order.constraints }}</span>
                </div>
                <div v-if="order.note_to_master !== '-'" class="note-item">
                    <span class="note-label">Izoh:</span>
                    <span class="note-text">{{ order.note_to_master }}</span>
                </div>
                <div class="detail-chips-row">
                    <div class="detail-chip" :class="order.space_ok ? 'ok' : 'warn'">
                        <span class="chip-label">Joy 2×2:</span>
                        <span class="chip-value">{{ order.space_ok ? 'Ha' : 'Yo\'q' }}</span>
                    </div>
                    <div class="detail-chip" :class="order.pets ? 'warn' : 'ok'">
                        <span class="chip-label">Hayvonlar:</span>
                        <span class="chip-value">{{ order.pets ? 'Ha' : 'Yo\'q' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.order-view-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #1a2a3a 0%, #2d4a5e 50%, #1a2a3a 100%);
    padding: 20px 16px 40px;
}

/* Header */
.page-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 20px;
}

.back-btn {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 14px;
    color: #fff;
    transition: all 0.3s ease;
}

.back-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

.header-content {
    flex: 1;
}

.page-title {
    font-size: 20px;
    font-weight: 600;
    color: #fff;
    margin: 0;
}

.order-number {
    font-size: 14px;
    color: #B8A369;
    margin: 4px 0 0;
}

/* Status Banner */
.status-banner {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    border-radius: 16px;
    margin-bottom: 20px;
}

.status-banner.paid {
    background: rgba(16, 185, 129, 0.15);
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.status-banner.unpaid {
    background: rgba(245, 158, 11, 0.15);
    border: 1px solid rgba(245, 158, 11, 0.3);
}

.status-icon {
    font-size: 24px;
}

.status-text {
    font-size: 14px;
    font-weight: 600;
    color: #fff;
    flex: 1;
}

.amount {
    font-size: 16px;
    font-weight: 700;
    color: #B8A369;
}

/* Info Cards */
.info-card {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 20px;
    padding: 20px;
    margin-bottom: 16px;
}

.card-section {
    /* No extra styling needed */
}

.section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.6);
    margin: 0 0 16px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.section-title svg {
    opacity: 0.6;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.5);
}

.info-value {
    font-size: 14px;
    font-weight: 500;
    color: #fff;
}

.info-value.highlight {
    font-size: 16px;
    font-weight: 600;
    color: #B8A369;
}

.info-value.phone {
    color: #B8A369;
    text-decoration: none;
}

/* Address */
.address-main {
    font-size: 16px;
    font-weight: 500;
    color: #fff;
    margin-bottom: 16px;
    line-height: 1.5;
}

.address-details {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 12px;
}

.detail-chip {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 10px;
    font-size: 13px;
}

.chip-label {
    color: rgba(255, 255, 255, 0.5);
}

.chip-value {
    color: #fff;
    font-weight: 500;
}

.detail-chips-row {
    display: flex;
    gap: 10px;
    margin-top: 12px;
}

.detail-chip.ok {
    background: rgba(16, 185, 129, 0.15);
}

.detail-chip.warn {
    background: rgba(245, 158, 11, 0.15);
}

/* Notes */
.note-item {
    padding: 12px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    margin-bottom: 10px;
}

.note-item.warning {
    background: rgba(245, 158, 11, 0.1);
    border: 1px solid rgba(245, 158, 11, 0.2);
}

.note-label {
    display: block;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.5);
    margin-bottom: 4px;
}

.note-text {
    font-size: 14px;
    color: #fff;
    line-height: 1.5;
}
</style>
