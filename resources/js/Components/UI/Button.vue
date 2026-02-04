<script setup>
defineProps({
  type: {
    type: String,
    default: 'button',
  },
  loading: Boolean,
  disabled: Boolean,
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'danger'].includes(value),
  },
});

defineEmits(['click']);

const variantClasses = {
  primary: 'bg-purple-500 hover:bg-purple-600 text-white',
  secondary: 'bg-gray-500 hover:bg-gray-600 text-white',
  danger: 'bg-red-500 hover:bg-red-600 text-white',
};
</script>

<template>
  <button
    :type="type"
    :disabled="loading || disabled"
    @click="$emit('click')"
    :class="[
      'px-5 py-2.5 rounded-lg font-semibold transition-all duration-200 inline-flex items-center gap-2',
      variantClasses[variant],
      (loading || disabled) ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
    ]"
  >
    <span v-if="loading" class="inline-block animate-spin">⟳</span>
    <slot />
  </button>
</template>
