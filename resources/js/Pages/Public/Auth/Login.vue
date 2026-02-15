<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import axios from 'axios'

const { t, locale } = useI18n()

const step = ref(1)
const phone = ref('')
const code = ref(['', '', '', '', '', ''])
const loading = ref(false)
const error = ref(null)
const attemptsLeft = ref(null)
const countdownSeconds = ref(0)
const cooldownSeconds = ref(0)
const otpInputRefs = ref([])

// Format countdown as mm:ss
const formatTime = (seconds) => {
    const m = Math.floor(seconds / 60)
    const s = seconds % 60
    return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
}

// Phone display format
const phoneDisplay = () => {
    const p = phone.value
    if (p.length >= 9) {
        return `+998 ${p.slice(0, 2)} ${p.slice(2, 5)} ${p.slice(5, 7)} ${p.slice(7, 9)}`
    }
    return `+998${p}`
}

// Handle Send OTP
async function handleSendOtp() {
    error.value = null
    loading.value = true

    try {
        await axios.post(route('customer.otp.send'), {
            phone: '+998' + phone.value,
            locale: locale.value,
        })

        step.value = 2
        code.value = ['', '', '', '', '', '']
        startCountdown()
        startCooldown()
        // Focus first OTP input after transition
        setTimeout(() => otpInputRefs.value[0]?.focus(), 100)
    } catch (err) {
        if (err.response?.status === 429) {
            error.value = t('auth.otp.rate_limit_exceeded')
        } else {
            error.value = err.response?.data?.message || t('auth.otp.sms_failed')
        }
    } finally {
        loading.value = false
    }
}

// Handle Verify OTP
async function handleVerifyOtp() {
    const codeStr = code.value.join('')
    if (codeStr.length < 6) return

    error.value = null
    loading.value = true

    try {
        const response = await axios.post(route('customer.otp.verify'), {
            phone: '+998' + phone.value,
            code: codeStr,
        })

        window.location.href = response.data.redirect
    } catch (err) {
        const data = err.response?.data
        error.value = data?.message || t('auth.otp.invalid_code')
        attemptsLeft.value = data?.attempts_left || null

        if (data?.error === 'max_attempts') {
            step.value = 1
            phone.value = ''
            code.value = ['', '', '', '', '', '']
        }
    } finally {
        loading.value = false
    }
}

// Handle Resend OTP
async function handleResendOtp() {
    code.value = ['', '', '', '', '', '']
    cooldownSeconds.value = 0
    await handleSendOtp()
}

// Go back to phone input
function goBackToPhone() {
    step.value = 1
    code.value = ['', '', '', '', '', '']
    error.value = null
    attemptsLeft.value = null
    clearInterval(countdownInterval)
    clearInterval(cooldownInterval)
}

// OTP digit input handling
function handleOtpInput(index, event) {
    const val = event.target.value.replace(/\D/g, '')
    code.value[index] = val.slice(-1)

    // Move to next input
    if (val && index < 5) {
        otpInputRefs.value[index + 1]?.focus()
    }

    // Auto-submit when all 6 digits entered
    if (code.value.every(d => d !== '') && code.value.join('').length === 6) {
        handleVerifyOtp()
    }
}

function handleOtpKeydown(index, event) {
    if (event.key === 'Backspace' && !code.value[index] && index > 0) {
        otpInputRefs.value[index - 1]?.focus()
    }
}

function handleOtpPaste(event) {
    event.preventDefault()
    const pasted = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6)
    for (let i = 0; i < 6; i++) {
        code.value[i] = pasted[i] || ''
    }
    if (pasted.length === 6) {
        handleVerifyOtp()
    } else {
        otpInputRefs.value[Math.min(pasted.length, 5)]?.focus()
    }
}

// Countdown timer (OTP expiry)
let countdownInterval
function startCountdown() {
    countdownSeconds.value = 180
    clearInterval(countdownInterval)
    countdownInterval = setInterval(() => {
        countdownSeconds.value--
        if (countdownSeconds.value <= 0) clearInterval(countdownInterval)
    }, 1000)
}

// Cooldown timer (between sends)
let cooldownInterval
function startCooldown() {
    cooldownSeconds.value = 60
    clearInterval(cooldownInterval)
    cooldownInterval = setInterval(() => {
        cooldownSeconds.value--
        if (cooldownSeconds.value <= 0) clearInterval(cooldownInterval)
    }, 1000)
}

onBeforeUnmount(() => {
    clearInterval(countdownInterval)
    clearInterval(cooldownInterval)
})
</script>

