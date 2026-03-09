<script setup>
import { ref, computed, onMounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import MiniAppLayout from '@/Layouts/MiniAppLayout.vue';
import SidebarMenu from '@/Components/MiniApp/SidebarMenu.vue';

defineOptions({ layout: MiniAppLayout });

const props = defineProps({
    master: Object,
    stats: Object,
    ratingSummary: Object,
    recentRatings: Array,
    upcomingOrders: Array,
    user: Object,
});

const showSidebar = ref(false);

onMounted(() => {
    if (window.Telegram?.WebApp) {
        window.Telegram.WebApp.expand();
    }
});

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Xayrli tong';
    if (hour < 18) return 'Xayrli kun';
    return 'Xayrli kech';
});

const formatPrice = (price) => new Intl.NumberFormat('uz-UZ').format(price);

const statusLabel = (status) => {
    const map = {
        'NEW': 'Yangi',
        'CONFIRMING': 'Tasdiqlanmoqda',
        'CONFIRMED': 'Tasdiqlangan',
        'WAITING_PAYMENT': "To'lov kutilmoqda",
        'PAID': "To'langan",
        'RESERVED': 'Band qilingan',
        'IN_PROGRESS': 'Jarayonda',
        'COMPLETED': 'Yakunlangan',
        'CANCELLED': 'Bekor qilingan',
    };
    return map[status] || status;
};

const statusColor = (status) => {
    const map = {
        'NEW': '#3B82F6',
        'CONFIRMING': '#F59E0B',
        'CONFIRMED': '#10B981',
        'WAITING_PAYMENT': '#F59E0B',
        'PAID': '#10B981',
        'RESERVED': '#8B5CF6',
        'IN_PROGRESS': '#3B82F6',
        'COMPLETED': '#6B7280',
        'CANCELLED': '#EF4444',
    };
    return map[status] || '#6B7280';
};

const renderStars = (rating) => {
    const full = Math.floor(rating);
    const half = rating % 1 >= 0.5 ? 1 : 0;
    const empty = 5 - full - half;
    return { full, half, empty };
};

const logout = () => {
    router.post('/app/logout');
};
</script>

