<script setup>
import { ref, computed, onMounted } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import MiniAppLayout from '@/Layouts/MiniAppLayout.vue';
import axios from 'axios';

defineOptions({ layout: MiniAppLayout });

const props = defineProps({
    telegramUser: Object,
});

const tg = ref(null);
const tgUser = ref(null);
const step = ref('phone'); // phone | otp
const countdown = ref(0);
let countdownInterval = null;

const form = useForm({
    phone: '+998',
    code: '',
});

onMounted(() => {
    if (window.Telegram?.WebApp) {
        tg.value = window.Telegram.WebApp;
        tgUser.value = tg.value.initDataUnsafe?.user;
    }
});

const formattedPhone = computed(() => {
    return form.phone.replace(/[^\d+]/g, '');
});

const canSendOtp = computed(() => {
    return formattedPhone.value.length >= 13 && countdown.value === 0;
});

const canVerify = computed(() => {
    return form.code.length === 6;
});

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

    form.post('/auth/otp/verify', {
        preserveScroll: true,
        onSuccess: async () => {
            // Link Telegram account if available
            const tgData = tgUser.value || window.Telegram?.WebApp?.initDataUnsafe?.user;
            console.log('Telegram user data:', tgData);
            
            if (tgData?.id) {
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content 
                        || document.querySelector('input[name="_token"]')?.value;
                    
                    await fetch('/app/link-telegram', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            telegram_id: tgData.id,
                            telegram_username: tgData.username || null,
                            telegram_first_name: tgData.first_name || null,
                            telegram_photo_url: tgData.photo_url || null,
                        }),
                    });
                    console.log('Telegram account linked successfully');
                } catch (e) {
                    console.log('Telegram link failed:', e);
                }
            }
            window.location.href = '/app';
        },
    });
};

const startCountdown = () => {
    countdown.value = 60;
    countdownInterval = setInterval(() => {
        countdown.value--;
        if (countdown.value <= 0) {
            clearInterval(countdownInterval);
        }
    }, 1000);
};

const formatPhone = (e) => {
    let value = e.target.value.replace(/[^\d+]/g, '');
    if (!value.startsWith('+998')) {
        value = '+998' + value.replace('+998', '').replace('+', '');
    }
    if (value.length > 13) {
        value = value.slice(0, 13);
    }
    form.phone = value;
};
</script>

<template>
    <div class="login-page">
        <!-- Floating circles background -->
        <div class="bg-circles">
            <div class="circle circle-1"></div>
            <div class="circle circle-2"></div>
            <div class="circle circle-3"></div>
        </div>

        <!-- Header -->
        <div class="login-header">
            <div class="logo-container">
                <div class="logo">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path d="M9 22V12h6v10"/>
                    </svg>
                </div>
            </div>
            <h1 class="title">Home Massage</h1>
            <p class="subtitle">Uyda professional massaj xizmati</p>
        </div>

        <!-- Glass Card -->
        <div class="glass-card">
            <!-- Phone Step -->
            <div v-if="step === 'phone'" class="login-form">
                <div class="input-group">
                    <label class="input-label">Telefon raqamingiz</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.362 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
                        </svg>
                        <input
                            type="tel"
                            class="input-field"
                            :value="form.phone"
                            @input="formatPhone"
                            placeholder="+998 90 123 45 67"
                        />
                    </div>
                    <p v-if="form.errors.phone" class="error-text">{{ form.errors.phone }}</p>
                </div>

                <button 
                    class="submit-btn" 
                    :disabled="!canSendOtp || form.processing"
                    @click="sendOtp"
                >
                    <span v-if="form.processing">Yuborilmoqda...</span>
                    <span v-else>Kodni olish</span>
                    <svg v-if="!form.processing" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

            <!-- OTP Step -->
            <div v-if="step === 'otp'" class="login-form">
                <div class="input-group">
                    <label class="input-label">SMS kod</label>
                    <p class="input-hint">{{ form.phone }} raqamiga yuborildi</p>
                    <div class="otp-container">
                        <input
                            type="text"
                            class="otp-input"
                            v-model="form.code"
                            placeholder="••••••"
                            maxlength="6"
                            inputmode="numeric"
                        />
                    </div>
                    <p v-if="form.errors.code" class="error-text">{{ form.errors.code }}</p>
                </div>

                <button 
                    class="submit-btn" 
                    :disabled="!canVerify || form.processing"
                    @click="verifyOtp"
                >
                    <span v-if="form.processing">Tekshirilmoqda...</span>
                    <span v-else>Kirish</span>
                    <svg v-if="!form.processing" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>

                <div class="secondary-actions">
                    <button 
                        class="link-btn" 
                        :disabled="countdown > 0"
                        @click="sendOtp"
                    >
                        <span v-if="countdown > 0">Qayta yuborish ({{ countdown }}s)</span>
                        <span v-else>Qayta yuborish</span>
                    </button>
                    <span class="divider">•</span>
                    <button class="link-btn" @click="step = 'phone'">
                        Raqamni o'zgartirish
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Xizmatdan foydalanish orqali <a href="#">Foydalanish shartlari</a>ga rozilik bildirasiz</p>
        </div>
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

