<script setup>
import { ref } from 'vue'
import { useForm, Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
    user: Object,
})

const form = useForm({
    name: props.user.name || '',
    email: props.user.email || '',
    locale: props.user.locale || 'uz',
})

const submit = () => {
    form.put('/customer/profile', {
        preserveScroll: true,
    })
}

const logout = () => {
    router.post('/customer/logout')
}

// PIN management
const showPinModal = ref(false)
const pinMode = ref('set') // 'set', 'change', 'remove'
const currentPin = ref('')
const newPin = ref('')
const confirmPin = ref('')
const pinError = ref('')
const pinSaving = ref(false)
const hasPin = ref(props.user.has_pin)

const openPinModal = (mode) => {
    pinMode.value = mode
    currentPin.value = ''
    newPin.value = ''
    confirmPin.value = ''
    pinError.value = ''
    showPinModal.value = true
}

const closePinModal = () => {
    showPinModal.value = false
}

const savePin = async () => {
    pinError.value = ''
    
    // Validation
    if (pinMode.value !== 'remove') {
        if (newPin.value.length !== 4 || !/^\d{4}$/.test(newPin.value)) {
            pinError.value = 'PIN kod 4 ta raqamdan iborat bo\'lishi kerak'
            return
        }
        if (newPin.value !== confirmPin.value) {
            pinError.value = 'PIN kodlar mos kelmadi'
            return
        }
    }
    
    if ((pinMode.value === 'change' || pinMode.value === 'remove') && currentPin.value.length !== 4) {
        pinError.value = 'Joriy PIN kodni kiriting'
        return
    }
    
    pinSaving.value = true
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
        
        if (pinMode.value === 'remove') {
            const response = await fetch('/customer/pin/remove', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ current_pin: currentPin.value }),
            })
            
            const result = await response.json()
            
            if (result.success) {
                hasPin.value = false
                closePinModal()
            } else {
                pinError.value = result.message || 'Xatolik yuz berdi'
            }
        } else {
            const body = { pin: newPin.value }
            if (pinMode.value === 'change') {
                body.current_pin = currentPin.value
            }
            
            const response = await fetch('/customer/pin/set', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(body),
            })
            
            const result = await response.json()
            
            if (result.success) {
                hasPin.value = true
                closePinModal()
            } else {
                pinError.value = result.message || 'Xatolik yuz berdi'
            }
        }
    } catch (e) {
        pinError.value = 'Xatolik yuz berdi'
    } finally {
        pinSaving.value = false
    }
}
</script>

