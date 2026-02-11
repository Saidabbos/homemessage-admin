<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    master: Object,
    date: String,
    date_display: String,
    day_name: String,
    orders: Array,
    token: String,
});

const getStatusClass = (status) => {
    const classes = {
        'CONFIRMED': 'status-ready',
        'IN_PROGRESS': 'status-progress',
        'COMPLETED': 'status-done',
    };
    return classes[status] || 'status-default';
};

const getStatusIcon = (status) => {
    const icons = {
        'CONFIRMED': '✅',
        'IN_PROGRESS': '🔄',
        'COMPLETED': '✓',
    };
    return icons[status] || '•';
};

// Navigation dates
const prevDate = computed(() => {
    const d = new Date(props.date);
    d.setDate(d.getDate() - 1);
    return d.toISOString().split('T')[0];
});

const nextDate = computed(() => {
    const d = new Date(props.date);
    d.setDate(d.getDate() + 1);
    return d.toISOString().split('T')[0];
});

const isToday = computed(() => {
    return props.date === new Date().toISOString().split('T')[0];
});
</script>

<template>
    <Head :title="`${master.name} - ${date_display}`" />

    <div class="master-day-page">
        <!-- Header -->
        <header class="page-header">
            <div class="master-info">
                <div class="master-avatar">
                    <img v-if="master.photo_url" :src="master.photo_url" :alt="master.name" />
                    <span v-else class="avatar-placeholder">{{ master.name?.charAt(0) }}</span>
                </div>
                <div class="master-details">
                    <h1 class="master-name">{{ master.name }}</h1>
                    <p class="page-subtitle">Kunlik jadval</p>
                </div>
            </div>
        </header>

        <!-- Date Navigation -->
        <div class="date-nav">
            <Link :href="`/m/${token}/day/${prevDate}`" class="nav-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 18l-6-6 6-6"/>
                </svg>
            </Link>
            <div class="date-display">
                <span class="day-name">{{ day_name }}</span>
                <span class="date-full">{{ date_display }}</span>
                <span v-if="isToday" class="today-badge">Bugun</span>
            </div>
            <Link :href="`/m/${token}/day/${nextDate}`" class="nav-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
            </Link>
        </div>

        <!-- Orders List -->
        <div class="orders-container">
            <div v-if="orders.length === 0" class="empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <p>Bu kunga buyurtma yo'q</p>
            </div>

            <div v-else class="orders-list">
                <Link 
                    v-for="order in orders" 
                    :key="order.id"
                    :href="`/o/${order.order_number}`"
                    class="order-card"
                >
                    <div class="order-time">
                        <span class="time-text">{{ order.time }}</span>
                        <span class="duration-text">{{ order.duration }} daq</span>
                    </div>
                    <div class="order-info">
                        <div class="order-service">{{ order.service }}</div>
                        <div class="order-address">{{ order.address_short }}</div>
                    </div>
                    <div class="order-status" :class="getStatusClass(order.status)">
                        <span class="status-icon">{{ getStatusIcon(order.status) }}</span>
                    </div>
                </Link>
            </div>
        </div>

        <!-- Quick Nav -->
        <div class="quick-nav">
            <Link :href="`/m/${token}/day`" class="quick-btn" :class="{ active: isToday }">
                Bugun
            </Link>
        </div>
    </div>
</template>

<style scoped>
.master-day-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #1a2a3a 0%, #2d4a5e 50%, #1a2a3a 100%);
    padding: 20px 16px;
}

/* Header */
.page-header {
    margin-bottom: 24px;
}

.master-info {
    display: flex;
    align-items: center;
    gap: 16px;
}

.master-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    overflow: hidden;
    background: linear-gradient(135deg, #B8A369, #D4C89A);
    display: flex;
    align-items: center;
    justify-content: center;
}

.master-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    font-size: 24px;
    font-weight: 600;
    color: #1a2a3a;
}

.master-name {
    font-size: 20px;
    font-weight: 600;
    color: #fff;
    margin: 0;
}

.page-subtitle {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.5);
    margin: 4px 0 0;
}

/* Date Navigation */
.date-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 16px;
    padding: 12px 16px;
    margin-bottom: 24px;
}

.nav-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    color: #fff;
    transition: all 0.3s ease;
}

.nav-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

.date-display {
    text-align: center;
}

.day-name {
    display: block;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.5);
    text-transform: uppercase;
}

.date-full {
    display: block;
    font-size: 18px;
    font-weight: 600;
    color: #fff;
    margin-top: 2px;
}

.today-badge {
    display: inline-block;
    background: #B8A369;
    color: #1a2a3a;
    font-size: 10px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 10px;
    margin-top: 4px;
}

/* Orders */
.orders-container {
    margin-bottom: 80px;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: rgba(255, 255, 255, 0.4);
}

.empty-state svg {
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-state p {
    font-size: 16px;
}

.orders-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.order-card {
    display: flex;
    align-items: center;
    gap: 16px;
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 16px;
    padding: 16px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.order-card:hover {
    background: rgba(255, 255, 255, 0.12);
    transform: translateX(4px);
}

.order-time {
    text-align: center;
    min-width: 70px;
}

.time-text {
    display: block;
    font-size: 16px;
    font-weight: 600;
    color: #B8A369;
}

.duration-text {
    display: block;
    font-size: 11px;
    color: rgba(255, 255, 255, 0.4);
    margin-top: 2px;
}

.order-info {
    flex: 1;
    min-width: 0;
}

.order-service {
    font-size: 15px;
    font-weight: 500;
    color: #fff;
}

.order-address {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.5);
    margin-top: 4px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.order-status {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 16px;
}

.status-ready {
    background: rgba(16, 185, 129, 0.2);
}

.status-progress {
    background: rgba(245, 158, 11, 0.2);
}

.status-done {
    background: rgba(107, 114, 128, 0.2);
}

/* Quick Nav */
.quick-nav {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
}

.quick-btn {
    padding: 12px 24px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 30px;
    color: rgba(255, 255, 255, 0.7);
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
}

.quick-btn:hover, .quick-btn.active {
    background: #B8A369;
    color: #1a2a3a;
    border-color: #B8A369;
}
</style>
