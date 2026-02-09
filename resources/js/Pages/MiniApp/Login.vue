<script setup>
import { ref, computed, onMounted } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import MiniAppLayout from '@/Layouts/MiniAppLayout.vue';

defineOptions({ layout: MiniAppLayout });

const props = defineProps({
    telegramUser: Object,
});

const tg = ref(null);
const tgUser = ref(null);
const step = ref('phone');
const countdown = ref(0);
let countdownInterval = null;

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
const canSendOtp = computed(() => formattedPhone.value.length >= 13 && countdown.value === 0);
const canVerify = computed(() => form.code.length === 6);

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
</script>

<template>
    <div class="login-page">
        <!-- Auto-login loading -->
        <div v-if="isAutoLogging" class="auto-login">
            <div class="spinner"></div>
            <p>Kirish...</p>
        </div>

        <!-- Logo -->
        <div class="login-header">
            <div class="logo">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path d="M9 22V12h6v10"/>
                </svg>
            </div>
            <h1 class="title">Home Massage</h1>
            <p class="subtitle">Uyda professional massaj xizmati</p>
        </div>

        <!-- Form Card -->
        <div class="form-card">
            <!-- Phone Step -->
            <div v-if="step === 'phone'" class="form-content">
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
                    {{ form.processing ? 'Yuborilmoqda...' : 'Kodni olish' }}
                </button>
            </div>

            <!-- OTP Step -->
            <div v-if="step === 'otp'" class="form-content">
                <div class="input-group">
                    <label class="input-label">SMS kod</label>
                    <p class="input-hint">{{ form.phone }} raqamiga yuborildi</p>
                    <input
                        type="text"
                        class="otp-input"
                        v-model="form.code"
                        placeholder="••••••"
                        maxlength="6"
                        inputmode="numeric"
                    />
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
                    <button class="link-btn" @click="step = 'phone'">
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
    background: #F8F6F3;
}

/* Auto-login */
.auto-login {
    position: fixed;
    inset: 0;
    background: #F8F6F3;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 100;
}

.auto-login p {
    color: #666;
    margin-top: 16px;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #E5E5E5;
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
}

.logo {
    width: 72px;
    height: 72px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #B8A369;
    border-radius: 20px;
    color: #fff;
    margin-bottom: 20px;
}

.title {
    font-size: 24px;
    font-weight: 700;
    color: #333;
    margin: 0 0 8px;
}

.subtitle {
    font-size: 14px;
    color: #888;
    margin: 0;
}

/* Form Card */
.form-card {
    background: #fff;
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

.form-content {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.input-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.input-label {
    font-size: 14px;
    font-weight: 600;
    color: #333;
}

.input-hint {
    font-size: 13px;
    color: #888;
    margin: 0;
}

.input-wrapper {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #888;
}

.input-field {
    width: 100%;
    padding: 16px 16px 16px 48px;
    font-size: 16px;
    border: 1px solid #E5E5E5;
    border-radius: 12px;
    background: #F8F6F3;
    color: #333;
    outline: none;
}

.input-field:focus {
    border-color: #B8A369;
}

.otp-input {
    width: 100%;
    padding: 20px;
    text-align: center;
    letter-spacing: 12px;
    font-size: 24px;
    font-weight: 600;
    border: 1px solid #E5E5E5;
    border-radius: 12px;
    background: #F8F6F3;
    color: #333;
    outline: none;
}

.otp-input:focus {
    border-color: #B8A369;
}

.error-text {
    color: #DC2626;
    font-size: 13px;
    margin: 0;
}

.submit-btn {
    width: 100%;
    padding: 16px;
    font-size: 16px;
    font-weight: 600;
    background: #B8A369;
    color: #fff;
    border: none;
    border-radius: 12px;
    cursor: pointer;
}

.submit-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
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
    color: #888;
    border: none;
    cursor: pointer;
}

.link-btn:hover:not(:disabled) {
    color: #B8A369;
}

.link-btn:disabled {
    color: #CCC;
    cursor: not-allowed;
}

.divider {
    color: #CCC;
}

/* Footer */
.footer {
    margin-top: auto;
    padding-top: 24px;
    text-align: center;
    font-size: 12px;
    color: #888;
}
</style>
