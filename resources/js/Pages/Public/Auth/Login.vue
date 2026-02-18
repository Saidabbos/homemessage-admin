<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import axios from 'axios'

const { t, locale } = useI18n()

// step: 1 = phone, 2 = method select, 3 = PIN, 4 = OTP
const step = ref(1)
const phone = ref('')
const code = ref(['', '', '', '', '', ''])
const pinCode = ref(['', '', '', ''])
const loading = ref(false)
const error = ref(null)
const attemptsLeft = ref(null)
const countdownSeconds = ref(0)
const cooldownSeconds = ref(0)
const otpInputRefs = ref([])
const pinInputRefs = ref([])
const hasPin = ref(false)

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

// Check if phone has PIN and proceed
async function handleCheckPhone() {
    error.value = null
    loading.value = true

    try {
        const response = await axios.post(route('customer.pin.check'), {
            phone: '+998' + phone.value,
        })

        hasPin.value = response.data.has_pin

        if (response.data.has_pin) {
            // User has PIN - show method selection
            step.value = 2
        } else {
            // No PIN - go directly to OTP
            await sendOtp()
        }
    } catch (err) {
        console.error('PIN check error:', err.response?.status, err.message)
        
        // CSRF token mismatch (419) - refresh page to get new token and retry
        if (err.response?.status === 419) {
            try {
                // Fetch login page to get fresh CSRF token
                const pageResponse = await axios.get(window.location.href)
                // Extract new CSRF token from response
                const match = pageResponse.data.match(/name="csrf-token" content="([^"]+)"/)
                if (match) {
                    const newToken = match[1]
                    document.head.querySelector('meta[name="csrf-token"]')?.setAttribute('content', newToken)
                }
                // Retry PIN check
                const retryResponse = await axios.post(route('customer.pin.check'), {
                    phone: '+998' + phone.value,
                })
                hasPin.value = retryResponse.data.has_pin
                if (retryResponse.data.has_pin) {
                    step.value = 2
                } else {
                    await sendOtp()
                }
                return
            } catch (retryErr) {
                console.error('PIN check retry failed:', retryErr)
                // Still try OTP as fallback
                await sendOtp()
                return
            }
        }
        
        // User not found (404) - new user, go to OTP
        if (err.response?.status === 404) {
            await sendOtp()
            return
        }
        
        // Validation error (422) - go to OTP
        if (err.response?.status === 422) {
            await sendOtp()
            return
        }
        
        // Other errors - show error message
        error.value = 'Tarmoq xatosi. Qayta urinib ko\'ring.'
    } finally {
        loading.value = false
    }
}

// Choose PIN method
function choosePin() {
    step.value = 3
    pinCode.value = ['', '', '', '']
    error.value = null
    setTimeout(() => pinInputRefs.value[0]?.focus(), 100)
}

// Choose OTP method
async function chooseOtp() {
    loading.value = true
    await sendOtp()
    loading.value = false
}

// Handle Send OTP
async function sendOtp() {
    error.value = null

    try {
        await axios.post(route('customer.otp.send'), {
            phone: '+998' + phone.value,
            locale: locale.value,
        })

        step.value = 4
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
    }
}

// Handle PIN login
async function handlePinLogin() {
    const pinStr = pinCode.value.join('')
    if (pinStr.length < 4) return

    error.value = null
    loading.value = true

    try {
        const response = await axios.post(route('customer.pin.login'), {
            phone: '+998' + phone.value,
            pin: pinStr,
        })

        window.location.href = response.data.redirect
    } catch (err) {
        const data = err.response?.data
        error.value = data?.message || 'Noto\'g\'ri PIN kod'
        pinCode.value = ['', '', '', '']
        setTimeout(() => pinInputRefs.value[0]?.focus(), 100)
    } finally {
        loading.value = false
    }
}

// PIN digit input handling
function handlePinInput(index, event) {
    const val = event.target.value.replace(/\D/g, '')
    pinCode.value[index] = val.slice(-1)

    // Move to next input
    if (val && index < 3) {
        pinInputRefs.value[index + 1]?.focus()
    }

    // Auto-submit when all 4 digits entered
    if (pinCode.value.every(d => d !== '') && pinCode.value.join('').length === 4) {
        handlePinLogin()
    }
}

