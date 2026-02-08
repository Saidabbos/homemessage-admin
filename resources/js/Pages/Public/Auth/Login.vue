<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-white mb-2">Golden Touch</h1>
        <p class="text-purple-200">{{ t('auth.otp.sent') }}</p>
      </div>

      <!-- Card -->
      <div class="bg-white rounded-3xl shadow-2xl p-8">
        <!-- Step 1: Phone Input -->
        <div v-if="step === 1" class="space-y-6">
          <h2 class="text-2xl font-bold text-gray-900">{{ t('auth.login') }}</h2>
          <p class="text-gray-600">{{ t('auth.phone') }} орқали кириш</p>

          <!-- Language Selector -->
          <div class="flex gap-2 justify-center">
            <button
              v-for="lang in ['uz', 'ru', 'en']"
              :key="lang"
              @click="locale = lang"
              :class="[
                'px-3 py-2 rounded-lg font-semibold text-sm transition-all',
                locale === lang
                  ? 'bg-purple-600 text-white'
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              ]"
            >
              {{ lang.toUpperCase() }}
            </button>
          </div>

          <!-- Phone Input -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              {{ t('auth.phone') }}
            </label>
            <div class="flex items-center gap-1 border-2 border-gray-300 rounded-xl px-4 py-3 focus-within:border-purple-600 focus-within:ring-2 focus-within:ring-purple-200">
              <span class="text-gray-600 font-semibold">+998</span>
              <input
                v-model="phone"
                type="tel"
                placeholder="90 123 45 67"
                maxlength="9"
                class="flex-1 outline-none text-lg"
                @keyup.enter="handleSendOtp"
                :disabled="loading"
              />
            </div>
            <p v-if="error" class="text-red-500 text-sm mt-2">{{ error }}</p>
          </div>

          <!-- Send Button -->
          <button
            @click="handleSendOtp"
            :disabled="loading || !phone || phone.length < 9"
            class="w-full bg-purple-600 hover:bg-purple-700 disabled:bg-gray-400 text-white font-bold py-3 px-4 rounded-xl transition-all disabled:cursor-not-allowed"
          >
            <span v-if="!loading">{{ t('auth.sendOtp') }}</span>
            <span v-else>{{ t('common.loading') }}...</span>
          </button>

          <!-- Info -->
          <p class="text-xs text-gray-500 text-center">
            SMS код буйича ташдиқлаш қўлланилади
          </p>
        </div>

        <!-- Step 2: OTP Verification -->
        <div v-else class="space-y-6">
          <h2 class="text-2xl font-bold text-gray-900">{{ t('auth.verifyCode') }}</h2>
          <p class="text-gray-600">+998{{ phone }} номерига код юборилди</p>

          <!-- Countdown Timer -->
          <div v-if="countdownSeconds > 0" class="text-center">
            <p class="text-sm text-gray-600">Код колдер вақти</p>
            <p class="text-2xl font-bold text-purple-600">{{ countdownSeconds }} сек</p>
          </div>
          <div v-else class="text-center">
            <p class="text-sm text-red-500">Код муддати ўтган</p>
          </div>

          <!-- OTP Input -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              {{ t('auth.otpCode') }}
            </label>
            <input
              v-model="code"
              type="tel"
              placeholder="000000"
              maxlength="6"
              class="w-full text-center text-3xl font-bold tracking-widest border-2 border-gray-300 rounded-xl px-4 py-4 focus:border-purple-600 focus:ring-2 focus:ring-purple-200 outline-none"
              @keyup.enter="handleVerifyOtp"
              :disabled="loading || countdownSeconds <= 0"
            />
            <p v-if="error" class="text-red-500 text-sm mt-2">{{ error }}</p>
            <p v-if="attemptsLeft" class="text-orange-500 text-sm mt-2">
              {{ attemptsLeft }} та уринишнинг қолди
            </p>
          </div>

          <!-- Verify Button -->
          <button
            @click="handleVerifyOtp"
            :disabled="loading || !code || code.length < 6 || countdownSeconds <= 0"
            class="w-full bg-purple-600 hover:bg-purple-700 disabled:bg-gray-400 text-white font-bold py-3 px-4 rounded-xl transition-all disabled:cursor-not-allowed"
          >
            <span v-if="!loading">{{ t('auth.verifyCode') }}</span>
            <span v-else>{{ t('common.loading') }}...</span>
          </button>

          <!-- Resend Button -->
          <div class="text-center">
            <p v-if="cooldownSeconds > 0" class="text-sm text-gray-600">
              {{ cooldownSeconds }} сек кутиб туринг
            </p>
            <button
              v-else
              @click="handleResendOtp"
              :disabled="loading"
              class="text-purple-600 hover:text-purple-700 font-semibold text-sm disabled:text-gray-400"
            >
              {{ t('auth.resend') }}
            </button>
          </div>

          <!-- Back Button -->
          <button
            @click="goBackToPhone"
            class="w-full text-gray-600 hover:text-gray-700 font-medium py-2 px-4"
          >
            {{ t('common.back') }}
          </button>
        </div>
      </div>

      <!-- Footer -->
      <p class="text-center text-gray-400 text-xs mt-6">
        © 2026 Golden Touch
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

