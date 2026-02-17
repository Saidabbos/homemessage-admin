<script setup>
import { ref, computed, onMounted } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MiniAppLayout from '@/Layouts/MiniAppLayout.vue';

defineOptions({ layout: MiniAppLayout });

const { t } = useI18n();

const props = defineProps({
    services: Array,
    user: Object,
    needsName: Boolean,
});

const tg = ref(null);
const telegramUser = ref(null);

// Name modal state
const showNameModal = ref(false);
const nameInput = ref('');
const nameSaving = ref(false);
const nameError = ref('');

onMounted(() => {
    if (window.Telegram?.WebApp) {
        tg.value = window.Telegram.WebApp;
        telegramUser.value = tg.value.initDataUnsafe?.user;
        tg.value.expand();
    }
    
    // Show name modal if needed
    if (props.needsName) {
        showNameModal.value = true;
        // Pre-fill with Telegram name if available
        if (telegramUser.value?.first_name) {
            nameInput.value = telegramUser.value.first_name;
        }
    }
});

const greeting = computed(() => {
    if (props.user?.name && !props.needsName) {
        return props.user.name;
    }
    if (telegramUser.value?.first_name) {
        return telegramUser.value.first_name;
    }
    return '';
});

const formatPrice = (price) => new Intl.NumberFormat('uz-UZ').format(price);

const logout = () => {
    router.post('/app/logout');
};

const goToBooking = () => {
    router.visit('/app/book');
};

const goToOrders = () => {
    router.visit('/app/orders');
};

const saveName = async () => {
    const name = nameInput.value.trim();
    
    if (name.length < 2) {
        nameError.value = "Ism kamida 2 ta harfdan iborat bo'lishi kerak";
        return;
    }
    
    nameSaving.value = true;
    nameError.value = '';
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const response = await fetch('/app/save-name', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ name }),
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNameModal.value = false;
            // Reload page to update user data
            router.reload();
        } else {
            nameError.value = result.message || 'Xatolik yuz berdi';
        }
    } catch (e) {
        nameError.value = 'Xatolik yuz berdi';
    } finally {
        nameSaving.value = false;
    }
};
</script>

<template>
    <div class="ma-page">
        <!-- Name Input Modal -->
        <div v-if="showNameModal" class="name-modal-overlay">
            <div class="name-modal">
                <div class="name-modal-header">
                    <div class="name-modal-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <h2 class="name-modal-title">Xush kelibsiz!</h2>
                    <p class="name-modal-subtitle">Ismingizni kiriting</p>
                </div>
                <div class="name-modal-body">
                    <div class="name-input-wrapper">
                        <input
                            v-model="nameInput"
                            type="text"
                            class="name-input"
                            :class="{ error: nameError }"
                            placeholder="Ismingiz"
                            maxlength="50"
                            @keyup.enter="saveName"
                        />
                    </div>
                    <p v-if="nameError" class="name-error">{{ nameError }}</p>
                </div>
                <button 
                    class="name-submit-btn" 
                    :disabled="nameSaving || nameInput.trim().length < 2"
                    @click="saveName"
                >
                    {{ nameSaving ? 'Saqlanmoqda...' : 'Davom etish' }}
                </button>
            </div>
        </div>

        <!-- Header -->
        <header class="ma-header">
            <div class="ma-header-top">
                <div class="ma-logo">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m3 4.5a4.5 4.5 0 1 1-4.5-4.5M12 16.5V15m4.5-3a4.5 4.5 0 1 0-4.5-4.5M16.5 12H15"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <span>HOMEMASSAGE</span>
                </div>
                <div class="ma-header-actions">
                    <button class="ma-icon-btn" @click="goToOrders">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                    </button>
                    <Link href="/app/addresses" class="ma-icon-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                    </Link>
                    <Link href="/app/profile" class="ma-icon-btn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </Link>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="ma-hero">
            <div class="ma-hero-content">
                <div class="ma-hero-badge">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/>
                    </svg>
                    <span>Premium xizmat</span>
                </div>
                <h1 class="ma-hero-title">
                    Salom<span v-if="greeting">, {{ greeting }}</span>!
                </h1>
                <p class="ma-hero-subtitle">Uyingizda professional massaj xizmatidan bahramand bo'ling</p>
                
                <button class="ma-hero-cta" @click="goToBooking">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/>
                    </svg>
                    <span>Seans bron qilish</span>
                </button>
            </div>
        </section>

        <!-- Stats -->
        <section class="ma-stats">
            <div class="ma-stat">
                <span class="ma-stat-num">5000+</span>
                <span class="ma-stat-label">Mijozlar</span>
            </div>
            <div class="ma-stat-divider"></div>
            <div class="ma-stat">
                <span class="ma-stat-num">10+</span>
                <span class="ma-stat-label">Masterlar</span>
            </div>
            <div class="ma-stat-divider"></div>
            <div class="ma-stat">
                <span class="ma-stat-num">4.9</span>
                <span class="ma-stat-label">Reyting</span>
            </div>
        </section>

        <!-- Services Section -->
        <section class="ma-services">
            <div class="ma-section-header">
                <div class="ma-section-label">
                    <span class="ma-label-line"></span>
                    <span>XIZMATLAR</span>
                    <span class="ma-label-line"></span>
                </div>
                <h2 class="ma-section-title">Massaj Turlari</h2>
            </div>

            <div class="ma-services-list">
                <div 
                    v-for="service in services" 
                    :key="service.id"
                    class="ma-service-card"
                    @click="goToBooking"
                >
                    <div class="ma-service-img">
                        <img v-if="service.image_url" :src="service.image_url" :alt="service.name" />
                        <div v-else class="ma-service-placeholder">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M18 11V6a2 2 0 0 0-2-2a2 2 0 0 0-2 2"/><path d="M14 10V4a2 2 0 0 0-2-2a2 2 0 0 0-2 2v2"/><path d="M10 10.5V6a2 2 0 0 0-2-2a2 2 0 0 0-2 2v8"/><path d="M18 8a2 2 0 1 1 4 0v6a8 8 0 0 1-8 8h-2c-2.8 0-4.5-.86-5.99-2.34l-3.6-3.6a2 2 0 0 1 2.83-2.82L7 13"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ma-service-body">
                        <h3 class="ma-service-name">{{ service.name }}</h3>
                        <p class="ma-service-desc">{{ service.description }}</p>
                        <div class="ma-service-footer">
                            <span class="ma-service-price">
                                {{ formatPrice(service.durations?.[0]?.price || 0) }} so'm
                            </span>
                            <span class="ma-service-duration">
                                {{ service.durations?.[0]?.duration || 60 }} daqiqa
                            </span>
                        </div>
                    </div>
                    <div class="ma-service-arrow">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m9 18 6-6-6-6"/>
                        </svg>
                    </div>
                </div>
            </div>
        </section>

        <!-- Bottom Actions -->
        <div class="ma-bottom-actions">
            <button class="ma-bottom-btn ma-bottom-orders" @click="goToOrders">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                <span>Mening bronlarim</span>
            </button>
            <button class="ma-bottom-btn ma-bottom-book" @click="goToBooking">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <span>Yangi bron</span>
            </button>
        </div>
    </div>
