<script setup>
import { ref } from 'vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import MiniAppLayout from '@/Layouts/MiniAppLayout.vue';

defineOptions({ layout: MiniAppLayout });

const props = defineProps({
    user: Object,
});

const form = useForm({
    name: props.user.name || '',
    email: props.user.email || '',
    locale: props.user.locale || 'uz',
});

const submit = () => {
    form.put('/app/profile', {
        preserveScroll: true,
    });
};

const logout = () => {
    router.post('/app/logout');
};

const locales = [
    { value: 'uz', label: "O'zbekcha" },
    { value: 'ru', label: 'Русский' },
    { value: 'en', label: 'English' },
];

// PIN management
const showPinModal = ref(false);
const pinMode = ref('set'); // 'set', 'change', 'remove'
const currentPin = ref('');
const newPin = ref('');
const confirmPin = ref('');
const pinError = ref('');
const pinSaving = ref(false);
const hasPin = ref(props.user.has_pin);

const openPinModal = (mode) => {
    pinMode.value = mode;
    currentPin.value = '';
    newPin.value = '';
    confirmPin.value = '';
    pinError.value = '';
    showPinModal.value = true;
};

const closePinModal = () => {
    showPinModal.value = false;
};

const savePin = async () => {
    pinError.value = '';
    
    // Validation
    if (pinMode.value !== 'remove') {
        if (newPin.value.length !== 4 || !/^\d{4}$/.test(newPin.value)) {
            pinError.value = 'PIN kod 4 ta raqamdan iborat bo\'lishi kerak';
            return;
        }
        if (newPin.value !== confirmPin.value) {
            pinError.value = 'PIN kodlar mos kelmadi';
            return;
        }
    }
    
    if ((pinMode.value === 'change' || pinMode.value === 'remove') && currentPin.value.length !== 4) {
        pinError.value = 'Joriy PIN kodni kiriting';
        return;
    }
    
    pinSaving.value = true;
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        if (pinMode.value === 'remove') {
            const response = await fetch('/app/pin/remove', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ current_pin: currentPin.value }),
            });
            
            const result = await response.json();
            
            if (result.success) {
                hasPin.value = false;
                closePinModal();
            } else {
                pinError.value = result.message || 'Xatolik yuz berdi';
            }
        } else {
            const body = { pin: newPin.value };
            if (pinMode.value === 'change') {
                body.current_pin = currentPin.value;
            }
            
            const response = await fetch('/app/pin/set', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(body),
            });
            
            const result = await response.json();
            
            if (result.success) {
                hasPin.value = true;
                closePinModal();
            } else {
                pinError.value = result.message || 'Xatolik yuz berdi';
            }
        }
    } catch (e) {
        pinError.value = 'Xatolik yuz berdi';
    } finally {
        pinSaving.value = false;
    }
};
</script>

