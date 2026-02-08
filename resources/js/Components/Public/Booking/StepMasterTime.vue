<template>
  <div class="step-master-time">
    <!-- Service Summary -->
    <section class="summary-card" @click="$emit('back')">
      <div class="summary-card__content">
        <span class="summary-card__label">{{ serviceName }}</span>
        <span class="summary-card__details">
          {{ serviceParams.duration }} min • {{ serviceParams.people_count }} kishi • {{ pressureLabel }}
        </span>
      </div>
      <button class="summary-card__edit">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
          <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
        </svg>
        O'zgartirish
      </button>
    </section>

    <!-- Master Selection -->
    <section class="section">
      <h2 class="section__title">{{ $t('booking.select_master') }}</h2>
      <div class="masters-carousel">
        <!-- All Masters Option -->
        <MasterCard
          :master="{ id: null, name: $t('booking.all_masters'), first_name: $t('booking.auto_select') }"
          :selected="master === null"
          :is-all="true"
          @click="$emit('update:master', null)"
        />
        
        <!-- Individual Masters -->
        <MasterCard
          v-for="m in masters"
          :key="m.id"
          :master="m"
          :selected="master?.id === m.id"
          @click="$emit('update:master', m)"
        />
      </div>
    </section>

    <!-- Date Selection -->
    <section class="section">
      <h2 class="section__title">{{ $t('booking.select_date') }}</h2>
      <div class="dates-row" v-if="!loading.dates">
        <DateChip
          v-for="d in dates"
          :key="d.date"
          :date="d"
          :selected="date === d.date"
          :disabled="!d.has_slots"
          @click="d.has_slots && $emit('update:date', d.date)"
        />
      </div>
      <div v-else class="loading-inline">
        <LoadingSpinner size="small" />
      </div>
    </section>

    <!-- Slot Selection -->
    <section class="section">
      <h2 class="section__title">{{ $t('booking.select_time') }}</h2>
      
      <div v-if="loading.slots" class="loading-inline">
        <LoadingSpinner size="small" />
      </div>
      
      <div v-else-if="slots.length === 0" class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10" />
          <line x1="12" y1="8" x2="12" y2="12" />
          <line x1="12" y1="16" x2="12.01" y2="16" />
        </svg>
        <p>{{ $t('booking.no_slots') }}</p>
      </div>
      
      <div v-else class="slots-grid">
        <SlotCard
          v-for="s in slots"
          :key="s.window_start"
          :slot-data="s"
          :selected="slot?.window_start === s.window_start"
          @click="$emit('update:slot', s)"
        />
      </div>
    </section>

    <!-- Reminder -->
    <section class="reminder-card">
      <h3 class="reminder-card__title">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
          <polyline points="14 2 14 8 20 8" />
          <line x1="16" y1="13" x2="8" y2="13" />
          <line x1="16" y1="17" x2="8" y2="17" />
          <polyline points="10 9 9 9 8 9" />
        </svg>
        {{ $t('booking.reminder_title') }}
      </h3>
      <ul class="reminder-card__list">
        <li>{{ $t('booking.reminder_1') }}</li>
        <li>{{ $t('booking.reminder_2') }}</li>
        <li>{{ $t('booking.reminder_3') }}</li>
      </ul>
    </section>

    <!-- Sticky Footer -->
    <div class="sticky-footer">
      <div class="footer-summary">
        <div class="footer-summary__slot" v-if="slot">
          {{ formatDate(date) }}, {{ slot.display }}
        </div>
        <div class="footer-summary__master" v-if="selectedMasterName">
          {{ selectedMasterName }}
        </div>
      </div>
      <div class="footer-price">{{ formatPrice(totalPrice) }}</div>
      <BaseButton
        variant="primary"
        :disabled="!canProceed"
        @click="$emit('next')"
      >
        {{ $t('booking.next') }}
      </BaseButton>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import BaseButton from '@/Components/Public/BaseButton.vue'