<template>
    <div class="md-page">
        <!-- Sidebar Menu -->
        <SidebarMenu :show="showSidebar" :user="user" @close="showSidebar = false" />

        <!-- Header -->
        <header class="md-header">
            <div class="md-header-top">
                <button class="md-menu-btn" @click="showSidebar = true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>
                <div class="md-logo">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m3 4.5a4.5 4.5 0 1 1-4.5-4.5M12 16.5V15m4.5-3a4.5 4.5 0 1 0-4.5-4.5M16.5 12H15"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <span>SABAI</span>
                </div>
                <div class="md-role-badge">Master</div>
            </div>
        </header>

        <!-- Hero -->
        <section class="md-hero">
            <div class="md-hero-content">
                <div class="md-hero-row">
                    <div class="md-hero-text">
                        <div class="md-hero-badge">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 11V6a2 2 0 0 0-2-2a2 2 0 0 0-2 2"/><path d="M14 10V4a2 2 0 0 0-2-2a2 2 0 0 0-2 2v2"/><path d="M10 10.5V6a2 2 0 0 0-2-2a2 2 0 0 0-2 2v8"/><path d="M18 8a2 2 0 1 1 4 0v6a8 8 0 0 1-8 8h-2c-2.8 0-4.5-.86-5.99-2.34l-3.6-3.6a2 2 0 0 1 2.83-2.82L7 13"/>
                            </svg>
                            <span>Master panel</span>
                        </div>
                        <h1 class="md-hero-title">{{ greeting }}, {{ master.name }}!</h1>
                        <p class="md-hero-subtitle">Bugungi buyurtmalar va statistikangiz</p>
                    </div>
                    <div class="md-hero-avatar">
                        <img v-if="master.photo_url" :src="master.photo_url" :alt="master.name" />
                        <span v-else>{{ master.name?.charAt(0) }}</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Cards -->
        <section class="md-stats">
            <div class="md-stats-grid">
                <div class="md-stat-card">
                    <div class="md-stat-icon md-stat-icon-blue">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                    </div>
                    <span class="md-stat-value">{{ stats.totalOrders }}</span>
                    <span class="md-stat-label">Jami</span>
                </div>
                <div class="md-stat-card">
                    <div class="md-stat-icon md-stat-icon-green">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    </div>
                    <span class="md-stat-value">{{ stats.completedOrders }}</span>
                    <span class="md-stat-label">Bajarilgan</span>
                </div>
                <div class="md-stat-card">
                    <div class="md-stat-icon md-stat-icon-orange">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                    <span class="md-stat-value">{{ stats.todaysOrders }}</span>
                    <span class="md-stat-label">Bugungi</span>
                </div>
                <div class="md-stat-card">
                    <div class="md-stat-icon md-stat-icon-gold">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </div>
                    <span class="md-stat-value">{{ formatPrice(stats.totalEarnings) }}</span>
                    <span class="md-stat-label">Daromad</span>
                </div>
            </div>
        </section>

        <!-- Rating Summary -->
        <section v-if="ratingSummary.average" class="md-section">
            <div class="md-section-header">
                <h2 class="md-section-title">Reyting</h2>
            </div>
            <div class="md-rating-card">
                <div class="md-rating-big">
                    <span class="md-rating-number">{{ ratingSummary.average }}</span>
                    <div class="md-rating-stars">
                        <template v-for="i in renderStars(ratingSummary.average).full" :key="'f'+i">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#C8A951" stroke="#C8A951" stroke-width="1">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </template>
                        <template v-if="renderStars(ratingSummary.average).half">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="#C8A951" stroke="#C8A951" stroke-width="1" opacity="0.5">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </template>
                        <template v-for="i in renderStars(ratingSummary.average).empty" :key="'e'+i">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="1">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </template>
                    </div>
                    <span class="md-rating-count">{{ ratingSummary.count }} ta baho</span>
                </div>
            </div>
        </section>

        <!-- Upcoming Orders -->
        <section class="md-section">
            <div class="md-section-header">
                <h2 class="md-section-title">Kelgusi buyurtmalar</h2>
                <Link href="/app/orders" class="md-section-link">Barchasi</Link>
            </div>

            <div v-if="upcomingOrders.length === 0" class="md-empty-card">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <p>Hozircha kelgusi buyurtmalar yo'q</p>
            </div>

            <div v-else class="md-orders-list">
                <div v-for="order in upcomingOrders" :key="order.id" class="md-order-card">
                    <div class="md-order-time">
                        <span class="md-order-date">{{ order.booking_date }}</span>
                        <span v-if="order.arrival_time" class="md-order-hour">{{ order.arrival_time }}</span>
                    </div>
                    <div class="md-order-body">
                        <div class="md-order-service">{{ order.service_name }}</div>
                        <div class="md-order-customer">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            {{ order.customer_name }}
                        </div>
                        <span class="md-order-number">{{ order.order_number }}</span>
                    </div>
                    <div class="md-order-status" :style="{ color: statusColor(order.status), background: statusColor(order.status) + '15' }">
                        {{ statusLabel(order.status) }}
                    </div>
                </div>
            </div>
        </section>

        <!-- Recent Ratings -->
        <section v-if="recentRatings.length > 0" class="md-section">
            <div class="md-section-header">
                <h2 class="md-section-title">So'nggi baholar</h2>
                <Link href="/app/ratings" class="md-section-link">Barchasi</Link>
            </div>

            <div class="md-ratings-list">
                <div v-for="rating in recentRatings" :key="rating.id" class="md-rating-item">
                    <div class="md-rating-item-header">
                        <span class="md-rating-customer">{{ rating.customer_name }}</span>
                        <div class="md-rating-item-stars">
                            <template v-for="i in 5" :key="i">
                                <svg width="14" height="14" viewBox="0 0 24 24" :fill="i <= rating.overall_rating ? '#C8A951' : 'none'" :stroke="i <= rating.overall_rating ? '#C8A951' : '#D1D5DB'" stroke-width="1">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </template>
                        </div>
                    </div>
                    <p v-if="rating.feedback" class="md-rating-feedback">{{ rating.feedback }}</p>
                    <span class="md-rating-date">{{ rating.rated_at }}</span>
                </div>
            </div>
        </section>

        <!-- Bottom Navigation -->
        <div class="md-bottom-nav">
            <Link href="/app" class="md-nav-item md-nav-active">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                <span>Bosh sahifa</span>
            </Link>
            <Link href="/app/orders" class="md-nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                <span>Buyurtmalar</span>
            </Link>
            <Link href="/app/ratings" class="md-nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                <span>Reyting</span>
            </Link>
            <Link href="/app/profile" class="md-nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <span>Profil</span>
            </Link>
        </div>
    </div>