<template>
    <Head :title="t('auth.login')" />

    <div class="otp-page">
        <!-- Left Panel -->
        <div class="otp-left">
            <div class="otp-left-content">
                <div class="otp-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m3 4.5a4.5 4.5 0 1 1-4.5-4.5M12 16.5V15m4.5-3a4.5 4.5 0 1 0-4.5-4.5M16.5 12H15"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <span>HOMEMASSAGE</span>
                </div>

                <div class="otp-hero-img">
                    <img src="/images/login-hero.jpg" alt="Massage" onerror="this.style.display='none'" />
                </div>

                <h2 class="otp-tagline">{{ t('auth.loginTagline') }}</h2>
                <p class="otp-tag-sub">{{ t('auth.loginSubtagline') }}</p>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="otp-right">
            <!-- Step 1: Phone Number -->
            <div v-if="step === 1" class="otp-form-card">
                <div class="otp-form-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><path d="M12 18h.01"/></svg>
                </div>

                <div class="otp-form-title-block">
                    <h1 class="otp-form-title">{{ t('auth.login') }}</h1>
                    <p class="otp-form-desc">{{ t('auth.loginDesc') }}</p>
                </div>

                <div class="otp-input-block">
                    <label class="otp-input-label">{{ t('auth.phone') }}</label>
                    <div class="otp-phone-row">
                        <div class="otp-country-code">
                            <span class="otp-flag">🇺🇿</span>
                            <span class="otp-code">+998</span>
                        </div>
                        <input
                            v-model="phone"
                            type="tel"
                            class="otp-phone-input"
                            placeholder="90 123 45 67"
                            maxlength="9"
                            @keyup.enter="handleSendOtp"
                            :disabled="loading"
                        />
                    </div>
                    <p v-if="error" class="otp-error">{{ error }}</p>
                </div>

                <button
                    class="otp-submit-btn"
                    :class="{ disabled: loading || !phone || phone.length < 9 }"
                    :disabled="loading || !phone || phone.length < 9"
                    @click="handleSendOtp"
                >
                    <span v-if="!loading">{{ t('auth.sendOtp') }}</span>
                    <span v-else>{{ t('common.loading') }}</span>
                    <svg v-if="!loading" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </button>

                <div class="otp-divider">
                    <span class="otp-divider-line"></span>
                    <span class="otp-divider-text">{{ t('auth.or') }}</span>
                    <span class="otp-divider-line"></span>
                </div>

                <p class="otp-terms">{{ t('auth.termsText') }}</p>
            </div>

            <!-- Step 2: OTP Code -->
            <div v-else class="otp-form-card">
                <button class="otp-back-btn" @click="goBackToPhone">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    <span>{{ t('common.back') }}</span>
                </button>

                <div class="otp-form-icon otp-form-icon-gold">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                </div>

                <div class="otp-form-title-block">
                    <h1 class="otp-form-title">{{ t('auth.verifyCode') }}</h1>
                    <p class="otp-form-desc">{{ phoneDisplay() }} {{ t('auth.codeSentTo') }}</p>
                </div>

                <div class="otp-digits-row">
                    <input
                        v-for="(digit, i) in code"
                        :key="i"
                        :ref="el => otpInputRefs[i] = el"
                        type="tel"
                        maxlength="1"
                        class="otp-digit"
                        :class="{ filled: digit !== '' }"
                        :value="digit"
                        @input="handleOtpInput(i, $event)"
                        @keydown="handleOtpKeydown(i, $event)"
                        @paste="handleOtpPaste"
                        :disabled="loading || countdownSeconds <= 0"
                    />
                </div>

                <p v-if="error" class="otp-error">{{ error }}</p>
                <p v-if="attemptsLeft" class="otp-warning">{{ attemptsLeft }} {{ t('auth.attemptsLeft') }}</p>

                <button
                    class="otp-submit-btn"
                    :class="{ disabled: loading || code.join('').length < 6 || countdownSeconds <= 0 }"
                    :disabled="loading || code.join('').length < 6 || countdownSeconds <= 0"
                    @click="handleVerifyOtp"
                >
                    <span v-if="!loading">{{ t('auth.verifyCode') }}</span>
                    <span v-else>{{ t('common.loading') }}</span>
                    <svg v-if="!loading" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                </button>

                <div class="otp-timer-block">
                    <p v-if="countdownSeconds > 0" class="otp-timer">
                        {{ t('auth.resendTimer') }}: {{ formatTime(countdownSeconds) }}
                    </p>
                    <p v-else class="otp-expired">{{ t('auth.otp.otpExpired') }}</p>

                    <button
                        v-if="cooldownSeconds <= 0 && countdownSeconds > 0"
                        class="otp-resend-btn"
                        :disabled="loading"
                        @click="handleResendOtp"
                    >
                        {{ t('auth.resendQuestion') }}
                    </button>
                    <p v-else-if="cooldownSeconds > 0" class="otp-cooldown">
                        {{ t('auth.otp.cooldown') }} {{ cooldownSeconds }}s
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