import LoadingSpinner from '@/Components/Public/LoadingSpinner.vue'
import MasterCard from './MasterCard.vue'
import DateChip from './DateChip.vue'
import SlotCard from './SlotCard.vue'

const { t } = useI18n()

const props = defineProps({
  serviceParams: {
    type: Object,
    required: true,
  },
  master: {
    type: Object,
    default: null,
  },
  date: {
    type: String,
    default: null,
  },
  slot: {
    type: Object,
    default: null,
  },
  masters: {
    type: Array,
    default: () => [],
  },
  dates: {
    type: Array,
    default: () => [],
  },
  slots: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Object,
    default: () => ({}),
  },
  totalPrice: {
    type: Number,
    default: 0,
  },
})

const emit = defineEmits(['update:master', 'update:date', 'update:slot', 'back', 'next', 'update-slots'])

// Computed
const serviceName = computed(() => {
  // This would come from a service lookup in real app
  return t('booking.selected_service')
})

const pressureLabel = computed(() => {
  const labels = {
    soft: t('booking.pressure_soft'),
    medium: t('booking.pressure_medium'),
    hard: t('booking.pressure_hard'),
    any: t('booking.pressure_any'),
  }
  return labels[props.serviceParams.pressure_level] || ''
})

const selectedMasterName = computed(() => {
  if (!props.master) return t('booking.auto_select')
  return props.master.name
})

const canProceed = computed(() => {
  return props.date && props.slot
})

// Methods
function formatDate(dateStr) {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleDateString('uz-UZ', { day: 'numeric', month: 'short' })
}

function formatPrice(price) {
  return new Intl.NumberFormat('uz-UZ').format(price) + ' so\'m'
}
</script>

<style scoped>
.step-master-time {
  padding-bottom: 120px;
}

.summary-card {
  background: white;
  border-radius: 12px;
  padding: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  cursor: pointer;
}

.summary-card__label {
  font-weight: 600;
  color: #2d3748;
  display: block;
}

.summary-card__details {
  font-size: 0.75rem;
  color: #718096;
}

.summary-card__edit {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.75rem;
  color: #4299e1;
  background: none;
  border: none;
  cursor: pointer;
}

.summary-card__edit svg {
  width: 14px;
  height: 14px;
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

.masters-carousel {
  display: flex;
  gap: 0.75rem;
  overflow-x: auto;
  padding-bottom: 0.5rem;
  -webkit-overflow-scrolling: touch;
}

.masters-carousel::-webkit-scrollbar {
  display: none;
}

.dates-row {
  display: flex;
  gap: 0.5rem;
  overflow-x: auto;
  padding-bottom: 0.5rem;
  -webkit-overflow-scrolling: touch;
}

.dates-row::-webkit-scrollbar {
  display: none;
}

.slots-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0.5rem;
}

@media (max-width: 480px) {
  .slots-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

.loading-inline {
  display: flex;
  justify-content: center;
  padding: 2rem;
}

.empty-state {
  text-align: center;
  padding: 2rem;
  color: #a0aec0;
}

.empty-state svg {
  width: 48px;
  height: 48px;
  margin-bottom: 0.5rem;
}

.reminder-card {
  background: #ebf8ff;
  border-radius: 12px;
  padding: 1rem;
  margin-bottom: 1.5rem;
}

.reminder-card__title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #2b6cb0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.reminder-card__title svg {
  width: 18px;
  height: 18px;
}

.reminder-card__list {
  font-size: 0.75rem;
  color: #2c5282;
  padding-left: 1.5rem;
  margin: 0;
}

.reminder-card__list li {
  margin-bottom: 0.25rem;
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

.footer-summary {
  flex: 1;
}

.footer-summary__slot {
  font-weight: 600;
  color: #2d3748;
  font-size: 0.875rem;
}

.footer-summary__master {
  font-size: 0.75rem;
  color: #718096;
}

.footer-price {
  font-weight: 700;
  font-size: 1rem;
  color: #2d3748;
  white-space: nowrap;
}
</style>
