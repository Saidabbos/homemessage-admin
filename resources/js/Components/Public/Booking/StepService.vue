<template>
  <div class="step-service">
    <!-- Loading -->
    <div v-if="loading" class="loading-state">
      <p>{{ $t('common.loading') }}</p>
    </div>

    <template v-else>
      <div class="form-area">
        <!-- Service Type Selection -->
        <section class="form-section">
          <h3 class="section-label">{{ $t('booking.massage_type') }}</h3>
          <div class="service-grid">
            <button
              v-for="service in services"
              :key="service.id"
              class="service-card"
              :class="{ 'service-card--selected': modelValue.service_type_id === service.id }"
              @click="selectService(service)"
            >
              <div class="service-card-content">
                <div class="service-icon">
                  <svg class="icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M8 12h8M12 8v8"></path>
                  </svg>
                </div>
                <p class="service-name">{{ service.name }}</p>
              </div>
            </button>
          </div>
        </section>

        <!-- Duration Selection -->
        <section class="form-section">
          <h3 class="section-label">{{ $t('booking.duration') }}</h3>
          <div class="options-grid">
            <button
              v-for="dur in [60, 90, 120]"
              :key="dur"
              class="option-btn"
              :class="{ 'option-btn--selected': modelValue.duration === dur }"
              @click="updateParam('duration', dur)"
            >
              <span class="option-value">{{ dur }}</span>
              <span class="option-unit">{{ $t('common.minutes') }}</span>
            </button>
          </div>
        </section>

        <!-- Pressure Level Selection -->
        <section class="form-section">
          <h3 class="section-label">{{ $t('booking.pressure_level') }}</h3>
          <div class="options-grid">
            <button
              class="option-btn"
              :class="{ 'option-btn--selected': modelValue.pressure_level === 'light' }"
              @click="updateParam('pressure_level', 'light')"
            >
              <span class="option-label">{{ $t('booking.light') }}</span>
            </button>
            <button
              class="option-btn"
              :class="{ 'option-btn--selected': modelValue.pressure_level === 'medium' }"
              @click="updateParam('pressure_level', 'medium')"
            >
              <span class="option-label">{{ $t('booking.medium') }}</span>
            </button>
            <button
              class="option-btn"
              :class="{ 'option-btn--selected': modelValue.pressure_level === 'heavy' }"
              @click="updateParam('pressure_level', 'heavy')"
            >
              <span class="option-label">{{ $t('booking.heavy') }}</span>
            </button>
          </div>
        </section>

        <!-- People Count Selection -->
        <section class="form-section">
          <h3 class="section-label">{{ $t('booking.people_count') }}</h3>
          <div class="options-grid">
            <button
              v-for="count in [1, 2, 3, 4]"
              :key="count"
              class="option-btn"
              :class="{ 'option-btn--selected': modelValue.people_count === count }"
              @click="updateParam('people_count', count)"
            >
              <span class="option-label">{{ count }}</span>
            </button>
          </div>
        </section>

        <!-- Oil Selection (conditional - only for Relax Oil Massage) -->
        <section v-if="isRelaxSelected" class="form-section">
          <h3 class="section-label">{{ $t('booking.select_oil') }}</h3>
          <div class="oil-grid">
            <button
              v-for="oil in oils"
              :key="oil.id"
              class="oil-card"
              :class="{ 'oil-card--selected': selectedOilId === oil.id }"
              @click="selectOil(oil)"
            >
              {{ oil.name }}
            </button>
            <button
              class="oil-card"
              :class="{ 'oil-card--selected': selectedOilId === null }"
              @click="selectOil(null)"
            >
              {{ $t('booking.no_preference') }}
            </button>
          </div>
        </section>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

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

// Methods
function selectService(service) {
  selectedOilId.value = null
  emit('update:modelValue', {
    ...props.modelValue,
    service_type_id: service.id,
    massage_type: service.slug,
  })
}

function updateParam(key, value) {
  emit('update:modelValue', {
    ...props.modelValue,
    [key]: value,
  })
}

function selectOil(oil) {
  selectedOilId.value = oil?.id || null
}
</script>

<style scoped>
.step-service {
  display: flex;
  flex-direction: column;
  gap: 28px;
  width: 100%;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
  color: rgba(27, 43, 90, 0.6);
  font-size: 14px;
}

.form-area {
  display: flex;
  flex-direction: column;
  gap: 28px;
}

.form-section {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.section-label {
  font-family: 'Manrope', sans-serif;
  font-size: 12px;
  font-weight: 600;
  color: #1B2B5A;
  letter-spacing: 1px;
  text-transform: uppercase;
  margin: 0;
}

/* Service Type Grid */
.service-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
  gap: 10px;
}

.service-card {
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.5);
  backdrop-filter: blur(16px);
  padding: 16px 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  min-height: 100px;
  aspect-ratio: 1;
  flex-direction: column;
  font-family: 'Manrope', sans-serif;
}

.service-card:hover {
  background: rgba(255, 255, 255, 0.5);
  border-color: rgba(255, 255, 255, 0.7);
}

.service-card--selected {
  background: rgba(255, 255, 255, 0.5);
  border: 2px solid #C8A951;
  box-shadow: 0 2px 8px rgba(200, 169, 81, 0.3);
  color: #C8A951;
}

.service-card-content {
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  width: 100%;
}

.service-icon {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #1B2B5A;
}

.service-card--selected .service-icon {
  color: #C8A951;
}

.icon-svg {
  width: 100%;
  height: 100%;
  stroke-width: 2;
}

.service-name {
  font-family: 'Manrope', sans-serif;
  font-size: 12px;
  font-weight: 600;
  color: #1B2B5A;
  margin: 0;
  line-height: 1.2;
}

/* Options Grid */
.options-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
}

.option-btn {
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.5);
  backdrop-filter: blur(12px);
  padding: 12px 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 2px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-family: 'Manrope', sans-serif;
}

.option-btn:hover {
  background: rgba(255, 255, 255, 0.5);
  border-color: rgba(255, 255, 255, 0.7);
}

.option-btn--selected {
  background: #C8A951;
  border-color: #C8A951;
  box-shadow: 0 2px 8px rgba(200, 169, 81, 0.3);
}

.option-btn--selected .option-value,
.option-btn--selected .option-unit,
.option-btn--selected .option-label {
  color: #1B2B5A;
}

.option-value {
  font-size: 18px;
  font-weight: 700;
  color: #1B2B5A;
}

.option-unit {
  font-size: 10px;
  color: rgba(27, 43, 90, 0.6);
  font-weight: 500;
}

.option-label {
  font-size: 13px;
  font-weight: 600;
  color: #1B2B5A;
}

/* Oil Grid */
.oil-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
}

.oil-card {
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.5);
  backdrop-filter: blur(16px);
  padding: 14px 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  font-family: 'Manrope', sans-serif;
  font-size: 13px;
  font-weight: 600;
  color: #1B2B5A;
  min-height: 50px;
}

.oil-card:hover {
  background: rgba(255, 255, 255, 0.5);
  border-color: rgba(255, 255, 255, 0.7);
}

.oil-card--selected {
  background: rgba(255, 255, 255, 0.5);
  border: 2px solid #C8A951;
  box-shadow: 0 2px 8px rgba(200, 169, 81, 0.3);
}

@media (max-width: 768px) {
  .form-section {
    gap: 10px;
  }

  .section-label {
    font-size: 11px;
  }

  .option-btn {
    padding: 10px 0;
  }

  .option-value {
    font-size: 16px;
  }
}
</style>