</template>

<style scoped>
/* Variables */
.ma-page {
    --gold: #C8A951;
    --gold-light: #D4B76A;
    --navy: #1B2B5A;
    --cream: #F5F2ED;
    --cream-dark: #EDE8DF;
    --text-muted: #8B8680;

    min-height: 100vh;
    background: var(--cream);
    font-family: 'Manrope', -apple-system, BlinkMacSystemFont, sans-serif;
    padding-bottom: 100px;
}

/* Header */
.ma-header {
    padding: 16px;
    background: var(--cream);
    position: sticky;
    top: 0;
    z-index: 50;
}

.ma-header-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.ma-logo {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--gold);
}

.ma-logo svg {
    color: var(--gold);
}

.ma-logo span {
    font-family: 'Playfair Display', serif;
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 1.5px;
    color: var(--navy);
}

.ma-header-actions {
    display: flex;
    gap: 8px;
}

.ma-icon-btn {
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

.ma-icon-btn:first-child {
    color: var(--gold);
}

.ma-icon-btn:hover {
    background: var(--cream-dark);
}

.ma-logout {
    color: var(--text-muted);
}

/* Hero */
.ma-hero {
    padding: 24px 16px 32px;
    background: linear-gradient(135deg, var(--navy) 0%, #253672 100%);
    margin: 0 16px;
    border-radius: 20px;
    position: relative;
    overflow: hidden;
}

.ma-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -30%;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(200,169,81,0.15) 0%, transparent 70%);
    border-radius: 50%;
}

.ma-hero-content {
    position: relative;
    z-index: 1;
}

.ma-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: rgba(200,169,81,0.15);
    border: 1px solid rgba(200,169,81,0.3);
    border-radius: 20px;
    margin-bottom: 16px;
}

.ma-hero-badge svg {
    color: var(--gold);
}

.ma-hero-badge span {
    font-size: 12px;
    font-weight: 600;
    color: var(--gold);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.ma-hero-title {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    font-weight: 600;
    color: white;
    margin: 0 0 8px;
    line-height: 1.2;
}

.ma-hero-subtitle {
    font-size: 14px;
    color: rgba(255,255,255,0.7);
    margin: 0 0 24px;
    line-height: 1.5;
}

.ma-hero-cta {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 14px 28px;
    background: var(--gold);
    border: none;
    border-radius: 14px;
    font-size: 15px;
    font-weight: 600;
    color: var(--navy);
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 4px 16px rgba(200,169,81,0.3);
}

.ma-hero-cta:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(200,169,81,0.4);
}

