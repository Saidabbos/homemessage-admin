<template>
  <div class="booking-page">
    <!-- Header with Stepper (Desktop) -->
    <header class="booking-header">
      <div class="top-bar">
        <!-- Logo -->
        <div class="top-logo">
          <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M12 6v6m0 0v6"></path>
          </svg>
          <h1 class="logo-text">SERENITY</h1>
        </div>

        <!-- Stepper (hidden on mobile) -->
        <BookingStepper :current-step="currentStep" :steps="steps" @step-click="goToStep" />

        <!-- Close Button -->
        <button class="close-btn" @click="goToLanding">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>
    </header>

    <!-- Main Content -->
    <main class="booking-content">
      <div class="content-wrapper">
        <!-- Left Column: Form -->
        <div class="left-column">
          <div class="step-title-area">
            <h2 class="step-title">{{ steps[currentStep - 1]?.name }}</h2>
            <p class="step-subtitle">{{ getStepSubtitle() }}</p>
          </div>

          <!-- Step 1: Service Selection -->
          <StepService
            v-if="currentStep === 1"
            v-model="serviceParams"
            :services="services"
            :loading="loading.services"
            @next="nextStep"
          />

          <!-- Step 2: Master & Time -->
          <StepMasterTime
            v-if="currentStep === 2"
            v-model:master="selectedMaster"
            v-model:date="selectedDate"
            v-model:slot="selectedSlot"
            :service-params="serviceParams"
            :masters="masters"
            :dates="dates"
            :slots="slots"
            :loading="loading"
            :total-price="totalPrice"
            @back="prevStep"
            @next="nextStep"
            @update-slots="fetchSlots"
          />

          <!-- Step 3: Payment -->
          <StepPayment
            v-if="currentStep === 3"
            v-model:address="address"
            v-model:phone="phone"
            v-model:offer-accepted="offerAccepted"
            :service-params="serviceParams"
            :selected-master="selectedMaster"
            :selected-date="selectedDate"
            :selected-slot="selectedSlot"
            :total-price="totalPrice"
            :loading="loading.submit"
            @back="prevStep"
            @submit="submitBooking"
          />
        </div>

        <!-- Right Column: Summary (Desktop only) -->
        <aside class="right-column">
          <div class="summary-card">
            <div class="summary-content">
              <!-- Service Summary -->
              <div v-if="currentStep >= 1" class="summary-item">
                <div class="summary-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2z"></path>
                  </svg>
                </div>
                <div class="summary-text">
                  <p class="summary-label">{{ selectedService?.name || t('booking.select_service') }}</p>
                  <p class="summary-detail" v-if="selectedService">{{ serviceParams.duration }} min</p>
                </div>
              </div>

              <!-- Master Summary -->
              <div v-if="currentStep >= 2 && selectedMaster" class="summary-item">
                <div class="summary-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                  </svg>
                </div>
                <div class="summary-text">
                  <p class="summary-label">{{ selectedMaster.full_name }}</p>
                </div>
              </div>

              <!-- Date & Time Summary -->
              <div v-if="currentStep >= 2 && selectedDate && selectedSlot" class="summary-item">
                <div class="summary-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                  </svg>
                </div>
                <div class="summary-text">
                  <p class="summary-label">{{ formatDate(selectedDate) }}</p>
                  <p class="summary-detail">{{ selectedSlot.window_start }} - {{ selectedSlot.window_end }}</p>
                </div>
              </div>
            </div>

            <!-- Price -->
            <div class="summary-price">
              <span class="price-label">{{ t('booking.total') }}</span>
              <span class="price-value">{{ formatPrice(totalPrice) }}</span>
            </div>
          </div>

          <!-- Action Button -->
          <button
            v-if="currentStep < 3"
            class="next-btn"
            :disabled="!canProceedToNext"
            @click="nextStep"
          >
            <span>{{ t('booking.next') }}</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
          </button>
          <button
            v-else
            class="next-btn"
            :disabled="!offerAccepted"
            @click="submitBooking"
          >
            <span>{{ t('booking.confirm') }}</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
          </button>
        </aside>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { router } from '@inertiajs/vue3'
import BookingStepper from '@/Components/Public/Booking/BookingStepper.vue'
import StepService from '@/Components/Public/Booking/StepService.vue'
import StepMasterTime from '@/Components/Public/Booking/StepMasterTime.vue'
import StepPayment from '@/Components/Public/Booking/StepPayment.vue'

const { t, locale } = useI18n()

// Use route helper from Inertia
const route = window.route

// Steps configuration
const steps = [
  { id: 1, name: t('booking.select_service'), key: 'service' },
  { id: 2, name: t('booking.select_master'), key: 'master_time' },
  { id: 3, name: t('booking.confirm_order'), key: 'payment' },
]

const currentStep = ref(1)

// Step subtitles
const stepSubtitles = {
  1: t('booking.service_subtitle'),
  2: t('booking.master_time_subtitle'),
  3: t('booking.confirm_subtitle'),
}

// Loading states
const loading = reactive({
  services: false,
  masters: false,
  dates: false,
  slots: false,
  submit: false,
})