<template>
    <Head :title="t('profile.title')" />

    <div class="cd-page">
        <!-- Sidebar -->
        <aside class="cd-sidebar">
            <div class="cd-sidebar-top">
                <Link href="/" class="cd-sidebar-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m3 4.5a4.5 4.5 0 1 1-4.5-4.5M12 16.5V15m4.5-3a4.5 4.5 0 1 0-4.5-4.5M16.5 12H15"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <span>HOMEMASSAGE</span>
                </Link>

                <nav class="cd-nav">
                    <Link href="/customer/dashboard" class="cd-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                        <span>{{ t('customer.navDashboard') }}</span>
                    </Link>
                    <Link href="/customer/orders" class="cd-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>{{ t('customer.navBookings') }}</span>
                    </Link>
                    <Link href="/masters" class="cd-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span>{{ t('customer.navMasters') }}</span>
                    </Link>
                    <Link href="/customer/ratings" class="cd-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <span>{{ t('customer.navRatings') }}</span>
                    </Link>
                    <Link href="/customer/favorites" class="cd-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                        <span>{{ t('customer.navFavorites') }}</span>
                    </Link>
                    <Link href="/customer/addresses" class="cd-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span>{{ t('customer.navAddresses') }}</span>
                    </Link>
                    <Link href="/customer/profile" class="cd-nav-item active">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>{{ t('customer.navProfile') }}</span>
                    </Link>
                </nav>
            </div>

            <div class="cd-sidebar-bottom">
                <div class="cd-sidebar-divider"></div>
                <div class="cd-user-profile">
                    <Link href="/customer/profile" class="cd-user-link">
                        <div class="cd-user-avatar">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <div class="cd-user-info">
                            <span class="cd-user-name">{{ user.name || user.phone }}</span>
                            <span class="cd-user-phone">{{ user.phone }}</span>
                        </div>
                    </Link>
                    <button class="cd-logout-btn" @click="logout" :title="t('customer.logout')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Area -->
        <main class="cd-main">
            <!-- Top Bar -->
            <div class="cd-topbar">
                <div class="cd-topbar-left">
                    <h1 class="cd-greeting">{{ t('profile.title') }}</h1>
                    <p class="cd-date">{{ t('profile.subtitle') }}</p>
                </div>
            </div>

            <!-- Content Area -->
            <div class="cd-content cd-content-single">
                <div class="cd-profile-container">
                    <!-- Profile Card -->
                    <div class="cd-card">
                        <!-- Avatar Header -->
                        <div class="cd-profile-header">
                            <div class="cd-profile-avatar">
                                {{ user.name?.charAt(0) || user.phone?.slice(-2) }}
                            </div>
                            <div class="cd-profile-info">
                                <h2 class="cd-profile-name">{{ user.name || user.phone }}</h2>
                                <p class="cd-profile-phone">{{ user.phone }}</p>
                            </div>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="submit" class="cd-profile-form">
                            <!-- Name -->
                            <div class="cd-form-group">
                                <label class="cd-form-label">{{ t('profile.name') }}</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="cd-form-input"
                                    :class="{ 'cd-form-error': form.errors.name }"
                                    :placeholder="t('profile.name_placeholder')"
                                />
                                <p v-if="form.errors.name" class="cd-error-text">{{ form.errors.name }}</p>
                            </div>

                            <!-- Email -->
                            <div class="cd-form-group">
                                <label class="cd-form-label">{{ t('profile.email') }}</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="cd-form-input"
                                    :class="{ 'cd-form-error': form.errors.email }"
                                    :placeholder="t('profile.email_placeholder')"
                                />
                                <p v-if="form.errors.email" class="cd-error-text">{{ form.errors.email }}</p>
                            </div>

                            <!-- Phone (readonly) -->
                            <div class="cd-form-group">
                                <label class="cd-form-label">{{ t('profile.phone') }}</label>
                                <input
                                    :value="user.phone"
                                    type="tel"
                                    disabled
                                    class="cd-form-input cd-form-disabled"
                                />
                                <p class="cd-hint-text">{{ t('profile.phone_hint') }}</p>
                            </div>

                            <!-- Language -->
                            <div class="cd-form-group">
                                <label class="cd-form-label">{{ t('profile.language') }}</label>
                                <select v-model="form.locale" class="cd-form-select">
                                    <option value="uz">O'zbekcha</option>
                                    <option value="ru">Русский</option>
                                    <option value="en">English</option>
                                </select>
                            </div>

                            <!-- Submit Button -->
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="cd-btn-primary"
                            >
                                <span v-if="form.processing">{{ t('common.saving') }}...</span>
                                <span v-else>{{ t('profile.save') }}</span>
                            </button>
                        </form>
                    </div>

                    <!-- PIN Card -->
                    <div class="cd-card cd-pin-card">
                        <div class="cd-pin-header">
                            <div class="cd-pin-icon" :class="{ active: hasPin }">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </div>
                            <div class="cd-pin-info">
                                <h3 class="cd-pin-title">PIN kod</h3>
                                <p class="cd-pin-desc">
                                    {{ hasPin ? 'OTP siz tez kirish' : 'Tez kirish uchun PIN o\'rnating' }}
                                </p>
                            </div>
                        </div>
                        <div class="cd-pin-actions">
                            <button 
                                v-if="!hasPin" 
                                class="cd-pin-btn cd-pin-btn-primary"
                                @click="openPinModal('set')"
                            >
                                O'rnatish
                            </button>
                            <template v-else>
                                <button class="cd-pin-btn" @click="openPinModal('change')">
                                    O'zgartirish
                                </button>
                                <button class="cd-pin-btn cd-pin-btn-danger" @click="openPinModal('remove')">
                                    O'chirish
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Logout Card -->
                    <div class="cd-card cd-logout-card">
                        <button @click="logout" class="cd-btn-logout">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                            {{ t('public.nav.logout') }}
                        </button>
                    </div>
                </div>
            </div>
        </main>

        <!-- PIN Modal -->
        <div v-if="showPinModal" class="cd-modal-overlay" @click.self="closePinModal">
            <div class="cd-modal">
                <div class="cd-modal-header">
                    <h2 class="cd-modal-title">
                        {{ pinMode === 'set' ? 'PIN o\'rnatish' : pinMode === 'change' ? 'PIN o\'zgartirish' : 'PIN o\'chirish' }}
                    </h2>
                    <button class="cd-modal-close" @click="closePinModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>
                
                <div class="cd-modal-body">
                    <!-- Current PIN (for change/remove) -->
                    <div v-if="pinMode === 'change' || pinMode === 'remove'" class="cd-form-group">
                        <label class="cd-form-label">Joriy PIN kod</label>
                        <input
                            v-model="currentPin"
                            type="password"
                            inputmode="numeric"
                            maxlength="4"
                            class="cd-form-input cd-pin-input"
                            placeholder="••••"
                        />
                    </div>
                    
                    <!-- New PIN (for set/change) -->
                    <template v-if="pinMode !== 'remove'">
                        <div class="cd-form-group">
                            <label class="cd-form-label">{{ pinMode === 'change' ? 'Yangi PIN kod' : 'PIN kod' }}</label>
                            <input
                                v-model="newPin"
                                type="password"
                                inputmode="numeric"
                                maxlength="4"
                                class="cd-form-input cd-pin-input"
                                placeholder="••••"
                            />
                        </div>
                        
                        <div class="cd-form-group">
                            <label class="cd-form-label">PIN kodni tasdiqlang</label>
                            <input
                                v-model="confirmPin"
                                type="password"
                                inputmode="numeric"
                                maxlength="4"
                                class="cd-form-input cd-pin-input"
                                placeholder="••••"
                            />
                        </div>
                    </template>
                    
                    <p v-if="pinError" class="cd-error-text" style="text-align: center; margin-top: 12px;">{{ pinError }}</p>
                </div>
                
                <div class="cd-modal-footer">
                    <button class="cd-modal-cancel" @click="closePinModal">Bekor qilish</button>
                    <button 
                        class="cd-modal-submit"
                        :class="{ danger: pinMode === 'remove' }"
                        :disabled="pinSaving"
                        @click="savePin"
                    >
                        {{ pinSaving ? '...' : pinMode === 'remove' ? 'O\'chirish' : 'Saqlash' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Profile-specific styles */
.cd-content-single {
    grid-template-columns: 1fr !important;
    max-width: 600px;
}

.cd-profile-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.cd-profile-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding-bottom: 24px;
    border-bottom: 1px solid #F0EDE8;
    margin-bottom: 24px;
}

.cd-profile-avatar {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: linear-gradient(135deg, #C8A951, #B8963E);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: 700;
    color: #fff;
    flex-shrink: 0;
}

.cd-profile-info {
    flex: 1;
}

.cd-profile-name {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    font-weight: 600;
    color: #1B2B5A;
    margin: 0 0 4px;
}

.cd-profile-phone {
    font-size: 14px;
    color: #8B8680;
    margin: 0;
}

/* Form Styles */
.cd-profile-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.cd-form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.cd-form-label {
    font-size: 14px;
    font-weight: 600;
    color: #1B2B5A;
}

.cd-form-input,
.cd-form-select {
    width: 100%;
    padding: 12px 16px;
    background: #FAFAF8;
    border: 1px solid #E8E5E0;
    border-radius: 10px;
    font-size: 15px;
    color: #1B2B5A;
    transition: all 0.2s ease;
}

.cd-form-input:focus,
.cd-form-select:focus {
    outline: none;
    border-color: #C8A951;
    box-shadow: 0 0 0 3px rgba(200, 169, 81, 0.1);
}

.cd-form-input::placeholder {
    color: #C8C3BC;
}

.cd-form-disabled {
    background: #F5F2ED;
    color: #8B8680;
    cursor: not-allowed;
}

.cd-form-error {
    border-color: #E53E3E;
}

.cd-form-select {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%238B8680' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    padding-right: 44px;
}

.cd-error-text {
    font-size: 13px;
    color: #E53E3E;
    margin: 0;
}

.cd-hint-text {
    font-size: 12px;
    color: #8B8680;
    margin: 0;
}

/* Buttons */
.cd-btn-primary {
    width: 100%;
    padding: 14px 24px;
    background: linear-gradient(135deg, #C8A951, #B8963E);
    color: #fff;
    font-size: 15px;
    font-weight: 600;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-top: 8px;
}

.cd-btn-primary:hover {
    background: linear-gradient(135deg, #B8963E, #A88530);
    transform: translateY(-1px);
}

.cd-btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.cd-logout-card {
    padding: 16px 24px;
}

.cd-btn-logout {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 14px 24px;
    background: #FEF2F2;
    border: 1px solid #FECACA;
    color: #DC2626;
    font-size: 15px;
    font-weight: 600;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.cd-btn-logout:hover {
    background: #FEE2E2;
    border-color: #FCA5A5;
}

/* PIN Card */
.cd-pin-card {
    padding: 20px 24px;
}

.cd-pin-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 16px;
}

.cd-pin-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #F5F2ED;
    border-radius: 12px;
    color: #8B8680;
}

.cd-pin-icon.active {
    background: #DCFCE7;
    color: #16A34A;
}

.cd-pin-info {
    flex: 1;
}

.cd-pin-title {
    font-size: 16px;
    font-weight: 600;
    color: #1B2B5A;
    margin: 0;
}

.cd-pin-desc {
    font-size: 13px;
    color: #8B8680;
    margin: 4px 0 0;
}

.cd-pin-actions {
    display: flex;
    gap: 10px;
}

.cd-pin-btn {
    flex: 1;
    padding: 12px 16px;
    font-size: 14px;
    font-weight: 500;
    background: #F5F2ED;
    border: none;
    border-radius: 10px;
    color: #1B2B5A;
    cursor: pointer;
    transition: all 0.2s;
}

.cd-pin-btn:hover {
    background: #EDE8DF;
}

.cd-pin-btn-primary {
    background: linear-gradient(135deg, #C8A951, #B8963E);
    color: white;
}

.cd-pin-btn-primary:hover {
    background: linear-gradient(135deg, #B8963E, #A88530);
}

.cd-pin-btn-danger {
    color: #DC2626;
}

.cd-pin-btn-danger:hover {
    background: #FEF2F2;
}

/* Modal */
.cd-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 20px;
}

.cd-modal {
    width: 100%;
    max-width: 400px;
    background: white;
    border-radius: 20px;
    overflow: hidden;
}

.cd-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid #F0EDE8;
}

.cd-modal-title {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    font-weight: 600;
    color: #1B2B5A;
    margin: 0;
}

.cd-modal-close {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #F5F2ED;
    border: none;
    border-radius: 10px;
    color: #8B8680;
    cursor: pointer;
    transition: all 0.2s;
}

.cd-modal-close:hover {
    background: #EDE8DF;
    color: #1B2B5A;
}

.cd-modal-body {
    padding: 24px;
}

.cd-pin-input {
    text-align: center;
    font-size: 24px;
    letter-spacing: 8px;
}

.cd-modal-footer {
    display: flex;
    gap: 12px;
    padding: 0 24px 24px;
}

.cd-modal-cancel,
.cd-modal-submit {
    flex: 1;
    padding: 14px;
    font-size: 15px;
    font-weight: 600;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s;
}

.cd-modal-cancel {
    background: #F5F2ED;
    color: #1B2B5A;
}

.cd-modal-cancel:hover {
    background: #EDE8DF;
}

.cd-modal-submit {
    background: linear-gradient(135deg, #C8A951, #B8963E);
    color: white;
}

.cd-modal-submit:hover:not(:disabled) {
    background: linear-gradient(135deg, #B8963E, #A88530);
}

.cd-modal-submit.danger {
    background: #DC2626;
}

.cd-modal-submit.danger:hover:not(:disabled) {
    background: #B91C1C;
}

.cd-modal-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Mobile */
@media (max-width: 768px) {
    .cd-content-single {
        max-width: none;
    }

    .cd-profile-header {
        flex-direction: column;
        text-align: center;
    }

    .cd-profile-avatar {
        width: 80px;
        height: 80px;
        font-size: 28px;
    }
}
</style>