/* Stats */
.ma-stats {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 24px;
    padding: 20px 16px;
    margin: -16px 32px 0;
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    position: relative;
    z-index: 10;
}

.ma-stat {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
}

.ma-stat-num {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    font-weight: 700;
    color: var(--navy);
}

.ma-stat-label {
    font-size: 11px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.ma-stat-divider {
    width: 1px;
    height: 32px;
    background: rgba(0,0,0,0.08);
}

/* Services Section */
.ma-services {
    padding: 32px 16px;
}

.ma-section-header {
    text-align: center;
    margin-bottom: 24px;
}

.ma-section-label {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-bottom: 8px;
}

.ma-section-label span:not(.ma-label-line) {
    font-size: 11px;
    font-weight: 600;
    color: var(--gold);
    letter-spacing: 2px;
}

.ma-label-line {
    width: 24px;
    height: 1px;
    background: var(--gold);
    opacity: 0.5;
}

.ma-section-title {
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    font-weight: 600;
    color: var(--navy);
    margin: 0;
}

/* Service Cards */
.ma-services-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.ma-service-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    cursor: pointer;
    transition: all 0.2s;
}

.ma-service-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.ma-service-img {
    width: 72px;
    height: 72px;
    border-radius: 12px;
    overflow: hidden;
    flex-shrink: 0;
    background: var(--cream-dark);
}

.ma-service-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.ma-service-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--gold);
}

.ma-service-body {
    flex: 1;
    min-width: 0;
}

.ma-service-name {
    font-size: 15px;
    font-weight: 600;
    color: var(--navy);
    margin: 0 0 4px;
}

.ma-service-desc {
    font-size: 12px;
    color: var(--text-muted);
    margin: 0 0 8px;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.ma-service-footer {
    display: flex;
    align-items: center;
    gap: 12px;
}

.ma-service-price {
    font-size: 13px;
    font-weight: 700;
    color: var(--gold);
}

.ma-service-duration {
    font-size: 11px;
    color: var(--text-muted);
    padding: 3px 8px;
    background: var(--cream);
    border-radius: 6px;
}

.ma-service-arrow {
    flex-shrink: 0;
    color: var(--text-muted);
    transition: transform 0.2s;
}

.ma-service-card:hover .ma-service-arrow {
    transform: translateX(4px);
    color: var(--gold);
}

/* Bottom Actions */
.ma-bottom-actions {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    display: flex;
    gap: 12px;
    padding: 16px;
    background: white;
    border-top: 1px solid rgba(0,0,0,0.06);
    box-shadow: 0 -4px 20px rgba(0,0,0,0.06);
}

.ma-bottom-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 14px 16px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.ma-bottom-orders {
    background: var(--cream);
    border: 1px solid rgba(0,0,0,0.06);
    color: var(--navy);
}

.ma-bottom-orders:hover {
    background: var(--cream-dark);
}

.ma-bottom-book {
    background: var(--gold);
    border: none;
    color: var(--navy);
    box-shadow: 0 4px 16px rgba(200,169,81,0.3);
}

.ma-bottom-book:hover {
    background: var(--gold-light);
}

/* Name Modal */
.name-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(27, 43, 90, 0.9);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 24px;
}

.name-modal {
    width: 100%;
    max-width: 340px;
    background: white;
    border-radius: 24px;
    padding: 32px 24px;
    text-align: center;
    animation: modalSlideUp 0.3s ease;
}

@keyframes modalSlideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.name-modal-header {
    margin-bottom: 24px;
}

.name-modal-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--gold), var(--gold-light));
    border-radius: 50%;
    color: white;
}

.name-modal-title {
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    font-weight: 600;
    color: var(--navy);
    margin: 0 0 8px;
}

.name-modal-subtitle {
    font-size: 14px;
    color: var(--text-muted);
    margin: 0;
}

.name-modal-body {
    margin-bottom: 20px;
}

.name-input-wrapper {
    position: relative;
}

.name-input {
    width: 100%;
    padding: 16px 20px;
    font-size: 16px;
    font-family: inherit;
    color: var(--navy);
    background: var(--cream);
    border: 2px solid transparent;
    border-radius: 14px;
    outline: none;
    transition: all 0.2s;
    text-align: center;
}

.name-input:focus {
    background: white;
    border-color: var(--gold);
}

.name-input.error {
    border-color: #EF4444;
}

.name-input::placeholder {
    color: var(--text-muted);
}

.name-error {
    font-size: 13px;
    color: #EF4444;
    margin: 8px 0 0;
}

.name-submit-btn {
    width: 100%;
    padding: 16px;
    font-size: 15px;
    font-weight: 600;
    font-family: inherit;
    background: var(--gold);
    color: var(--navy);
    border: none;
    border-radius: 14px;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 4px 16px rgba(200,169,81,0.3);
}

.name-submit-btn:hover:not(:disabled) {
    background: var(--gold-light);
    transform: translateY(-1px);
}

.name-submit-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}
</style>