// Step 1: Service params
const serviceParams = ref({
  service_type_id: null,
  massage_type: null,
  pressure_level: 'medium',
  duration: 60,
  people_count: 1,
})

// Services data
const services = ref([])

// Step 2: Master & Time
const masters = ref([])
const dates = ref([])
const slots = ref([])
const selectedMaster = ref(null) // null = "All masters"
const selectedDate = ref(null)
const selectedSlot = ref(null)

// Step 3: Payment
const address = ref('')
const phone = ref('')
const offerAccepted = ref(false)

// Computed
const selectedService = computed(() => {
  return services.value.find(s => s.id === serviceParams.value.service_type_id)
})

const totalPrice = computed(() => {
  if (!serviceParams.value.service_type_id) return 0
  const service = selectedService.value
  if (!service) return 0
  return service.price * serviceParams.value.people_count
})

const totalPriceFormatted = computed(() => {
  return formatPrice(totalPrice.value)
})

const canProceedToNext = computed(() => {
  if (currentStep.value === 1) {
    return !!serviceParams.value.service_type_id
  }
  if (currentStep.value === 2) {
    return !!selectedMaster.value && !!selectedDate.value && !!selectedSlot.value
  }
  return true
})

// Utility functions
function formatPrice(price) {
  return new Intl.NumberFormat('uz-UZ').format(price) + ' so\'m'
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  const date = new Date(dateStr + 'T00:00:00')
  return new Intl.DateTimeFormat(locale.value === 'uz' ? 'uz-UZ' : locale.value === 'ru' ? 'ru-RU' : 'en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
  }).format(date)
}

function getStepSubtitle() {
  return stepSubtitles[currentStep.value] || ''
}

function goToLanding() {
  window.location.href = route('public.landing')
}

// API calls
const API_BASE = '/api/v1'

async function fetchServices() {
  loading.services = true
  try {
    const response = await fetch(`${API_BASE}/services`)
    const data = await response.json()
    if (data.success) {
      services.value = data.data
    }
  } catch (error) {
    console.error('Failed to fetch services:', error)
  } finally {
    loading.services = false
  }
}

async function fetchMasters() {
  loading.masters = true
  try {
    const params = new URLSearchParams()
    if (serviceParams.value.massage_type) {
      params.append('massage_type', serviceParams.value.massage_type)
    }
    if (serviceParams.value.pressure_level !== 'any') {
      params.append('pressure_level', serviceParams.value.pressure_level)
    }
    
    const response = await fetch(`${API_BASE}/masters?${params}`)
    const data = await response.json()
    if (data.success) {
      masters.value = data.data
    }
  } catch (error) {
    console.error('Failed to fetch masters:', error)
  } finally {
    loading.masters = false
  }
}

async function fetchDates() {
  loading.dates = true
  try {
    const params = new URLSearchParams({
      duration: serviceParams.value.duration,
      people_count: serviceParams.value.people_count,
    })
    if (selectedMaster.value) {
      params.append('master_id', selectedMaster.value.id)
    }
    if (serviceParams.value.massage_type) {
      params.append('massage_type', serviceParams.value.massage_type)
    }
    
    const response = await fetch(`${API_BASE}/dates/availability?${params}`)
    const data = await response.json()
    if (data.success) {
      dates.value = data.data
      // Auto-select first available date
      if (!selectedDate.value) {
        const firstAvailable = dates.value.find(d => d.has_slots)
        if (firstAvailable) {
          selectedDate.value = firstAvailable.date
        }
      }
    }
  } catch (error) {
    console.error('Failed to fetch dates:', error)
  } finally {
    loading.dates = false
  }
}

async function fetchSlots() {
  if (!selectedDate.value) return
  
  loading.slots = true
  slots.value = []
  
  try {
    const params = new URLSearchParams({
      date: selectedDate.value,
      duration: serviceParams.value.duration,
      people_count: serviceParams.value.people_count,
    })
    if (selectedMaster.value) {
      params.append('master_id', selectedMaster.value.id)
    }
    if (serviceParams.value.massage_type) {
      params.append('massage_type', serviceParams.value.massage_type)
    }
    if (serviceParams.value.pressure_level !== 'any') {
      params.append('pressure_level', serviceParams.value.pressure_level)
    }
    
    const response = await fetch(`${API_BASE}/slots?${params}`)
    const data = await response.json()
    if (data.success) {
      slots.value = data.data.slots
    }
  } catch (error) {
    console.error('Failed to fetch slots:', error)
  } finally {
    loading.slots = false
  }
}

async function submitBooking() {
  loading.submit = true
  try {
    const response = await fetch(`${API_BASE}/bookings`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        service_type_id: serviceParams.value.service_type_id,
        master_id: selectedMaster.value?.id,
        date: selectedDate.value,
        arrival_window_start: selectedSlot.value.window_start,
        arrival_window_end: selectedSlot.value.window_end,
        people_count: serviceParams.value.people_count,
        pressure_level: serviceParams.value.pressure_level,
        address: address.value,
        contact_phone: phone.value,
      }),
    })
    
    const data = await response.json()
    if (data.success) {
      // Redirect to success page
      window.location.href = `/booking/success?order=${data.data.order_number}`
    } else {
      alert(data.message || 'Xatolik yuz berdi')
    }
  } catch (error) {
    console.error('Failed to submit booking:', error)
    alert('Xatolik yuz berdi')
  } finally {
    loading.submit = false
  }
}

