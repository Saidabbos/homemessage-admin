<template>
  <div class="booking-page">
    <!-- Header with Stepper -->
    <header class="booking-header">
      <div class="header-content">
        <h1 class="logo">SABAI</h1>
        <BookingStepper :current-step="currentStep" :steps="steps" @step-click="goToStep" />
      </div>
    </header>

    <!-- Main Content -->
    <main class="booking-content">
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
    </main>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import BookingStepper from '@/Components/Public/Booking/BookingStepper.vue'
import StepService from '@/Components/Public/Booking/StepService.vue'
import StepMasterTime from '@/Components/Public/Booking/StepMasterTime.vue'
import StepPayment from '@/Components/Public/Booking/StepPayment.vue'

const { t } = useI18n()

// Steps configuration
const steps = [
  { id: 1, name: t('booking.step_service'), key: 'service' },
  { id: 2, name: t('booking.step_master_time'), key: 'master_time' },
  { id: 3, name: t('booking.step_payment'), key: 'payment' },
]

const currentStep = ref(1)

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
const totalPrice = computed(() => {
  if (!serviceParams.value.service_type_id) return 0
  const service = services.value.find(s => s.id === serviceParams.value.service_type_id)
  if (!service) return 0
  return service.price * serviceParams.value.people_count
})

const totalPriceFormatted = computed(() => {
  return new Intl.NumberFormat('uz-UZ').format(totalPrice.value) + ' so\'m'
})

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
  background: linear-gradient(135deg, #f5f7fa 0%, #e4e9f2 100%);
}

.booking-header {
  background: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  position: sticky;
  top: 0;
  z-index: 100;
}

.header-content {
  max-width: 640px;
  margin: 0 auto;
  padding: 1rem;
}

.logo {
  font-size: 1.5rem;
  font-weight: 700;
  color: #2d3748;
  text-align: center;
  margin-bottom: 1rem;
}

.booking-content {
  max-width: 640px;
  margin: 0 auto;
  padding: 1rem;
  padding-bottom: 120px; /* Space for sticky footer */
}
</style>
