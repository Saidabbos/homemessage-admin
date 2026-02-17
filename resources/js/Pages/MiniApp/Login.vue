<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import MiniAppLayout from '@/Layouts/MiniAppLayout.vue';

defineOptions({ layout: MiniAppLayout });

const props = defineProps({
    telegramUser: Object,
});

const tg = ref(null);
const tgUser = ref(null);
const step = ref('phone'); // 'phone', 'method', 'otp', 'pin'
const countdown = ref(0);
let countdownInterval = null;

// PIN state
const hasPin = ref(false);
const pinCode = ref('');
const pinError = ref('');
const pinLoading = ref(false);

const form = useForm({
    phone: '+998',
    code: '',
    telegram_id: null,
    telegram_username: null,
    telegram_first_name: null,
    telegram_photo_url: null,
});

const isAutoLogging = ref(false);

onMounted(async () => {
    if (window.Telegram?.WebApp) {
        tg.value = window.Telegram.WebApp;
        tgUser.value = tg.value.initDataUnsafe?.user;
        
        if (tgUser.value?.id) {
            isAutoLogging.value = true;
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                const response = await fetch('/app/auto-login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        telegram_id: tgUser.value.id,
                        init_data: tg.value.initData,
                    }),
                });
                
                const result = await response.json();
                if (result.success) {
                    window.location.href = '/app';
                    return;
                }
            } catch (e) {
                console.log('Auto-login failed:', e);
            }
            isAutoLogging.value = false;
        }
    }
});

const formattedPhone = computed(() => form.phone.replace(/[^\d+]/g, ''));
const canCheckPhone = computed(() => formattedPhone.value.length >= 13);
const canSendOtp = computed(() => formattedPhone.value.length >= 13 && countdown.value === 0);
const canVerify = computed(() => form.code.length === 6);
const canVerifyPin = computed(() => pinCode.value.length === 4);

// Check if phone has PIN when moving to next step
const checkPhoneAndProceed = async () => {
    if (!canCheckPhone.value) return;
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const response = await fetch('/auth/pin/check', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ phone: form.phone }),
        });
        
        const result = await response.json();
        hasPin.value = result.has_pin;
        
        if (result.has_pin) {
            // User has PIN - show method selection
            step.value = 'method';
        } else {
            // No PIN - go directly to OTP
            sendOtp();
        }
    } catch (e) {
        // On error, fallback to OTP
        sendOtp();
    }
};

const choosePin = () => {
    pinCode.value = '';
    pinError.value = '';
    step.value = 'pin';
};

const chooseOtp = () => {
    sendOtp();
};

const loginWithPin = async () => {
    if (!canVerifyPin.value) return;
    
    pinLoading.value = true;
    pinError.value = '';
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const response = await fetch('/auth/pin/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ 
                phone: form.phone,
                pin: pinCode.value,
            }),
        });
        
        const result = await response.json();
        
        if (result.success) {
            window.location.href = result.redirect || '/app';
        } else {
            pinError.value = result.message || 'Noto\'g\'ri PIN kod';
            pinCode.value = '';
        }
    } catch (e) {
        pinError.value = 'Xatolik yuz berdi';
    } finally {
        pinLoading.value = false;
    }
};

const sendOtp = async () => {
    if (!canSendOtp.value) return;
    form.post('/auth/otp/send', {
        preserveScroll: true,
        onSuccess: () => {
            step.value = 'otp';
            startCountdown();
        },
    });
};

const verifyOtp = async () => {
    if (!canVerify.value) return;
    const webapp = window.Telegram?.WebApp;
    const tgData = tgUser.value || webapp?.initDataUnsafe?.user;
    
    if (tgData) {
        form.telegram_id = tgData.id || null;
        form.telegram_username = tgData.username || null;
        form.telegram_first_name = tgData.first_name || null;
        form.telegram_photo_url = tgData.photo_url || null;
    }

    form.post('/auth/otp/verify', { preserveScroll: true });
};

const startCountdown = () => {
    countdown.value = 60;
    countdownInterval = setInterval(() => {
        countdown.value--;
        if (countdown.value <= 0) clearInterval(countdownInterval);
    }, 1000);
};

const formatPhone = (e) => {
    let value = e.target.value.replace(/[^\d+]/g, '');
    if (!value.startsWith('+998')) {
        value = '+998' + value.replace('+998', '').replace('+', '');
    }
    if (value.length > 13) value = value.slice(0, 13);
    form.phone = value;
};

const goBack = () => {
    if (step.value === 'method' || step.value === 'otp' || step.value === 'pin') {
        step.value = 'phone';
        pinCode.value = '';
        pinError.value = '';
        form.code = '';
    }
};
</script>

