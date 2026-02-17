<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import MiniAppLayout from '@/Layouts/MiniAppLayout.vue';
import SidebarMenu from '@/Components/MiniApp/SidebarMenu.vue';

defineOptions({ layout: MiniAppLayout });

const props = defineProps({
    ratings: Object,
    tab: { type: String, default: 'given' },
    summary: { type: Object, default: () => ({ average: null, count: 0 }) },
    counts: { type: Object, default: () => ({ received: 0, given: 0 }) },
    pendingOrders: { type: Array, default: () => [] },
    user: Object,
});

const showSidebar = ref(false);

const activeTab = ref(props.tab);

const switchTab = (tab) => {
    activeTab.value = tab;
    router.get('/app/ratings', { tab }, { preserveState: true, preserveScroll: true });
};

const goToPage = (url) => {
    if (url) router.get(url, {}, { preserveState: true, preserveScroll: true });
};

const rateOrder = (orderId) => {
    router.post(`/app/orders/${orderId}/rate`);
};
</script>

<template>
    <div class="ratings-page">
        <!-- Sidebar Menu -->
        <SidebarMenu :show="showSidebar" :user="user" @close="showSidebar = false" />

        <!-- Header -->
        <header class="ratings-header">
            <button class="menu-btn" @click="showSidebar = true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <line x1="3" y1="12" x2="21" y2="12"/>
                    <line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>
            <h1 class="header-title">Reytinglar</h1>
            <Link href="/app" class="home-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                    <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
            </Link>
        </header>

        <!-- Summary Card -->
        <div class="summary-card">
            <div class="summary-rating">
                <span class="summary-num">{{ summary.average ?? '-' }}</span>
                <div class="summary-stars">
                    <svg v-for="s in 5" :key="s" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" 
                        :fill="s <= Math.round(summary.average || 0) ? '#C8A951' : '#3f3f46'" 
                        :stroke="s <= Math.round(summary.average || 0) ? '#C8A951' : '#3f3f46'" stroke-width="1">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                </div>
            </div>
            <span class="summary-count">{{ summary.count }} ta baho</span>
        </div>

        <!-- Pending Orders to Rate -->
        <div v-if="pendingOrders.length > 0" class="pending-section">
            <h3 class="section-title">Baholash kutilmoqda</h3>
            <div class="pending-list">
                <div v-for="order in pendingOrders" :key="order.id" class="pending-card">
                    <div class="pending-avatar">
                        <img v-if="order.master_photo" :src="order.master_photo" :alt="order.master_name" />
                        <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <div class="pending-info">
                        <span class="pending-name">{{ order.master_name }}</span>
                        <span class="pending-service">{{ order.service_name }} · {{ order.completed_at }}</span>
                    </div>
                    <button class="rate-btn" @click="rateOrder(order.id)">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                        Baholash
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab" :class="{ active: activeTab === 'given' }" @click="switchTab('given')">
                Bergan ({{ counts.given }})
            </button>
            <button class="tab" :class="{ active: activeTab === 'received' }" @click="switchTab('received')">
                Olgan ({{ counts.received }})
            </button>
        </div>

        <!-- Ratings List -->
        <div class="ratings-content">
            <div v-if="ratings.data.length > 0" class="ratings-list">
                <div v-for="rating in ratings.data" :key="rating.id" class="rating-card">
                    <div class="rating-top">
                        <div class="rating-left">
                            <div class="rating-avatar">
                                <img v-if="rating.master_photo" :src="rating.master_photo" :alt="rating.master_name" />
                                <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                                </svg>
                            </div>
                            <div>
                                <span class="rating-name">{{ rating.master_name }}</span>
                                <span class="rating-meta">{{ rating.service_name }} · #{{ rating.order_number }}</span>
                            </div>
                        </div>
                        <span class="rating-date">{{ rating.rated_at }}</span>
                    </div>

                    <div class="rating-stars">
                        <div class="stars">
                            <svg v-for="s in 5" :key="s" width="14" height="14" viewBox="0 0 24 24" 
                                :fill="s <= rating.overall_rating ? '#C8A951' : '#3f3f46'" stroke-width="1">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                        <div v-if="rating.punctuality_rating" class="sub-ratings">
                            <span>Vaqtdanlik: {{ rating.punctuality_rating }}/5</span>
                            <span>Professional: {{ rating.professionalism_rating }}/5</span>
                            <span>Tozalik: {{ rating.cleanliness_rating }}/5</span>
                        </div>
                    </div>

                    <p v-if="rating.feedback" class="rating-feedback">{{ rating.feedback }}</p>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                <p>{{ activeTab === 'given' ? 'Siz hali hech kimni baholamagansiz' : 'Sizni hali hech kim baholamagan' }}</p>
            </div>

            <!-- Pagination -->
            <div v-if="ratings.data.length && ratings.last_page > 1" class="pagination">
                <button class="pag-btn" :disabled="!ratings.prev_page_url" @click="goToPage(ratings.prev_page_url)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                </button>
                <span class="pag-info">{{ ratings.current_page }} / {{ ratings.last_page }}</span>
                <button class="pag-btn" :disabled="!ratings.next_page_url" @click="goToPage(ratings.next_page_url)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.ratings-page {
    --gold: #C8A951;
    --navy: #1B2B5A;
    --bg: #18181b;
    --card: #27272a;
    --border: #3f3f46;
    --text: #fafafa;
    --text-muted: #a1a1aa;

    min-height: 100vh;
    background: var(--bg);
    font-family: 'Manrope', -apple-system, sans-serif;
    color: var(--text);
    padding-bottom: 40px;
}

