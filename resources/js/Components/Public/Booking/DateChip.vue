<template>
  <button
    class="date-chip"
    :class="{
      'date-chip--selected': selected,
      'date-chip--disabled': disabled,
      'date-chip--today': date.is_today,
    }"
    :disabled="disabled"
    @click="$emit('click')"
  >
    <span class="date-chip__day">{{ dayLabel }}</span>
    <span class="date-chip__date">{{ date.display }}</span>
  </button>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
  date: {
    type: Object,
    required: true,
  },
  selected: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['click'])

const dayLabel = computed(() => {
  if (props.date.is_today) return t('booking.today')
  if (props.date.is_tomorrow) return t('booking.tomorrow')
  return props.date.day_name?.substring(0, 3) || ''
})
</script>

<style scoped>
.date-chip {
  flex-shrink: 0;
  padding: 0.5rem 1rem;
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  cursor: pointer;
  text-align: center;
  transition: all 0.2s ease;
  min-width: 70px;
}

.date-chip:hover:not(:disabled) {
  border-color: #cbd5e0;
}

.date-chip--selected {
  background: #4299e1;
  border-color: #4299e1;
  color: white;
}

.date-chip--disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.date-chip--today:not(.date-chip--selected) {
  border-color: #48bb78;
}

.date-chip__day {
  display: block;
  font-size: 0.625rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #718096;
  margin-bottom: 2px;
}

.date-chip--selected .date-chip__day {
  color: rgba(255, 255, 255, 0.8);
}

.date-chip__date {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #2d3748;
}

.date-chip--selected .date-chip__date {
  color: white;
}
</style>