function handlePinKeydown(index, event) {
    if (event.key === 'Backspace' && !pinCode.value[index] && index > 0) {
        pinInputRefs.value[index - 1]?.focus()
    }
}

// Legacy function for resend
async function handleSendOtp() {
    loading.value = true
    await sendOtp()
    loading.value = false
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
    pinCode.value = ['', '', '', '']
    error.value = null
    attemptsLeft.value = null
    hasPin.value = false
    clearInterval(countdownInterval)
    clearInterval(cooldownInterval)
}

// Go back to method selection
function goBackToMethod() {
    step.value = 2
    code.value = ['', '', '', '', '', '']
    pinCode.value = ['', '', '', '']
    error.value = null
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
                    <img src="/images/sauna.jpg" alt="Massage" onerror="this.style.display='none'" />
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
                    @click="handleCheckPhone"
                >
                    <span v-if="!loading">{{ t('auth.continue') || 'Davom etish' }}</span>
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

            <!-- Step 2: Method Selection -->
            <div v-else-if="step === 2" class="otp-form-card">
                <button class="otp-back-btn" @click="goBackToPhone">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    <span>{{ t('common.back') }}</span>
                </button>

                <div class="otp-form-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><path d="M12 18h.01"/></svg>
                </div>

                <div class="otp-form-title-block">
                    <h1 class="otp-form-title">Kirish usulini tanlang</h1>
                    <p class="otp-form-desc">{{ phoneDisplay() }}</p>
                </div>

                <div class="method-options">
                    <button class="method-btn" @click="choosePin">
                        <div class="method-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <div class="method-text">
                            <span class="method-title">PIN kod</span>
                            <span class="method-desc">Tez kirish</span>
                        </div>
                        <svg class="method-arrow" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </button>

                    <button class="method-btn" @click="chooseOtp" :disabled="loading">
                        <div class="method-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                            </svg>
                        </div>
                        <div class="method-text">
                            <span class="method-title">SMS kod</span>
                            <span class="method-desc">Bir martalik kod</span>
                        </div>
                        <svg v-if="!loading" class="method-arrow" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                        <span v-else class="method-loading">...</span>
                    </button>
                </div>
            </div>

            <!-- Step 3: PIN Code -->
            <div v-else-if="step === 3" class="otp-form-card">
                <button class="otp-back-btn" @click="goBackToMethod">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    <span>{{ t('common.back') }}</span>
                </button>

                <div class="otp-form-icon otp-form-icon-gold">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>

                <div class="otp-form-title-block">
                    <h1 class="otp-form-title">PIN kod kiriting</h1>
                    <p class="otp-form-desc">4 xonali PIN kodingizni kiriting</p>
                </div>

                <div class="otp-digits-row pin-digits-row">
                    <input
                        v-for="(digit, i) in pinCode"
                        :key="i"
                        :ref="el => pinInputRefs[i] = el"
                        type="password"
                        inputmode="numeric"
                        maxlength="1"
                        class="otp-digit pin-digit"
                        :class="{ filled: digit !== '' }"
                        :value="digit"
                        @input="handlePinInput(i, $event)"
                        @keydown="handlePinKeydown(i, $event)"
                        :disabled="loading"
                    />
                </div>

                <p v-if="error" class="otp-error">{{ error }}</p>

                <button
                    class="otp-submit-btn"
                    :class="{ disabled: loading || pinCode.join('').length < 4 }"
                    :disabled="loading || pinCode.join('').length < 4"
                    @click="handlePinLogin"
                >
                    <span v-if="!loading">Kirish</span>
                    <span v-else>{{ t('common.loading') }}</span>
                    <svg v-if="!loading" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                </button>

                <div class="otp-divider">
                    <span class="otp-divider-line"></span>
                    <span class="otp-divider-text">{{ t('auth.or') }}</span>
                    <span class="otp-divider-line"></span>
                </div>

                <button class="otp-link-btn" @click="chooseOtp" :disabled="loading">
                    SMS kod bilan kirish
                </button>
            </div>

            <!-- Step 4: OTP Code -->
            <div v-else class="otp-form-card">
                <button class="otp-back-btn" @click="hasPin ? goBackToMethod() : goBackToPhone()">
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
