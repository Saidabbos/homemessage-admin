<template>
  <div class="stepper">
    <!-- Step 1 Indicator -->
    <div class="step-wrapper">
      <div
        class="step-indicator"
        :class="{
          'step-indicator--done': currentStep > 1,
          'step-indicator--active': currentStep === 1,
          'step-indicator--pending': currentStep < 1,
        }"
      >
        <svg v-if="currentStep > 1" class="step-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
          <polyline points="20 6 9 17 4 12" />
        </svg>
        <span v-else class="step-number">1</span>
      </div>
    </div>

    <!-- Connector Line 1 -->
    <div
      class="connector-line"
      :class="{
        'connector-line--active': currentStep > 1,
      }"
    ></div>

    <!-- Step 2 Indicator -->
    <div class="step-wrapper">
      <div
        class="step-indicator"
        :class="{
          'step-indicator--done': currentStep > 2,
          'step-indicator--active': currentStep === 2,
          'step-indicator--pending': currentStep < 2,
        }"
      >
        <svg v-if="currentStep > 2" class="step-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
          <polyline points="20 6 9 17 4 12" />
        </svg>
        <span v-else class="step-number">2</span>
      </div>
    </div>

    <!-- Connector Line 2 -->
    <div
      class="connector-line"
      :class="{
        'connector-line--active': currentStep > 2,
      }"
    ></div>

    <!-- Step 3 Indicator -->
    <div class="step-wrapper">
      <div
        class="step-indicator"
        :class="{
          'step-indicator--done': currentStep > 3,
          'step-indicator--active': currentStep === 3,
          'step-indicator--pending': currentStep < 3,
        }"
      >
        <svg v-if="currentStep > 3" class="step-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
          <polyline points="20 6 9 17 4 12" />
        </svg>
        <span v-else class="step-number">3</span>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  steps: {
    type: Array,
    required: true,
  },
  currentStep: {
    type: Number,
    default: 1,
  },
})

const emit = defineEmits(['step-click'])

function handleClick(stepId) {
  emit('step-click', stepId)
}
</script>

<style scoped>
.stepper {
  display: flex;
  align-items: center;
  gap: 10px;
}

.step-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
}

.step-indicator {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 14px;
  font-family: 'Manrope', sans-serif;
  transition: all 0.3s ease;
  flex-shrink: 0;
}

.step-indicator--active {
  background: #C8A951;
  color: #1B2B5A;
  box-shadow: 0 0 0 0px rgba(200, 169, 81, 0.3);
}

.step-indicator--done {
  background: #C8A951;
  color: white;
}

.step-indicator--pending {
  background: rgba(27, 43, 90, 0.1);
  color: rgba(27, 43, 90, 0.3);
}

.step-number {
  font-size: 14px;
  font-weight: 600;
}

.step-check {
  width: 16px;
  height: 16px;
}

.connector-line {
  width: 48px;
  height: 2px;
  background: rgba(27, 43, 90, 0.1);
  transition: all 0.3s ease;
  flex-shrink: 0;
}

.connector-line--active {
  background: #C8A951;
}

/* Mobile adjustments */
@media (max-width: 1024px) {
  .stepper {
    gap: 8px;
  }

  .step-indicator {
    width: 28px;
    height: 28px;
    font-size: 12px;
  }

  .connector-line {
    width: 36px;
  }
}
</style>