<template>
    <div class="profile-page">
        <!-- Header -->
        <header class="profile-header">
            <Link href="/app" class="back-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 18l-6-6 6-6"/>
                </svg>
            </Link>
            <h1 class="header-title">Profil</h1>
            <div class="header-space"></div>
        </header>

        <!-- Avatar Section -->
        <div class="avatar-section">
            <div class="avatar" v-if="user.telegram_photo_url">
                <img :src="user.telegram_photo_url" :alt="user.name" />
            </div>
            <div class="avatar avatar-placeholder" v-else>
                {{ user.name?.charAt(0) || user.phone?.slice(-2) }}
            </div>
            <h2 class="user-name">{{ user.name || user.phone }}</h2>
            <p class="user-phone">{{ user.phone }}</p>
            <div v-if="user.telegram_username" class="telegram-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                </svg>
                <span>@{{ user.telegram_username }}</span>
            </div>
        </div>

        <!-- Form Section -->
        <form @submit.prevent="submit" class="profile-form">
            <div class="form-card">
                <!-- Name -->
                <div class="form-group">
                    <label class="form-label">Ism</label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="form-input"
                        :class="{ error: form.errors.name }"
                        placeholder="Ismingiz"
                    />
                    <p v-if="form.errors.name" class="error-text">{{ form.errors.name }}</p>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="form-input"
                        :class="{ error: form.errors.email }"
                        placeholder="email@example.com"
                    />
                    <p v-if="form.errors.email" class="error-text">{{ form.errors.email }}</p>
                </div>

                <!-- Phone (readonly) -->
                <div class="form-group">
                    <label class="form-label">Telefon</label>
                    <input
                        :value="user.phone"
                        type="tel"
                        class="form-input readonly"
                        disabled
                    />
                    <p class="help-text">Telefon raqamini o'zgartirish mumkin emas</p>
                </div>

                <!-- Locale -->
                <div class="form-group">
                    <label class="form-label">Til</label>
                    <div class="locale-chips">
                        <button
                            v-for="locale in locales"
                            :key="locale.value"
                            type="button"
                            class="locale-chip"
                            :class="{ selected: form.locale === locale.value }"
                            @click="form.locale = locale.value"
                        >
                            {{ locale.label }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                class="submit-btn"
                :disabled="form.processing"
            >
                <span v-if="form.processing">Saqlanmoqda...</span>
                <span v-else>Saqlash</span>
            </button>
        </form>

        <!-- PIN Section -->
        <div class="pin-section">
            <div class="pin-card">
                <div class="pin-header">
                    <div class="pin-icon" :class="{ active: hasPin }">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </div>
                    <div class="pin-info">
                        <h3 class="pin-title">PIN kod</h3>
                        <p class="pin-desc">
                            {{ hasPin ? 'OTP siz tez kirish' : 'Tez kirish uchun PIN o\'rnating' }}
                        </p>
                    </div>
                </div>
                <div class="pin-actions">
                    <button 
                        v-if="!hasPin" 
                        class="pin-btn pin-btn-primary"
                        @click="openPinModal('set')"
                    >
                        O'rnatish
                    </button>
                    <template v-else>
                        <button class="pin-btn" @click="openPinModal('change')">
                            O'zgartirish
                        </button>
                        <button class="pin-btn pin-btn-danger" @click="openPinModal('remove')">
                            O'chirish
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Quick Links Section -->
        <div class="quick-links-section">
            <!-- Addresses -->
            <Link href="/app/addresses" class="quick-link-card">
                <div class="quick-link-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                </div>
                <div class="quick-link-info">
                    <h3 class="quick-link-title">Manzillarim</h3>
                    <p class="quick-link-desc">Saqlangan manzillarni boshqarish</p>
                </div>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="quick-link-arrow">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </Link>

            <!-- Ratings -->
            <Link href="/app/ratings" class="quick-link-card">
                <div class="quick-link-icon quick-link-icon-gold">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                </div>
                <div class="quick-link-info">
                    <h3 class="quick-link-title">Reytinglar</h3>
                    <p class="quick-link-desc">Berilgan va olingan baholar</p>
                </div>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="quick-link-arrow">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </Link>
        </div>

        <!-- Logout Section -->
        <div class="logout-section">
            <button class="logout-btn" @click="logout">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                <span>Chiqish</span>
            </button>
        </div>

        <!-- PIN Modal -->
        <div v-if="showPinModal" class="pin-modal-overlay" @click.self="closePinModal">
            <div class="pin-modal">
                <div class="pin-modal-header">
                    <h2 class="pin-modal-title">
                        {{ pinMode === 'set' ? 'PIN o\'rnatish' : pinMode === 'change' ? 'PIN o\'zgartirish' : 'PIN o\'chirish' }}
                    </h2>
                    <button class="pin-modal-close" @click="closePinModal">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
                
                <div class="pin-modal-body">
                    <!-- Current PIN (for change/remove) -->
                    <div v-if="pinMode === 'change' || pinMode === 'remove'" class="pin-input-group">
                        <label class="pin-input-label">Joriy PIN kod</label>
                        <input
                            v-model="currentPin"
                            type="password"
                            inputmode="numeric"
                            maxlength="4"
                            class="pin-input"
                            placeholder="••••"
                        />
                    </div>
                    
                    <!-- New PIN (for set/change) -->
                    <template v-if="pinMode !== 'remove'">
                        <div class="pin-input-group">
                            <label class="pin-input-label">{{ pinMode === 'change' ? 'Yangi PIN kod' : 'PIN kod' }}</label>
                            <input
                                v-model="newPin"
                                type="password"
                                inputmode="numeric"
                                maxlength="4"
                                class="pin-input"
                                placeholder="••••"
                            />
                        </div>
                        
                        <div class="pin-input-group">
                            <label class="pin-input-label">PIN kodni tasdiqlang</label>
                            <input
                                v-model="confirmPin"
                                type="password"
                                inputmode="numeric"
                                maxlength="4"
                                class="pin-input"
                                placeholder="••••"
                            />
                        </div>
                    </template>
                    
                    <p v-if="pinError" class="pin-error">{{ pinError }}</p>
                </div>
                
                <div class="pin-modal-footer">
                    <button class="pin-modal-cancel" @click="closePinModal">Bekor qilish</button>
                    <button 
                        class="pin-modal-submit"
                        :class="{ danger: pinMode === 'remove' }"
                        :disabled="pinSaving"
                        @click="savePin"
                    >
                        {{ pinSaving ? '...' : pinMode === 'remove' ? 'O\'chirish' : 'Saqlash' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        <div v-if="$page.props.flash?.success" class="toast-success">
            {{ $page.props.flash.success }}
        </div>
    </div>
</template>

<style scoped>
.profile-page {
    --navy: #1B2B5A;
    --gold: #C8A951;
    --cream: #F5F2ED;
    --cream-dark: #EDE8DF;
    --text-muted: #8B8680;

    min-height: 100vh;
    background: var(--cream);
    font-family: 'Manrope', -apple-system, sans-serif;
    padding-bottom: 40px;
}

/* Header */
.profile-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: white;
    border-bottom: 1px solid rgba(0,0,0,0.06);
    position: sticky;
    top: 0;
    z-index: 50;
}

.back-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--cream);
    border: none;
    border-radius: 12px;
    color: var(--navy);
    cursor: pointer;
    text-decoration: none;
}

