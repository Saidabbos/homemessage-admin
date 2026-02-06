<script setup>
/**
 * BaseInput - Reusable input component for public booking flow
 * 
 * @prop {String} modelValue - Input value (v-model)
 * @prop {String} label - Input label
 * @prop {String} error - Error message
 * @prop {String} help - Help text
 * @prop {String} placeholder - Input placeholder
 * @prop {String} type - Input type: text | email | tel | password | number
 * @prop {Boolean} required - Shows required indicator
 * @prop {Boolean} disabled - Disables the input
 */
defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    label: {
        type: String,
        default: '',
    },
    error: {
        type: String,
        default: '',
    },
    help: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: '',
    },
    type: {
        type: String,
        default: 'text',
    },
    required: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    inputClass: {
        type: String,
        default: '',
    },
});

defineEmits(['update:modelValue', 'blur', 'focus']);
</script>

<template>
    <div class="w-full">
        <!-- Label -->
        <label v-if="label" class="block text-sm font-medium text-gray-700 mb-1.5">
            {{ label }}
            <span v-if="required" class="text-red-500 ml-0.5">*</span>
        </label>
        
        <!-- Input -->
        <input
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            @blur="$emit('blur', $event)"
            @focus="$emit('focus', $event)"
            :type="type"
            :placeholder="placeholder"
            :disabled="disabled"
            :required="required"
            :class="[
                'w-full px-4 py-3 border rounded-xl text-base transition-all duration-200',
                'focus:outline-none focus:ring-2',
                error
                    ? 'border-red-300 focus:ring-red-200 focus:border-red-400 bg-red-50'
                    : 'border-gray-200 focus:ring-gold/20 focus:border-gold bg-white',
                disabled ? 'bg-gray-100 cursor-not-allowed text-gray-500' : '',
                inputClass
            ]"
        />
        
        <!-- Error message -->
        <p v-if="error" class="mt-1.5 text-sm text-red-600 flex items-center gap-1">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            {{ error }}
        </p>
        
        <!-- Help text -->
        <p v-else-if="help" class="mt-1.5 text-sm text-gray-500">
            {{ help }}
        </p>
    </div>
</template>

<style scoped>
.focus\:ring-gold\/20:focus {
    --tw-ring-color: rgba(201, 165, 92, 0.2);
}
.focus\:border-gold:focus {
    border-color: #C9A55C;
}
</style>
