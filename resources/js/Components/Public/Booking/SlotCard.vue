<template>
  <button
    class="slot-card"
    :class="{ 'slot-card--selected': selected }"
    @click="$emit('click')"
  >
    <span class="slot-card__time">{{ slotData.window_start }}</span>
    <span class="slot-card__masters" v-if="slotData.available_masters_count">
      {{ slotData.available_masters_count }}
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
        <circle cx="12" cy="7" r="4" />
      </svg>
    </span>
  </button>
</template>

<script setup>
defineProps({
  slotData: {
    type: Object,
    required: true,
  },
  selected: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['click'])
</script>

<style scoped>
.slot-card {
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  padding: 0.75rem 0.5rem;
  cursor: pointer;
  text-align: center;
  transition: all 0.2s ease;
}

.slot-card:hover {
  border-color: #4299e1;
  background: #ebf8ff;
}

.slot-card--selected {
  background: #4299e1;
  border-color: #4299e1;
  color: white;
}

.slot-card__time {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #2d3748;
}

.slot-card--selected .slot-card__time {
  color: white;
}

.slot-card__masters {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 2px;
  font-size: 0.625rem;
  color: #718096;
  margin-top: 4px;
}

.slot-card--selected .slot-card__masters {
  color: rgba(255, 255, 255, 0.8);
}

.slot-card__masters svg {
  width: 10px;
  height: 10px;
}
</style>