.header-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--navy);
}

.header-space {
    width: 40px;
}

/* Avatar Section */
.avatar-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 24px 16px;
    background: white;
    border-bottom: 1px solid rgba(0,0,0,0.06);
}

.avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    overflow: hidden;
    margin-bottom: 12px;
}

.avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--gold), #D4B96A);
    color: white;
    font-size: 28px;
    font-weight: 600;
}

.user-name {
    font-size: 18px;
    font-weight: 600;
    color: var(--navy);
    margin: 0;
}

.user-phone {
    font-size: 14px;
    color: var(--text-muted);
    margin: 4px 0 0;
}

.telegram-badge {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 8px;
    padding: 4px 12px;
    background: #E3F2FD;
    border-radius: 20px;
    font-size: 12px;
    color: #1976D2;
}

/* Form */
.profile-form {
    padding: 16px;
}

.form-card {
    background: white;
    border-radius: 16px;
    padding: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.form-group {
    margin-bottom: 16px;
}

.form-group:last-child {
    margin-bottom: 0;
}

.form-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--navy);
    margin-bottom: 8px;
}

.form-input {
    width: 100%;
    padding: 12px 14px;
    background: var(--cream);
    border: 1px solid transparent;
    border-radius: 10px;
    font-size: 14px;
    font-family: inherit;
    color: var(--navy);
    transition: all 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: var(--gold);
    background: white;
}

.form-input.error {
    border-color: #EF4444;
}

.form-input.readonly {
    opacity: 0.6;
    cursor: not-allowed;
}

.error-text {
    font-size: 12px;
    color: #EF4444;
    margin: 4px 0 0;
}

.help-text {
    font-size: 12px;
    color: var(--text-muted);
    margin: 4px 0 0;
}

/* Locale Chips */
.locale-chips {
    display: flex;
    gap: 8px;
}

.locale-chip {
    flex: 1;
    padding: 10px;
    background: var(--cream);
    border: 1px solid transparent;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 500;
    color: var(--navy);
    cursor: pointer;
    transition: all 0.2s;
}

.locale-chip:hover {
    border-color: var(--gold);
}

.locale-chip.selected {
    background: var(--gold);
    color: white;
}

/* Submit Button */
.submit-btn {
    width: 100%;
    margin-top: 16px;
    padding: 14px;
    background: var(--gold);
    border: none;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    color: white;
    cursor: pointer;
    transition: all 0.2s;
}

.submit-btn:hover:not(:disabled) {
    background: #B8993F;
}

.submit-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Quick Links Section */
.quick-links-section {
    padding: 16px;
    padding-top: 8px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.quick-link-card {
    display: flex;
    align-items: center;
    gap: 14px;
    background: white;
    border-radius: 14px;
    padding: 16px;
    text-decoration: none;
    transition: all 0.2s;
}

.quick-link-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.quick-link-icon {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(200, 169, 81, 0.15) 0%, rgba(200, 169, 81, 0.05) 100%);
    border-radius: 12px;
    color: #C8A951;
}

