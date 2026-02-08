<template>
  <div class="step-payment">
    <!-- Order Summary -->
    <section class="order-summary">
      <h2 class="section__title">{{ $t('booking.order_details') }}</h2>
      
      <div class="summary-row">
        <span class="summary-row__label">{{ $t('booking.service') }}</span>
        <span class="summary-row__value">{{ serviceName }}</span>
      </div>
      
      <div class="summary-row">
        <span class="summary-row__label">{{ $t('booking.duration') }}</span>
        <span class="summary-row__value">{{ serviceParams.duration }} min</span>
      </div>
      
      <div class="summary-row">
        <span class="summary-row__label">{{ $t('booking.people') }}</span>
        <span class="summary-row__value">{{ serviceParams.people_count }} kishi</span>
      </div>
      
      <div class="summary-row">
        <span class="summary-row__label">{{ $t('booking.master') }}</span>
        <span class="summary-row__value">{{ masterName }}</span>
      </div>
      
      <div class="summary-row">
        <span class="summary-row__label">{{ $t('booking.datetime') }}</span>
        <span class="summary-row__value">{{ formattedDateTime }}</span>
      </div>
      
      <div class="summary-row summary-row--total">
        <span class="summary-row__label">{{ $t('booking.total') }}</span>
        <span class="summary-row__value">{{ formatPrice(totalPrice) }}</span>
      </div>
    </section>

    <!-- Address Input -->
    <section class="section">
      <h2 class="section__title">{{ $t('booking.address') }}</h2>
      <BaseInput
        v-model="addressValue"
        :placeholder="$t('booking.address_placeholder')"
        :required="true"
      />
    </section>

    <!-- Phone Input -->
    <section class="section">
      <h2 class="section__title">{{ $t('booking.phone') }}</h2>
      <BaseInput
        v-model="phoneValue"
        type="tel"
        :placeholder="$t('booking.phone_placeholder')"
        :required="true"
      />
    </section>

    <!-- Payment Methods -->
    <section class="section">
      <h2 class="section__title">{{ $t('booking.payment_method') }}</h2>
      <div class="payment-methods">
        <button
          class="payment-method"
          :class="{ 'payment-method--selected': paymentMethod === 'payme' }"
          @click="paymentMethod = 'payme'"
        >
          <img src="/images/payme-logo.svg" alt="Payme" />
        </button>
        <button
          class="payment-method"
          :class="{ 'payment-method--selected': paymentMethod === 'click' }"
          @click="paymentMethod = 'click'"
        >
          <img src="/images/click-logo.svg" alt="Click" />
        </button>
      </div>
    </section>

    <!-- Offer Acceptance -->
    <section class="section">
      <label class="offer-checkbox">
        <input
          type="checkbox"
          v-model="offerValue"
        />
        <span class="offer-checkbox__text">
          {{ $t('booking.accept_offer') }}
          <a href="/offer" target="_blank">{{ $t('booking.offer_link') }}</a>
        </span>
      </label>
    </section>

    <!-- Sticky Footer -->
    <div class="sticky-footer">
      <button class="back-button" @click="$emit('back')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="15 18 9 12 15 6" />
        </svg>
      </button>
      <div class="footer-price">{{ formatPrice(totalPrice) }}</div>
      <BaseButton
        variant="primary"
        :disabled="!canSubmit"
        :loading="loading"
        @click="$emit('submit')"
      >
        {{ $t('booking.pay') }}
      </BaseButton>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import BaseInput from '@/Components/Public/BaseInput.vue'
import BaseButton from '@/Components/Public/BaseButton.vue'

const { t } = useI18n()

const props = defineProps({
  serviceParams: {
    type: Object,
    required: true,
  },
  selectedMaster: {
    type: Object,
    default: null,
  },
  selectedDate: {
    type: String,
    default: null,
  },
  selectedSlot: {
    type: Object,
    default: null,
  },
  totalPrice: {
    type: Number,
    default: 0,
  },
  address: {
    type: String,
    default: '',
  },
  phone: {
    type: String,
    default: '',
  },
  offerAccepted: {
    type: Boolean,
    default: false,
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:address', 'update:phone', 'update:offer-accepted', 'back', 'submit'])

// Local state for payment method
const paymentMethod = ref('payme')

// Computed v-model bindings
const addressValue = computed({
  get: () => props.address,
  set: (val) => emit('update:address', val),
})

const phoneValue = computed({
  get: () => props.phone,
  set: (val) => emit('update:phone', val),
})

const offerValue = computed({
  get: () => props.offerAccepted,
  set: (val) => emit('update:offer-accepted', val),
})

// Computed
const serviceName = computed(() => {
  return t('booking.selected_service')
})

const masterName = computed(() => {
  return props.selectedMaster?.name || t('booking.auto_select')
})

const formattedDateTime = computed(() => {
  if (!props.selectedDate || !props.selectedSlot) return ''
  const date = new Date(props.selectedDate)
  const dateStr = date.toLocaleDateString('uz-UZ', { day: 'numeric', month: 'long' })
  return `${dateStr}, ${props.selectedSlot.display}`
})

const canSubmit = computed(() => {
  return props.address.trim() !== '' &&
         props.phone.trim() !== '' &&
         props.offerAccepted
})

// Methods
function formatPrice(price) {
  return new Intl.NumberFormat('uz-UZ').format(price) + ' so\'m'
}
</script>

<style scoped>
.step-payment {
  padding-bottom: 100px;
}

.section {
  margin-bottom: 1.5rem;
}

.section__title {
  font-size: 1rem;
  font-weight: 600;
  color: #2d3748;
  margin-bottom: 0.75rem;
}

.order-summary {
  background: white;
  border-radius: 12px;
  padding: 1rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid #e2e8f0;
}

.summary-row:last-child {
  border-bottom: none;
}

.summary-row__label {
  color: #718096;
  font-size: 0.875rem;
}

.summary-row__value {
  color: #2d3748;
  font-weight: 500;
  font-size: 0.875rem;
}

.summary-row--total {
  margin-top: 0.5rem;
  padding-top: 1rem;
  border-top: 2px solid #e2e8f0;
  border-bottom: none;
}

.summary-row--total .summary-row__value {
  font-size: 1.125rem;
  font-weight: 700;
  color: #48bb78;
}

.payment-methods {
  display: flex;
  gap: 0.75rem;
}

.payment-method {
  flex: 1;
  padding: 1rem;
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.payment-method:hover {
  border-color: #cbd5e0;
}

.payment-method--selected {
  border-color: #4299e1;
  background: #ebf8ff;
}

.payment-method img {
  height: 24px;
  width: auto;
}

.offer-checkbox {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  cursor: pointer;
}

.offer-checkbox input {
  width: 20px;
  height: 20px;
  margin-top: 2px;
  cursor: pointer;
}

.offer-checkbox__text {
  font-size: 0.875rem;
  color: #4a5568;
  line-height: 1.5;
}

.offer-checkbox__text a {
  color: #4299e1;
  text-decoration: underline;
}

.sticky-footer {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: white;
  padding: 1rem;
  box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 1rem;
  z-index: 50;
}

.back-button {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  border: 2px solid #e2e8f0;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.back-button:hover {
  border-color: #cbd5e0;
  background: #f7fafc;
}

.back-button svg {
  width: 20px;
  height: 20px;
  color: #718096;
}

.footer-price {
  flex: 1;
  font-weight: 700;
  font-size: 1.125rem;
  color: #2d3748;
}
</style>