// Navigation
function nextStep() {
  if (currentStep.value < 3) {
    currentStep.value++
    
    // Load data for step 2
    if (currentStep.value === 2) {
      fetchMasters()
      fetchDates()
    }
  }
}

function prevStep() {
  if (currentStep.value > 1) {
    currentStep.value--
  }
}

function goToStep(step) {
  if (step < currentStep.value) {
    currentStep.value = step
  }
}

// Watchers
watch(selectedDate, () => {
  selectedSlot.value = null
  fetchSlots()
})

watch(selectedMaster, () => {
  selectedSlot.value = null
  fetchDates()
})

// Lifecycle
onMounted(() => {
  fetchServices()
})
</script>

<style scoped>
.booking-page {
  min-height: 100vh;
  background: linear-gradient(160deg, #F0EBE2 0%, #E5E0D7 50%, #DDD8CF 100%);
  font-family: 'Manrope', sans-serif;
}

/* Header */
.booking-header {
  position: sticky;
  top: 0;
  z-index: 100;
  padding: 20px 120px;
  display: flex;
  align-items: center;
  backdrop-filter: blur(12px);
  background: rgba(255, 255, 255, 0.1);
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.top-bar {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
}

.top-logo {
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo-icon {
  width: 28px;
  height: 28px;
  color: #C8A951;
  stroke-width: 2;
}

.logo-text {
  font-family: 'Playfair Display', serif;
  font-size: 20px;
  font-weight: 500;
  color: #1B2B5A;
  letter-spacing: 3px;
  margin: 0;
}

.close-btn {
  width: 40px;
  height: 40px;
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.5);
  backdrop-filter: blur(12px);
  cursor: pointer;
  transition: all 0.3s ease;
  color: #1B2B5A;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.5);
}

.close-btn svg {
  width: 18px;
  height: 18px;
}

/* Main Content */
.booking-content {
  flex: 1;
  padding: 40px 120px;
  min-height: calc(100vh - 100px);
}

.content-wrapper {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 48px;
  height: fit-content;
  max-width: 1600px;
  margin: 0 auto;
}

.left-column {
  display: flex;
  flex-direction: column;
  gap: 32px;
}

.step-title-area {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.step-title {
  font-family: 'Playfair Display', serif;
  font-size: 32px;
  font-weight: 500;
  color: #1B2B5A;
  margin: 0;
  line-height: 1.2;
}

.step-subtitle {
  font-size: 14px;
  color: rgba(27, 43, 90, 0.6);
  margin: 0;
  font-weight: 400;
}

/* Right Column - Summary */
.right-column {
  display: flex;
  flex-direction: column;
  gap: 24px;
  height: fit-content;
  position: sticky;
  top: 120px;
}

.summary-card {
  border-radius: 24px;
  background: rgba(255, 255, 255, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(24px);
  padding: 28px;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.summary-content {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.summary-item {
  display: flex;
  gap: 14px;
}

.summary-icon {
  width: 44px;
  height: 44px;
  min-width: 44px;
  border-radius: 12px;
  background: rgba(200, 169, 81, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #C8A951;
}

.summary-icon svg {
  width: 20px;
  height: 20px;
}

.summary-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
  flex: 1;
}

.summary-label {
  font-size: 13px;
  font-weight: 600;
  color: #1B2B5A;
  margin: 0;
}

.summary-detail {
  font-size: 12px;
  color: rgba(27, 43, 90, 0.6);
  margin: 0;
}

.summary-price {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.price-label {
  font-size: 11px;
  color: rgba(27, 43, 90, 0.6);
  font-weight: 400;
}

.price-value {
  font-family: 'Playfair Display', serif;
  font-size: 20px;
  color: #C8A951;
  font-weight: 500;
}

.next-btn {
  border-radius: 28px;
  background: #C8A951;
  color: #1B2B5A;
  border: none;
  padding: 18px 40px;
  font-size: 15px;
  font-weight: 600;
  font-family: 'Manrope', sans-serif;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 20px rgba(200, 169, 81, 0.25);
}

.next-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 24px rgba(200, 169, 81, 0.35);
}

.next-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.next-btn svg {
  width: 16px;
  height: 16px;
}

/* Mobile Responsive */
@media (max-width: 1200px) {
  .booking-header {
    padding: 12px 20px;
  }

  .booking-content {
    padding: 16px 20px;
  }

  .content-wrapper {
    grid-template-columns: 1fr;
    gap: 24px;
  }

  .right-column {
    position: static;
    top: auto;
  }
}

@media (max-width: 768px) {
  .booking-header {
    padding: 12px 20px;
  }

  .booking-content {
    padding: 16px 20px;
  }

  .step-title {
    font-size: 26px;
  }

  .summary-card {
    padding: 20px;
    gap: 20px;
  }
}
</style>