const { t, locale } = useI18n()

const step = ref(1) // 1 = phone, 2 = otp
const phone = ref('')
const code = ref('')
const loading = ref(false)
const error = ref(null)
const attemptsLeft = ref(null)
const countdownSeconds = ref(0)
const cooldownSeconds = ref(0)
const expiresAt = ref(null)

// Handle Send OTP
async function handleSendOtp() {
  error.value = null
  loading.value = true

  try {
    const response = await axios.post(route('customer.otp.send'), {
      phone: '+998' + phone.value,
      locale: locale.value,
    })

    expiresAt.value = response.data.expires_at
    step.value = 2
    code.value = ''
    startCountdown()
  } catch (err) {
    if (err.response?.status === 429) {
      error.value = 'Кўп уринишлар. 1 соат кутиб туринг.'
    } else {
      error.value = err.response?.data?.message || 'Хатолик юз берди'
    }
  } finally {
    loading.value = false
  }
}

// Handle Verify OTP
async function handleVerifyOtp() {
  error.value = null
  loading.value = true

  try {
    const response = await axios.post(route('customer.otp.verify'), {
      phone: '+998' + phone.value,
      code: code.value,
    })

    // Redirect to dashboard
    window.location.href = response.data.redirect
  } catch (err) {
    const data = err.response?.data
    error.value = data?.message || 'Хатолик юз берди'
    attemptsLeft.value = data?.attempts_left || null

    if (data?.error === 'max_attempts') {
      step.value = 1
      phone.value = ''
      code.value = ''
    }
  } finally {
    loading.value = false
  }
}

// Handle Resend OTP
async function handleResendOtp() {
  code.value = ''
  cooldownSeconds.value = 0
  await handleSendOtp()
}

// Go back to phone input
function goBackToPhone() {
  step.value = 1
  code.value = ''
  error.value = null
  attemptsLeft.value = null
  clearInterval(countdownInterval)
  clearInterval(cooldownInterval)
}

// Start countdown timer (OTP expiry)
let countdownInterval
function startCountdown() {
  countdownSeconds.value = 180 // 3 minutes
  clearInterval(countdownInterval)

  countdownInterval = setInterval(() => {
    countdownSeconds.value--
    if (countdownSeconds.value <= 0) {
      clearInterval(countdownInterval)
    }
  }, 1000)
}

// Start cooldown timer (between sends)
let cooldownInterval
function startCooldown() {
  cooldownSeconds.value = 60 // 60 seconds
  clearInterval(cooldownInterval)

  cooldownInterval = setInterval(() => {
    cooldownSeconds.value--
    if (cooldownSeconds.value <= 0) {
      clearInterval(cooldownInterval)
    }
  }, 1000)
}

// Watch for code input and auto-submit if 6 digits
const handleCodeInput = () => {
  if (code.value.length === 6) {
    handleVerifyOtp()
  }
}

// Cleanup on unmount
onMounted(() => {
  return () => {
    clearInterval(countdownInterval)
    clearInterval(cooldownInterval)
  }
})
</script>

<style scoped>
input[type='tel']::placeholder {
  letter-spacing: 0.1em;
}
</style>