</template>

<style scoped>
.md-page {
    --gold: #C8A951;
    --gold-light: #D4B76A;
    --navy: #1B2B5A;
    --cream: #f9fafb;
    --cream-dark: #EDE8DF;
    --text-muted: #8B8680;

    min-height: 100vh;
    background: var(--cream);
    font-family: 'Manrope', -apple-system, BlinkMacSystemFont, sans-serif;
    padding-bottom: 80px;
}

/* Header */
.md-header {
    padding: 16px;
    background: var(--cream);
    position: sticky;
    top: 0;
    z-index: 50;
}

.md-header-top {
    display: flex;
    align-items: center;
    gap: 12px;
}

.md-menu-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: white;
    border: 1px solid rgba(0,0,0,0.06);
    color: var(--navy);
    cursor: pointer;
    transition: all 0.2s;
}

.md-menu-btn:hover {
    background: var(--cream-dark);
}

.md-logo {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--gold);
}

.md-logo span {
    font-family: 'Playfair Display', serif;
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 1.5px;
    color: var(--navy);
}

.md-role-badge {
    padding: 6px 12px;
    background: linear-gradient(135deg, var(--gold), var(--gold-light));
    color: white;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    border-radius: 20px;
}

/* Hero */
.md-hero {
    padding: 24px 16px 32px;
    background: linear-gradient(135deg, var(--navy) 0%, #253672 100%);
    margin: 0 16px;
    border-radius: 20px;
    position: relative;
    overflow: hidden;
}

.md-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -30%;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(200,169,81,0.15) 0%, transparent 70%);
    border-radius: 50%;
}

.md-hero-content {
    position: relative;
    z-index: 1;
}

.md-hero-row {
    display: flex;
    align-items: center;
    gap: 16px;
}

.md-hero-text {
    flex: 1;
}

.md-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: rgba(200,169,81,0.15);
    border: 1px solid rgba(200,169,81,0.3);
    border-radius: 20px;
    margin-bottom: 12px;
}

.md-hero-badge svg {
    color: var(--gold);
}

.md-hero-badge span {
    font-size: 12px;
    font-weight: 600;
    color: var(--gold);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.md-hero-title {
    font-family: 'Playfair Display', serif;
    font-size: 22px;
    font-weight: 600;
    color: white;
    margin: 0 0 6px;
    line-height: 1.2;
}

.md-hero-subtitle {
    font-size: 13px;
    color: rgba(255,255,255,0.65);
    margin: 0;
    line-height: 1.4;
}

.md-hero-avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    background: linear-gradient(135deg, var(--gold), var(--gold-light));
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid rgba(200,169,81,0.4);
}

.md-hero-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.md-hero-avatar span {
    font-size: 24px;
    font-weight: 700;
    color: white;
}

/* Stats */
.md-stats {
    padding: 16px;
    margin-top: -16px;
    position: relative;
    z-index: 10;
}

.md-stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.md-stat-card {
    background: white;
    border-radius: 16px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}

