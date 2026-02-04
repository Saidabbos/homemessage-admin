<script setup>
defineProps({
  modelValue: String,
  label: String,
  error: String,
  help: String,
  placeholder: String,
  type: {
    type: String,
    default: 'text',
  },
});

defineEmits(['update:modelValue', 'blur']);
</script>

<template>
  <div>
    <label v-if="label" class="block text-sm font-medium text-gray-700 mb-2">
      {{ label }}
    </label>
    <input
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      @blur="$emit('blur')"
      :type="type"
      :placeholder="placeholder"
      :class="[
        'w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 transition',
        error
          ? 'border-red-300 focus:ring-red-500'
          : 'border-gray-300 focus:ring-purple-500'
      ]"
    />
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    <p v-if="help" class="mt-1 text-xs text-gray-500">{{ help }}</p>
  </div>
</template>
