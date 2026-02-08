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
        <div class="services-grid">
          <ServiceCard
            v-for="service in services"
            :key="service.id"
            :service="service"
            :selected="modelValue.service_type_id === service.id"
            @click="selectService(service)"
          />
        </div>
      </section>

      <!-- Pressure Level -->
      <section class="section">
        <h2 class="section__title">{{ $t('booking.pressure_level') }}</h2>
        <div class="chips-row">
          <BaseChip
            v-for="level in pressureLevels"
            :key="level.value"
            :active="modelValue.pressure_level === level.value"
            @click="updateValue('pressure_level', level.value)"
          >
            {{ level.label }}
          </BaseChip>
        </div>
      </section>

      <!-- Duration -->
      <section class="section">
        <h2 class="section__title">{{ $t('booking.duration') }}</h2>
        <div class="chips-row">
          <BaseChip
            v-for="dur in durations"
            :key="dur.value"
            :active="modelValue.duration === dur.value"
            @click="updateValue('duration', dur.value)"
          >
            {{ dur.label }}
          </BaseChip>
        </div>
      </section>

      <!-- People Count -->
      <section class="section">
        <h2 class="section__title">{{ $t('booking.people_count') }}</h2>
        <div class="chips-row">
          <BaseChip
            v-for="count in peopleCounts"
            :key="count"
            :active="modelValue.people_count === count"
            @click="updateValue('people_count', count)"
          >
            {{ count }} {{ count === 1 ? $t('booking.person') : $t('booking.people') }}
          </BaseChip>
        </div>
      </section>

      <!-- Summary & Next Button -->
      <div class="sticky-footer">
        <div class="summary" v-if="selectedService">
          <div class="summary__service">{{ selectedService.name }}</div>
          <div class="summary__details">
            {{ modelValue.duration }} {{ $t('booking.min') }} • {{ modelValue.people_count }} {{ $t('booking.person') }}
          </div>
        </div>
        <div class="summary__price" v-if="totalPrice > 0">
          {{ formatPrice(totalPrice) }}
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
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import BaseChip from '@/Components/Public/BaseChip.vue'
import BaseButton from '@/Components/Public/BaseButton.vue'
import LoadingSpinner from '@/Components/Public/LoadingSpinner.vue'
import ServiceCard from './ServiceCard.vue'

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

// Options
const pressureLevels = [
  { value: 'soft', label: t('booking.pressure_soft') },
  { value: 'medium', label: t('booking.pressure_medium') },
  { value: 'hard', label: t('booking.pressure_hard') },
  { value: 'any', label: t('booking.pressure_any') },
]

const durations = [
  { value: 60, label: '60 ' + t('booking.min') },
  { value: 90, label: '90 ' + t('booking.min') },
  { value: 120, label: '120 ' + t('booking.min') },
]

const peopleCounts = [1, 2, 3, 4]

// Computed
const selectedService = computed(() => {
  return props.services.find(s => s.id === props.modelValue.service_type_id)
})

const totalPrice = computed(() => {
  if (!selectedService.value) return 0
  return selectedService.value.price * props.modelValue.people_count
})

const canProceed = computed(() => {
  return props.modelValue.service_type_id !== null
})

// Methods
function selectService(service) {
  emit('update:modelValue', {
    ...props.modelValue,
    service_type_id: service.id,
    massage_type: service.slug,
    duration: service.duration,
  })
}

function updateValue(key, value) {
  emit('update:modelValue', {
    ...props.modelValue,
    [key]: value,
  })
}

function formatPrice(price) {
  return new Intl.NumberFormat('uz-UZ').format(price) + ' so\'m'
}
</script>

<style scoped>
.step-service {
  padding-bottom: 100px;
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
}

.section__title {
  font-size: 1rem;
  font-weight: 600;
  color: #2d3748;
  margin-bottom: 0.75rem;
}

.services-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

.chips-row {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
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

.summary {
  flex: 1;
}

.summary__service {
  font-weight: 600;
  color: #2d3748;
  font-size: 0.875rem;
}

.summary__details {
  font-size: 0.75rem;
  color: #718096;
}

.summary__price {
  font-weight: 700;
  font-size: 1rem;
  color: #2d3748;
  white-space: nowrap;
}

@media (max-width: 480px) {
  .services-grid {
    grid-template-columns: 1fr;
  }
}
</style>