.md-stat-icon {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    margin-bottom: 4px;
}

.md-stat-icon-blue {
    background: rgba(59,130,246,0.1);
    color: #3B82F6;
}

.md-stat-icon-green {
    background: rgba(16,185,129,0.1);
    color: #10B981;
}

.md-stat-icon-orange {
    background: rgba(245,158,11,0.1);
    color: #F59E0B;
}

.md-stat-icon-gold {
    background: rgba(200,169,81,0.15);
    color: var(--gold);
}

.md-stat-value {
    font-family: 'Playfair Display', serif;
    font-size: 22px;
    font-weight: 700;
    color: var(--navy);
    line-height: 1;
}

.md-stat-label {
    font-size: 12px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

/* Sections */
.md-section {
    padding: 0 16px 24px;
}

.md-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}

.md-section-title {
    font-family: 'Playfair Display', serif;
    font-size: 18px;
    font-weight: 600;
    color: var(--navy);
    margin: 0;
}

.md-section-link {
    font-size: 13px;
    font-weight: 600;
    color: var(--gold);
    text-decoration: none;
}

/* Rating Card */
.md-rating-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}

.md-rating-big {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}

.md-rating-number {
    font-family: 'Playfair Display', serif;
    font-size: 40px;
    font-weight: 700;
    color: var(--navy);
    line-height: 1;
}

.md-rating-stars {
    display: flex;
    gap: 2px;
}

.md-rating-count {
    font-size: 13px;
    color: var(--text-muted);
    margin-top: 2px;
}

/* Empty State */
.md-empty-card {
    background: white;
    border-radius: 16px;
    padding: 32px 16px;
    text-align: center;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    color: var(--text-muted);
}

.md-empty-card svg {
    margin-bottom: 12px;
    opacity: 0.5;
}

.md-empty-card p {
    margin: 0;
    font-size: 14px;
}

/* Orders List */
.md-orders-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.md-order-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px;
    background: white;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}

.md-order-time {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
    min-width: 56px;
    padding: 8px;
    background: var(--cream);
    border-radius: 10px;
}

.md-order-date {
    font-size: 11px;
    font-weight: 600;
    color: var(--navy);
}

.md-order-hour {
    font-size: 15px;
    font-weight: 700;
    color: var(--gold);
}

.md-order-body {
    flex: 1;
    min-width: 0;
}

.md-order-service {
    font-size: 14px;
    font-weight: 600;
    color: var(--navy);
    margin-bottom: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.md-order-customer {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    color: var(--text-muted);
    margin-bottom: 3px;
}

.md-order-number {
    font-size: 11px;
    color: var(--text-muted);
    opacity: 0.7;
}

.md-order-status {
    font-size: 11px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 8px;
    white-space: nowrap;
    flex-shrink: 0;
}

/* Recent Ratings */
.md-ratings-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.md-rating-item {
    background: white;
    border-radius: 14px;
    padding: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
}

.md-rating-item-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 6px;
}

.md-rating-customer {
    font-size: 14px;
    font-weight: 600;
    color: var(--navy);
}

.md-rating-item-stars {
    display: flex;
    gap: 1px;
}

.md-rating-feedback {
    font-size: 13px;
    color: #555;
    margin: 0 0 6px;
    line-height: 1.4;
}

.md-rating-date {
    font-size: 11px;
    color: var(--text-muted);
}

/* Bottom Navigation */
.md-bottom-nav {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    display: flex;
    background: white;
    border-top: 1px solid rgba(0,0,0,0.06);
    box-shadow: 0 -4px 20px rgba(0,0,0,0.06);
    z-index: 40;
}

.md-nav-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 10px 4px;
    font-size: 11px;
    font-weight: 500;
    color: var(--text-muted);
    text-decoration: none;
    transition: color 0.2s;
}

.md-nav-item svg {
    transition: color 0.2s;
}

.md-nav-active {
    color: var(--gold);
}

.md-nav-active svg {
    color: var(--gold);
}
</style>
