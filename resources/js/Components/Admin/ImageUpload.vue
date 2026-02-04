<script setup>
import { ref } from 'vue';

const props = defineProps({
  modelValue: [File, String],
  label: String,
  error: String,
  help: String,
  currentImage: String,
});

const emit = defineEmits(['update:modelValue']);

const preview = ref(null);

const onFileChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    emit('update:modelValue', file);
    const reader = new FileReader();
    reader.onload = (e) => {
      preview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};
</script>

<template>
  <div>
    <label v-if="label" class="block text-sm font-medium text-gray-700 mb-2">
      {{ label }}
    </label>
    <input
      type="file"
      @change="onFileChange"
      accept="image/*"
      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 cursor-pointer"
    />
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    <p v-if="help" class="mt-1 text-xs text-gray-500">{{ help }}</p>

    <!-- Image Preview -->
    <div v-if="preview || currentImage" class="mt-4">
      <img
        :src="preview || currentImage"
        alt="Preview"
        class="max-w-[200px] rounded-lg border border-gray-300 object-cover"
      />
    </div>
  </div>
</template>