/* Floating circles */
.bg-circles {
    position: absolute;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
}

.circle {
    position: absolute;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(107, 139, 164, 0.3), rgba(255, 107, 74, 0.2));
    filter: blur(40px);
}

.circle-1 {
    width: 200px;
    height: 200px;
    top: -50px;
    right: -50px;
    animation: float 8s ease-in-out infinite;
}

.circle-2 {
    width: 150px;
    height: 150px;
    bottom: 100px;
    left: -30px;
    animation: float 6s ease-in-out infinite reverse;
}

.circle-3 {
    width: 100px;
    height: 100px;
    top: 50%;
    right: 20%;
    animation: float 10s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0) scale(1); }
    50% { transform: translateY(-20px) scale(1.05); }
}

/* Header */
.login-header {
    text-align: center;
    margin-bottom: 32px;
    position: relative;
    z-index: 1;
    padding-top: 40px;
}

.logo-container {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 80px;
    height: 80px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 24px;
    margin-bottom: 20px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.logo {
    color: #FF6B4A;
}

.title {
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 8px 0;
    color: #ffffff;
}

.subtitle {
    font-size: 15px;
    color: rgba(255, 255, 255, 0.6);
    margin: 0;
}

/* Glass Card */
.glass-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 24px;
    padding: 32px 24px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    position: relative;
    z-index: 1;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}

.login-form {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.input-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.input-label {
    font-size: 14px;
    font-weight: 600;
    color: #ffffff;
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
}

.input-icon {
    position: absolute;
    left: 16px;
    color: rgba(255, 255, 255, 0.4);
}

.input-field {
    width: 100%;
    padding: 16px 16px 16px 48px;
    font-size: 17px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.05);
    color: #ffffff;
    outline: none;
    transition: all 0.3s ease;
}

.input-field::placeholder {
    color: rgba(255, 255, 255, 0.3);
}

.input-field:focus {
    border-color: #FF6B4A;
    background: rgba(255, 255, 255, 0.1);
}

.otp-container {
    display: flex;
    justify-content: center;
}

.otp-input {
    width: 100%;
    max-width: 200px;
    padding: 20px;
    text-align: center;
    letter-spacing: 12px;
    font-size: 28px;
    font-weight: 600;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.05);
    color: #ffffff;
    outline: none;
    transition: all 0.3s ease;
}

.otp-input::placeholder {
    color: rgba(255, 255, 255, 0.2);
    letter-spacing: 8px;
}

.otp-input:focus {
    border-color: #FF6B4A;
    background: rgba(255, 255, 255, 0.1);
}

.error-text {
    color: #FF6B4A;
    font-size: 13px;
    margin: 0;
}

.submit-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 18px;
    font-size: 16px;
    font-weight: 600;
    background: linear-gradient(135deg, #FF6B4A, #FF8F6B);
    color: #ffffff;
    border: none;
    border-radius: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(255, 107, 74, 0.3);
}

.submit-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 107, 74, 0.4);
}

.submit-btn:disabled {
    opacity: 0.5;
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
    color: rgba(255, 255, 255, 0.7);
    border: none;
    cursor: pointer;
    transition: color 0.3s ease;
}

.link-btn:hover:not(:disabled) {
    color: #FF6B4A;
}

.link-btn:disabled {
    color: rgba(255, 255, 255, 0.3);
    cursor: not-allowed;
}

.divider {
    color: rgba(255, 255, 255, 0.3);
}

/* Footer */
.footer {
    margin-top: auto;
    padding-top: 32px;
    text-align: center;
    position: relative;
    z-index: 1;
}

.footer p {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.4);
    margin: 0;
}

.footer a {
    color: rgba(255, 255, 255, 0.6);
    text-decoration: underline;
}
</style>