.quick-link-icon-gold {
    background: linear-gradient(135deg, #C8A951 0%, #B8993F 100%);
    color: white;
}

.quick-link-info {
    flex: 1;
}

.quick-link-title {
    font-size: 15px;
    font-weight: 600;
    color: #1a1a2e;
    margin: 0 0 2px;
}

.quick-link-desc {
    font-size: 13px;
    color: #6b7280;
    margin: 0;
}

.quick-link-arrow {
    color: #9ca3af;
}

/* Logout Section */
.logout-section {
    padding: 16px;
    padding-top: 8px;
}

.logout-btn {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 14px;
    background: white;
    border: 1px solid #FEE2E2;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 500;
    color: #EF4444;
    cursor: pointer;
    transition: all 0.2s;
}

.logout-btn:hover {
    background: #FEF2F2;
}

/* Toast */
.toast-success {
    position: fixed;
    bottom: 80px;
    left: 16px;
    right: 16px;
    padding: 12px 16px;
    background: #10B981;
    border-radius: 12px;
    color: white;
    font-size: 14px;
    font-weight: 500;
    text-align: center;
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* PIN Section */
.pin-section {
    padding: 0 16px 8px;
}

.pin-card {
    background: white;
    border-radius: 16px;
    padding: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.pin-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.pin-icon {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--cream);
    border-radius: 12px;
    color: var(--text-muted);
}

.pin-icon.active {
    background: #DCFCE7;
    color: #16A34A;
}

.pin-info {
    flex: 1;
}

.pin-title {
    font-size: 15px;
    font-weight: 600;
    color: var(--navy);
    margin: 0;
}

.pin-desc {
    font-size: 12px;
    color: var(--text-muted);
    margin: 2px 0 0;
}

.pin-actions {
    display: flex;
    gap: 8px;
}

.pin-btn {
    flex: 1;
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 500;
    background: var(--cream);
    border: none;
    border-radius: 10px;
    color: var(--navy);
    cursor: pointer;
    transition: all 0.2s;
}

.pin-btn:hover {
    background: var(--cream-dark);
}

.pin-btn-primary {
    background: var(--gold);
    color: white;
}

.pin-btn-primary:hover {
    background: #B8993F;
}

.pin-btn-danger {
    color: #EF4444;
}

.pin-btn-danger:hover {
    background: #FEF2F2;
}

/* PIN Modal */
.pin-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: flex-end;
    justify-content: center;
    z-index: 100;
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.pin-modal {
    width: 100%;
    max-width: 400px;
    background: white;
    border-radius: 20px 20px 0 0;
    animation: slideUp 0.3s ease;
}

.pin-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 20px 0;
}

.pin-modal-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--navy);
    margin: 0;
}

.pin-modal-close {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--cream);
    border: none;
    border-radius: 8px;
    color: var(--text-muted);
    cursor: pointer;
}

.pin-modal-body {
    padding: 20px;
}

.pin-input-group {
    margin-bottom: 16px;
}

.pin-input-group:last-child {
    margin-bottom: 0;
}

.pin-input-label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: var(--navy);
    margin-bottom: 8px;
}

.pin-input {
    width: 100%;
    padding: 14px 16px;
    font-size: 24px;
    font-family: inherit;
    letter-spacing: 8px;
    text-align: center;
    background: var(--cream);
    border: 2px solid transparent;
    border-radius: 12px;
    color: var(--navy);
    outline: none;
    transition: all 0.2s;
}

.pin-input:focus {
    background: white;
    border-color: var(--gold);
}

.pin-error {
    font-size: 13px;
    color: #EF4444;
    margin: 12px 0 0;
    text-align: center;
}

.pin-modal-footer {
    display: flex;
    gap: 12px;
    padding: 0 20px 20px;
}

.pin-modal-cancel,
.pin-modal-submit {
    flex: 1;
    padding: 14px;
    font-size: 15px;
    font-weight: 600;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.pin-modal-cancel {
    background: var(--cream);
    color: var(--navy);
}

.pin-modal-cancel:hover {
    background: var(--cream-dark);
}

.pin-modal-submit {
    background: var(--gold);
    color: white;
}

.pin-modal-submit:hover:not(:disabled) {
    background: #B8993F;
}

.pin-modal-submit.danger {
    background: #EF4444;
}

.pin-modal-submit.danger:hover:not(:disabled) {
    background: #DC2626;
}

.pin-modal-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
