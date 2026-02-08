<template>
  <div class="step-master-time">
    <div class="form-area">
      <!-- Master Selection -->
      <section class="form-section">
        <h3 class="section-label">{{ $t('booking.select_master') }}</h3>

        <!-- Search Input -->
        <div class="search-input">
          <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.35-4.35"></path>
          </svg>
          <input
            type="text"
            :placeholder="$t('booking.search_master')"
            class="search-field"
            @input="searchQuery = $event.target.value"
          />
        </div>

        <!-- Masters Grid -->
        <div class="masters-grid">
          <button
            v-for="m in filteredMasters"
            :key="m.id"
            class="master-card"
            :class="{ 'master-card--selected': master?.id === m.id }"
            @click="$emit('update:master', m)"
          >
            <div class="master-avatar">
              <div class="avatar-placeholder">
                {{ getInitials(m.full_name) }}
              </div>
            </div>
            <p class="master-name">{{ m.full_name }}</p>
            <p class="master-specialty">{{ m.specialty || 'Massaj' }}</p>
          </button>
        </div>
      </section>

      <!-- Date Selection -->
      <section class="form-section">
        <h3 class="section-label">{{ $t('booking.select_date') }}</h3>

        <div class="dates-row" v-if="!loading.dates">
          <button
            v-for="d in dates"
            :key="d.date"
            class="date-chip"
            :class="{ 'date-chip--selected': date === d.date, 'date-chip--disabled': !d.has_slots }"
            :disabled="!d.has_slots"
            @click="$emit('update:date', d.date)"
          >
            <span class="date-day">{{ getDayNumber(d.date) }}</span>
            <span class="date-label">{{ getDayLabel(d.date) }}</span>
          </button>
        </div>
        <div v-else class="loading-placeholder">{{ $t('common.loading') }}</div>
      </section>

      <!-- Slot Selection -->
      <section class="form-section">
        <h3 class="section-label">{{ $t('booking.select_time') }}</h3>

        <div v-if="loading.slots" class="loading-placeholder">{{ $t('common.loading') }}</div>

        <div v-else-if="slots.length === 0" class="empty-state">
          <p>{{ $t('booking.no_slots') }}</p>
        </div>

        <div v-else class="slots-grid">
          <button
            v-for="s in slots"
            :key="s.window_start"
            class="slot-card"
            :class="{ 'slot-card--selected': slot?.window_start === s.window_start }"
            @click="$emit('update:slot', s)"
          >
            {{ s.window_start }} - {{ s.window_end }}
          </button>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

// Search state
const searchQuery = ref('')

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

const filteredMasters = computed(() => {
  if (!searchQuery.value) {
    return props.masters
  }
  const query = searchQuery.value.toLowerCase()
  return props.masters.filter(m =>
    m.full_name.toLowerCase().includes(query) ||
    (m.specialty && m.specialty.toLowerCase().includes(query))
  )
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

function getDayNumber(dateStr) {
  if (!dateStr) return ''
  const date = new Date(dateStr + 'T00:00:00')
  return date.getDate()
}

function getDayLabel(dateStr) {
  if (!dateStr) return ''
  const date = new Date(dateStr + 'T00:00:00')
  const days = ['Yak', 'Dsh', 'Shs', 'Chra', 'Psh', 'Jma', 'Sha']
  return days[date.getDay()]
}

function getInitials(name) {
  if (!name) return '?'
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}
</script>

<style scoped>
.step-master-time {
  display: flex;
  flex-direction: column;
  gap: 28px;
  width: 100%;
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

.loading-placeholder {
  padding: 20px;
  text-align: center;
  color: rgba(27, 43, 90, 0.6);
  font-size: 13px;
}

.empty-state {
  padding: 20px;
  text-align: center;
  color: rgba(27, 43, 90, 0.6);
  font-size: 13px;
}

.empty-state p {
  margin: 0;
}

/* Search Input */
.search-input {
  display: flex;
  align-items: center;
  gap: 10px;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.5);
  backdrop-filter: blur(12px);
  padding: 10px 14px;
}

.search-icon {
  width: 16px;
  height: 16px;
  color: rgba(27, 43, 90, 0.4);
  flex-shrink: 0;
}

.search-field {
  flex: 1;
  border: none;
  background: transparent;
  font-family: 'Manrope', sans-serif;
  font-size: 13px;
  color: #1B2B5A;
  padding: 0;
  outline: none;
}

.search-field::placeholder {
  color: rgba(27, 43, 90, 0.4);
}

/* Masters Grid */
.masters-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
  gap: 10px;
}

.master-card {
  border-radius: 14px;
  background: rgba(255, 255, 255, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.5);
  backdrop-filter: blur(16px);
  padding: 12px 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-family: 'Manrope', sans-serif;
  min-height: 110px;
  justify-content: flex-start;
}

.master-card:hover {
  background: rgba(255, 255, 255, 0.5);
  border-color: rgba(255, 255, 255, 0.7);
}

.master-card--selected {
  background: rgba(255, 255, 255, 0.5);
  border: 2px solid #C8A951;
  box-shadow: 0 2px 8px rgba(200, 169, 81, 0.3);
}

.master-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  overflow: hidden;
  background: rgba(200, 169, 81, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
}

.avatar-placeholder {
  font-size: 16px;
  font-weight: 600;
  color: #C8A951;
}

.master-name {
  font-size: 12px;
  font-weight: 600;
  color: #1B2B5A;
  margin: 0;
  text-align: center;
  line-height: 1.2;
}

.master-specialty {
  font-size: 10px;
  color: rgba(27, 43, 90, 0.6);
  margin: 0;
  text-align: center;
}

/* Dates Row */
.dates-row {
  display: flex;
  gap: 6px;
  overflow-x: auto;
  overflow-y: hidden;
  padding-bottom: 4px;
  -webkit-overflow-scrolling: touch;
}

.dates-row::-webkit-scrollbar {
  height: 4px;
}

.dates-row::-webkit-scrollbar-thumb {
  background: rgba(200, 169, 81, 0.3);
  border-radius: 2px;
}

.date-chip {
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.5);
  backdrop-filter: blur(12px);
  padding: 8px 10px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-family: 'Manrope', sans-serif;
  flex-shrink: 0;
  min-width: fit-content;
}

.date-chip:hover:not(.date-chip--disabled) {
  background: rgba(255, 255, 255, 0.5);
  border-color: rgba(255, 255, 255, 0.7);
}

.date-chip--selected {
  background: #C8A951;
  border-color: #C8A951;
  box-shadow: 0 2px 8px rgba(200, 169, 81, 0.3);
}

.date-chip--selected .date-day,
.date-chip--selected .date-label {
  color: white;
}

.date-chip--disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.date-day {
  font-size: 14px;
  font-weight: 700;
  color: #1B2B5A;
}

.date-label {
  font-size: 10px;
  color: rgba(27, 43, 90, 0.6);
  font-weight: 500;
}

/* Slots Grid */
.slots-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
}

.slot-card {
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.5);
  backdrop-filter: blur(12px);
  padding: 12px;
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
  text-align: center;
}

.slot-card:hover {
  background: rgba(255, 255, 255, 0.5);
  border-color: rgba(255, 255, 255, 0.7);
}

.slot-card--selected {
  background: #C8A951;
  border-color: #C8A951;
  color: white;
  box-shadow: 0 2px 8px rgba(200, 169, 81, 0.3);
}

@media (max-width: 768px) {
  .masters-grid {
    grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
  }

  .date-chip {
    padding: 6px 8px;
    font-size: 12px;
  }

  .slot-card {
    font-size: 12px;
    min-height: 45px;
  }
}
</style>