/* Header */
.ratings-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: var(--card);
    border-bottom: 1px solid var(--border);
    position: sticky;
    top: 0;
    z-index: 50;
}

.menu-btn,
.home-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg);
    border: none;
    border-radius: 12px;
    color: var(--text);
    text-decoration: none;
    cursor: pointer;
}

.header-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--text);
}

/* Summary Card */
.summary-card {
    margin: 16px;
    padding: 20px;
    background: linear-gradient(135deg, var(--gold) 0%, #B8993F 100%);
    border-radius: 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.summary-rating {
    display: flex;
    align-items: center;
    gap: 12px;
}

.summary-num {
    font-size: 32px;
    font-weight: 700;
    color: var(--navy);
}

.summary-stars {
    display: flex;
    gap: 4px;
}

.summary-count {
    font-size: 14px;
    color: var(--navy);
    opacity: 0.8;
}

/* Pending Section */
.pending-section {
    padding: 0 16px 16px;
}

.section-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-muted);
    margin-bottom: 12px;
}

.pending-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.pending-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: var(--card);
    border-radius: 12px;
    border: 1px solid var(--border);
}

.pending-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    background: var(--bg);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.pending-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.pending-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}

.pending-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pending-service {
    font-size: 12px;
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.rate-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    background: var(--gold);
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    color: var(--navy);
    cursor: pointer;
    white-space: nowrap;
}

/* Tabs */
.tabs {
    display: flex;
    gap: 8px;
    padding: 0 16px;
    margin-bottom: 16px;
}

.tab {
    flex: 1;
    padding: 10px;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    color: var(--text-muted);
    cursor: pointer;
    transition: all 0.2s;
}

.tab.active {
    background: var(--gold);
    border-color: var(--gold);
    color: var(--navy);
}

/* Ratings Content */
.ratings-content {
    padding: 0 16px;
}

.ratings-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.rating-card {
    padding: 14px;
    background: var(--card);
    border-radius: 14px;
    border: 1px solid var(--border);
}

.rating-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 12px;
}

.rating-left {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}

.rating-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    overflow: hidden;
    background: var(--bg);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.rating-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.rating-name {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: var(--text);
}

.rating-meta {
    display: block;
    font-size: 12px;
    color: var(--text-muted);
}

.rating-date {
    font-size: 12px;
    color: var(--text-muted);
    white-space: nowrap;
}

.rating-stars {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 8px;
}

.stars {
    display: flex;
    gap: 3px;
}

.sub-ratings {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    font-size: 11px;
    color: var(--text-muted);
}

.rating-feedback {
    font-size: 13px;
    color: var(--text);
    line-height: 1.5;
    margin: 0;
    padding-top: 8px;
    border-top: 1px solid var(--border);
}

/* Empty State */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 48px 16px;
    color: var(--text-muted);
    text-align: center;
}

.empty-state svg {
    margin-bottom: 12px;
    opacity: 0.5;
}

.empty-state p {
    font-size: 14px;
    margin: 0;
}

/* Pagination */
.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 20px 0;
}

.pag-btn {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 10px;
    color: var(--text);
    cursor: pointer;
}

.pag-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.pag-info {
    font-size: 13px;
    color: var(--text-muted);
}
</style>