<template>
    <div class="login-page">
        <!-- Background circles -->
        <div class="bg-circles">
            <div class="circle c1"></div>
            <div class="circle c2"></div>
            <div class="circle c3"></div>
        </div>

        <!-- Auto-login loading -->
        <div v-if="isAutoLogging" class="auto-login">
            <div class="spinner"></div>
            <p>Kirish...</p>
        </div>

        <!-- Logo -->
        <div class="login-header">
            <div class="logo glass">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path d="M9 22V12h6v10"/>
                </svg>
            </div>
            <h1 class="title">Home Massage</h1>
            <p class="subtitle">Uyda professional massaj xizmati</p>
        </div>

        <!-- Form Card -->
        <div class="form-card glass">
            <!-- Phone Step -->
            <div v-if="step === 'phone'" class="form-content">
                <div class="input-group">
                    <label class="input-label">Telefon raqamingiz</label>
                    <div class="input-wrapper glass">
                        <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.362 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
                        </svg>
                        <input
                            type="tel"
                            class="input-field"
                            :value="form.phone"
                            @input="formatPhone"
                            placeholder="+998 90 123 45 67"
                            @keyup.enter="checkPhoneAndProceed"
                        />
                    </div>
                    <p v-if="form.errors.phone" class="error-text">{{ form.errors.phone }}</p>
                </div>

                <button 
                    class="submit-btn" 
                    :disabled="!canCheckPhone || form.processing"
                    @click="checkPhoneAndProceed"
                >
                    {{ form.processing ? 'Yuborilmoqda...' : 'Davom etish' }}
                </button>
            </div>

            <!-- Method Selection Step (when user has PIN) -->
            <div v-if="step === 'method'" class="form-content">
                <div class="method-header">
                    <p class="method-phone">{{ form.phone }}</p>
                    <p class="method-hint">Kirish usulini tanlang</p>
                </div>

                <div class="method-options">
                    <button class="method-btn" @click="choosePin">
                        <div class="method-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <div class="method-text">
                            <span class="method-title">PIN kod</span>
                            <span class="method-desc">Tez kirish</span>
                        </div>
                        <svg class="method-arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m9 18 6-6-6-6"/>
                        </svg>
                    </button>

                    <button class="method-btn" @click="chooseOtp">
                        <div class="method-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                            </svg>
                        </div>
                        <div class="method-text">
                            <span class="method-title">SMS kod</span>
                            <span class="method-desc">Bir martalik kod</span>
                        </div>
                        <svg class="method-arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m9 18 6-6-6-6"/>
                        </svg>
                    </button>
                </div>

                <button class="link-btn back-btn" @click="goBack">
                    ← Raqamni o'zgartirish
                </button>
            </div>

            <!-- PIN Step -->
            <div v-if="step === 'pin'" class="form-content">
                <div class="input-group">
                    <label class="input-label">PIN kod</label>
                    <p class="input-hint">4 xonali PIN kodingizni kiriting</p>
                    <div class="otp-wrapper glass">
                        <input
                            type="password"
                            class="otp-input pin-input-field"
                            v-model="pinCode"
                            placeholder="••••"
                            maxlength="4"
                            inputmode="numeric"
                            @keyup.enter="loginWithPin"
                        />
                    </div>
                    <p v-if="pinError" class="error-text">{{ pinError }}</p>
                </div>

                <button 
                    class="submit-btn" 
                    :disabled="!canVerifyPin || pinLoading"
                    @click="loginWithPin"
                >
                    {{ pinLoading ? 'Tekshirilmoqda...' : 'Kirish' }}
                </button>

                <div class="secondary-actions">
                    <button class="link-btn" @click="chooseOtp">
                        SMS kod bilan kirish
                    </button>
                    <span class="divider">•</span>
                    <button class="link-btn" @click="goBack">
                        Raqamni o'zgartirish
                    </button>
                </div>
            </div>

            <!-- OTP Step -->
            <div v-if="step === 'otp'" class="form-content">
                <div class="input-group">
                    <label class="input-label">SMS kod</label>
                    <p class="input-hint">{{ form.phone }} raqamiga yuborildi</p>
                    <div class="otp-wrapper glass">
                        <input
                            type="text"
                            class="otp-input"
                            v-model="form.code"
                            placeholder="••••••"
                            maxlength="6"
                            inputmode="numeric"
                            @keyup.enter="verifyOtp"
                        />
                    </div>
                    <p v-if="form.errors.code" class="error-text">{{ form.errors.code }}</p>
                </div>

                <button 
                    class="submit-btn" 
                    :disabled="!canVerify || form.processing"
                    @click="verifyOtp"
                >
                    {{ form.processing ? 'Tekshirilmoqda...' : 'Kirish' }}
                </button>

                <div class="secondary-actions">
                    <button 
                        class="link-btn" 
                        :disabled="countdown > 0"
                        @click="sendOtp"
                    >
                        {{ countdown > 0 ? `Qayta yuborish (${countdown}s)` : 'Qayta yuborish' }}
                    </button>
                    <span class="divider">•</span>
                    <button class="link-btn" @click="goBack">
                        Raqamni o'zgartirish
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <p class="footer">Xizmatdan foydalanish orqali Foydalanish shartlariga rozilik bildirasiz</p>
    </div>
</template>

