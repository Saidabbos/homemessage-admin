<script setup>
defineProps({
  modelValue: Boolean,
  label: String,
});

defineEmits(['update:modelValue']);
</script>

<template>
  <div class="flex items-center gap-3">
    <input
      :checked="modelValue"
      @change="$emit('update:modelValue', $event.target.checked)"
      type="checkbox"
      :id="label"
      class="w-4 h-4 rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50 cursor-pointer"
    />
    <label v-if="label" :for="label" class="text-sm font-medium text-gray-700 cursor-pointer">
      {{ label }}
    </label>
  </div>
</template>
