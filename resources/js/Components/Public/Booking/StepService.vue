<template>
  <div class="step-service">
    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <LoadingSpinner />
      <p>Yuklanmoqda...</p>
    </div>

    <template v-else>
      <!-- Service Type Selection -->
      <section class="section">
        <h2 class="section__title">{{ $t('booking.select_service') }}</h2>
        <div class="services-container">
          <div
            v-for="service in services"
            :key="service.id"
            class="service-option"
            :class="{ 'service-option--selected': modelValue.service_type_id === service.id }"
            @click="selectService(service)"
          >
            <div class="service-option__header">
              <h3 class="service-option__title">{{ service.name }}</h3>
            </div>
            <p class="service-option__description">{{ service.description }}</p>
            <div class="service-option__price">{{ formatPrice(service.min_price) }}</div>
          </div>
        </div>
      </section>

      <!-- Oil Selection (conditional - only for Relax Oil Massage) -->
      <section v-if="isRelaxSelected" class="section oil-section">
        <h2 class="section__title">{{ $t('booking.select_oil') }}</h2>
        <div class="oil-options">
          <button
            v-for="oil in oils"
            :key="oil.id"
            class="oil-button"
            :class="{ 'oil-button--selected': selectedOilId === oil.id }"
            @click="selectOil(oil)"
          >
            {{ oil.name }}
          </button>
          <button
            class="oil-button"
            :class="{ 'oil-button--selected': selectedOilId === null }"
            @click="selectOil(null)"
          >
            {{ $t('booking.no_preference') }}
          </button>
        </div>
      </section>

      <!-- Summary & Next Button -->
      <div class="sticky-footer">
        <div class="summary-info">
          <span class="summary-label">{{ $t('booking.total') }}:</span>
          <span class="summary-price" v-if="totalPrice > 0">
            {{ formatPrice(totalPrice) }}
          </span>
        </div>
        <BaseButton
          variant="primary"
          :disabled="!canProceed"
          @click="$emit('next')"
        >
          {{ $t('booking.next') }}
        </BaseButton>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import BaseButton from '@/Components/Public/BaseButton.vue'
import LoadingSpinner from '@/Components/Public/LoadingSpinner.vue'

const { t } = useI18n()

const props = defineProps({
  modelValue: {
    type: Object,
    required: true,
  },
  services: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue', 'next'])

// Oil selection state
const selectedOilId = ref(null)
const oils = ref([
  { id: 1, name: t('booking.oil_coconut') },
  { id: 2, name: t('booking.oil_lavender') },
])

// Computed
const selectedService = computed(() => {
  return props.services.find(s => s.id === props.modelValue.service_type_id)
})

const isRelaxSelected = computed(() => {
  return selectedService.value?.slug === 'relax-oil-massage' || selectedService.value?.slug === 'relax'
})

const totalPrice = computed(() => {
  if (!selectedService.value) return 0
  return selectedService.value.min_price || selectedService.value.price || 0
})

const canProceed = computed(() => {
  // Can proceed if service is selected, and if Relax is selected, an oil must be chosen
  if (!props.modelValue.service_type_id) return false
  if (isRelaxSelected.value && selectedOilId.value === null && selectedOilId.value !== 'no-preference') {
    return true // Allow "no preference" as a valid selection
  }
  return true
})

// Methods
function selectService(service) {
  selectedOilId.value = null // Reset oil selection when changing service
  emit('update:modelValue', {
    ...props.modelValue,
    service_type_id: service.id,
    massage_type: service.slug,
  })
}

function selectOil(oil) {
  selectedOilId.value = oil?.id || 'no-preference'
}

function formatPrice(price) {
  return new Intl.NumberFormat('uz-UZ').format(price) + ' soʻm'
}
</script>

<style scoped>
.step-service {
  padding-bottom: 140px;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  color: #718096;
}

.section {
  margin-bottom: 1.5rem;
  padding: 0 1rem;
}

.section__title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1a1a1a;
  margin-bottom: 0.75rem;
  text-transform: capitalize;
}

.services-container {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.service-option {
  background: rgba(255, 255, 255, 0.5);
  backdrop-filter: blur(24px);
  border: 1px solid rgba(255, 255, 255, 0.8);
  border-radius: 20px;
  padding: 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.service-option:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.service-option--selected {
  background: rgba(255, 255, 255, 0.7);
  border: 1.5px solid rgba(160, 136, 80, 0.4);
  box-shadow: 0 4px 24px rgba(160, 136, 80, 0.15);
}

.service-option__header {
  margin-bottom: 0.5rem;
}

.service-option__title {
  font-size: 1rem;
  font-weight: 600;
  color: #1a1a1a;
  margin: 0;
}

.service-option__description {
  font-size: 0.875rem;
  color: #666666;
  margin: 0.25rem 0;
}

.service-option__price {
  font-size: 0.875rem;
  font-weight: 600;
  color: #A08850;
  margin-top: 0.5rem;
}

.oil-section {
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.oil-options {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.oil-button {
  background: rgba(255, 255, 255, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.7);
  border-radius: 12px;
  padding: 0.75rem 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: #1a1a1a;
  cursor: pointer;
  transition: all 0.2s ease;
  text-align: center;
}

.oil-button:hover {
  background: rgba(255, 255, 255, 0.5);
  border-color: rgba(255, 255, 255, 0.8);
}

.oil-button--selected {
  background: rgba(255, 255, 255, 0.5);
  border-color: rgba(160, 136, 80, 0.4);
  box-shadow: 0 2px 12px rgba(160, 136, 80, 0.1);
}

.sticky-footer {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(40px);
  border-top: 1px solid rgba(255, 255, 255, 0.9);
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  z-index: 50;
  box-shadow: 0 -4px 24px rgba(0, 0, 0, 0.15);
}

.summary-info {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.summary-label {
  font-size: 0.875rem;
  color: #666666;
  font-weight: 400;
}

.summary-price {
  font-size: 1rem;
  font-weight: 600;
  color: #A08850;
  white-space: nowrap;
}

@media (max-width: 480px) {
  .section {
    padding: 0 1rem;
  }
}
</style>