<style scoped>
.login-page {
    min-height: 100vh;
    padding: 24px;
    display: flex;
    flex-direction: column;
    background: linear-gradient(135deg, #1a2a3a 0%, #2d4a5e 50%, #1a2a3a 100%);
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
.c2 { width: 150px; height: 150px; bottom: 100px; left: -40px; animation-delay: -2s; }
.c3 { width: 100px; height: 100px; top: 50%; right: 20%; animation-delay: -4s; }

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

/* Auto-login */
.auto-login {
    position: fixed;
    inset: 0;
    background: linear-gradient(135deg, #1a2a3a 0%, #2d4a5e 50%, #1a2a3a 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 100;
}

.auto-login p {
    color: rgba(255, 255, 255, 0.6);
    margin-top: 16px;
}

.spinner {
    width: 48px;
    height: 48px;
    border: 3px solid rgba(255, 255, 255, 0.1);
    border-top-color: #B8A369;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Header */
.login-header {
    text-align: center;
    margin: 48px 0 32px;
    position: relative;
    z-index: 1;
    animation: fadeInDown 0.5s ease;
}

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

.logo {
    width: 80px;
    height: 80px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 24px;
    color: #B8A369;
    margin-bottom: 20px;
    transition: all 0.3s ease;
}

.logo:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 30px rgba(184, 163, 105, 0.3);
}

.title {
    font-size: 28px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 8px;
}

.subtitle {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.5);
    margin: 0;
}

/* Form Card */
.form-card {
    border-radius: 28px;
    padding: 28px;
    position: relative;
    z-index: 1;
    animation: fadeInUp 0.5s ease 0.2s both;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.form-content {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.input-group {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.input-label {
    font-size: 14px;
    font-weight: 600;
    color: #fff;
}

.input-hint {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.5);
    margin: 0;
}

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    border-radius: 16px;
    transition: all 0.3s ease;
}

.input-wrapper:focus-within {
    border-color: rgba(184, 163, 105, 0.5);
    box-shadow: 0 0 20px rgba(184, 163, 105, 0.2);
}

.input-icon {
    position: absolute;
    left: 16px;
    color: rgba(255, 255, 255, 0.4);
}

.input-field {
    width: 100%;
    padding: 18px 18px 18px 52px;
    font-size: 17px;
    border: none;
    background: transparent;
    color: #fff;
    outline: none;
}

.input-field::placeholder {
    color: rgba(255, 255, 255, 0.3);
}

.otp-wrapper {
    border-radius: 16px;
    transition: all 0.3s ease;
}

.otp-wrapper:focus-within {
    border-color: rgba(184, 163, 105, 0.5);
    box-shadow: 0 0 20px rgba(184, 163, 105, 0.2);
}

.otp-input {
    width: 100%;
    padding: 20px;
    text-align: center;
    letter-spacing: 16px;
    font-size: 28px;
    font-weight: 600;
    border: none;
    background: transparent;
    color: #fff;
    outline: none;
}

.otp-input::placeholder {
    color: rgba(255, 255, 255, 0.2);
    letter-spacing: 10px;
}

.error-text {
    color: #FCA5A5;
    font-size: 13px;
    margin: 0;
}

.submit-btn {
    width: 100%;
    padding: 18px;
    font-size: 16px;
    font-weight: 600;
    background: linear-gradient(135deg, #B8A369, #D4C89A);
    color: #1a2a3a;
    border: none;
    border-radius: 16px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 20px rgba(184, 163, 105, 0.4);
}

.submit-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(184, 163, 105, 0.5);
}

.submit-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    transform: none;
}

.secondary-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
}

.link-btn {
    padding: 8px;
    font-size: 14px;
    background: transparent;
    color: rgba(255, 255, 255, 0.6);
    border: none;
    cursor: pointer;
    transition: color 0.3s ease;
}

.link-btn:hover:not(:disabled) {
    color: #B8A369;
}

.link-btn:disabled {
    color: rgba(255, 255, 255, 0.3);
    cursor: not-allowed;
}

.divider {
    color: rgba(255, 255, 255, 0.3);
}

/* Method Selection */
.method-header {
    text-align: center;
    margin-bottom: 20px;
}

.method-phone {
    font-size: 16px;
    font-weight: 600;
    color: #fff;
    margin: 0 0 4px;
}

.method-hint {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.5);
    margin: 0;
}

.method-options {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 20px;
}

.method-btn {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px;
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 16px;
    cursor: pointer;
    transition: all 0.2s;
    text-align: left;
}

.method-btn:hover {
    background: rgba(255, 255, 255, 0.12);
    border-color: rgba(184, 163, 105, 0.5);
}

.method-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(184, 163, 105, 0.2);
    border-radius: 12px;
    color: #B8A369;
}

.method-text {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.method-title {
    font-size: 15px;
    font-weight: 600;
    color: #fff;
}

.method-desc {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.5);
}

.method-arrow {
    color: rgba(255, 255, 255, 0.4);
    transition: transform 0.2s;
}

.method-btn:hover .method-arrow {
    transform: translateX(4px);
    color: #B8A369;
}

.back-btn {
    width: 100%;
    text-align: center;
    padding: 12px;
}

/* PIN input */
.pin-input-field {
    letter-spacing: 12px;
}

/* Footer */
.footer {
    margin-top: auto;
    padding-top: 24px;
    text-align: center;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.3);
    position: relative;
    z-index: 1;
}
</style>
