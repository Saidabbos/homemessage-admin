<script setup>
import { ref, computed, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import MiniAppLayout from '@/Layouts/MiniAppLayout.vue';

defineOptions({ layout: MiniAppLayout });

const tg = ref(null);
const telegramUser = ref(null);
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
        telegramUser.value = tg.value.initDataUnsafe?.user;
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
        onSuccess: () => {
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
        <!-- Header -->
        <div class="login-header">
            <div class="logo">🏠</div>
            <h1 class="title">Home Massage</h1>
            <p class="subtitle">Uyda professional massaj xizmati</p>
        </div>

        <!-- Phone Step -->
        <div v-if="step === 'phone'" class="login-form">
            <div class="input-group">
                <label class="input-label">Telefon raqamingiz</label>
                <input
                    type="tel"
                    class="input-field"
                    :value="form.phone"
                    @input="formatPhone"
                    placeholder="+998 90 123 45 67"
                />
                <p v-if="form.errors.phone" class="error-text">{{ form.errors.phone }}</p>
            </div>

            <button 
                class="submit-btn" 
                :disabled="!canSendOtp || form.processing"
                @click="sendOtp"
            >
                <span v-if="form.processing">Yuborilmoqda...</span>
                <span v-else>Kodni olish</span>
            </button>
        </div>

        <!-- OTP Step -->
        <div v-if="step === 'otp'" class="login-form">
            <div class="input-group">
                <label class="input-label">SMS kod</label>
                <p class="input-hint">{{ form.phone }} raqamiga yuborildi</p>
                <input
                    type="text"
                    class="input-field otp-input"
                    v-model="form.code"
                    placeholder="000000"
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
                <span v-if="form.processing">Tekshirilmoqda...</span>
                <span v-else>Kirish</span>
            </button>

            <button 
                class="resend-btn" 
                :disabled="countdown > 0"
                @click="sendOtp"
            >
                <span v-if="countdown > 0">Qayta yuborish ({{ countdown }}s)</span>
                <span v-else>Qayta yuborish</span>
            </button>

            <button class="back-btn" @click="step = 'phone'">
                Raqamni o'zgartirish
            </button>
        </div>
    </div>
</template>

<style scoped>
.login-page {
    min-height: 100vh;
    padding: 40px 24px;
    display: flex;
    flex-direction: column;
}

.login-header {
    text-align: center;
    margin-bottom: 48px;
}

.logo {
    font-size: 64px;
    margin-bottom: 16px;
}

.title {
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 8px 0;
}

.subtitle {
    font-size: 16px;
    color: var(--tg-theme-hint-color);
    margin: 0;
}

.login-form {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.input-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.input-label {
    font-size: 14px;
    font-weight: 600;
}

.input-hint {
    font-size: 13px;
    color: var(--tg-theme-hint-color);
    margin: 0;
}

.input-field {
    padding: 14px 16px;
    font-size: 18px;
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 12px;
    background: var(--tg-theme-bg-color);
    color: var(--tg-theme-text-color);
    outline: none;
}

.input-field:focus {
    border-color: var(--tg-theme-button-color);
}

.otp-input {
    text-align: center;
    letter-spacing: 8px;
    font-size: 24px;
    font-weight: 600;
}

.error-text {
    color: #e53935;
    font-size: 13px;
    margin: 0;
}

.submit-btn {
    padding: 16px;
    font-size: 16px;
    font-weight: 600;
    background: var(--tg-theme-button-color);
    color: var(--tg-theme-button-text-color);
    border: none;
    border-radius: 12px;
    cursor: pointer;
    margin-top: auto;
}

.submit-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.resend-btn, .back-btn {
    padding: 12px;
    font-size: 14px;
    background: transparent;
    color: var(--tg-theme-button-color);
    border: none;
    cursor: pointer;
}

.resend-btn:disabled {
    color: var(--tg-theme-hint-color);
    cursor: not-allowed;
}
</style>
