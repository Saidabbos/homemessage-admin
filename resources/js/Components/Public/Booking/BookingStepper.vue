<template>
  <div class="stepper">
    <div
      v-for="(step, index) in steps"
      :key="step.id"
      class="step"
      :class="{
        'step--done': step.id < currentStep,
        'step--active': step.id === currentStep,
        'step--todo': step.id > currentStep,
      }"
      @click="handleClick(step.id)"
    >
      <div class="step__indicator">
        <svg v-if="step.id < currentStep" class="step__check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <polyline points="20 6 9 17 4 12" />
        </svg>
        <span v-else class="step__number">{{ step.id }}</span>
      </div>
      <span class="step__name">{{ step.name }}</span>
      
      <!-- Connector line -->
      <div v-if="index < steps.length - 1" class="step__connector" />
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
  align-items: flex-start;
  justify-content: center;
  gap: 0;
}

.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  flex: 1;
  cursor: pointer;
}

.step--todo {
  cursor: default;
}

.step__indicator {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.3s ease;
  position: relative;
  z-index: 1;
}

.step--done .step__indicator {
  background: #48bb78;
  color: white;
}

.step--active .step__indicator {
  background: #4299e1;
  color: white;
  box-shadow: 0 0 0 4px rgba(66, 153, 225, 0.2);
}

.step--todo .step__indicator {
  background: #e2e8f0;
  color: #a0aec0;
}

.step__check {
  width: 16px;
  height: 16px;
}

.step__number {
  font-size: 14px;
}

.step__name {
  margin-top: 8px;
  font-size: 12px;
  color: #718096;
  text-align: center;
  white-space: nowrap;
}

.step--active .step__name {
  color: #4299e1;
  font-weight: 600;
}

.step--done .step__name {
  color: #48bb78;
}

.step__connector {
  position: absolute;
  top: 16px;
  left: calc(50% + 16px);
  width: calc(100% - 32px);
  height: 2px;
  background: #e2e8f0;
  z-index: 0;
}

.step--done .step__connector,
.step--active .step__connector {
  background: #48bb78;
}

/* Mobile adjustments */
@media (max-width: 480px) {
  .step__name {
    font-size: 10px;
  }
  
  .step__indicator {
    width: 28px;
    height: 28px;
    font-size: 12px;
  }
}
</style>
