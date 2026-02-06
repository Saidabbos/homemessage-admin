<script setup>
/**
 * LoadingSpinner - Animated loading indicator
 * 
 * @prop {String} size - Spinner size: sm | md | lg | xl
 * @prop {String} color - Spinner color: gold | gray | white
 * @prop {String} text - Optional loading text
 */
defineProps({
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value),
    },
    color: {
        type: String,
        default: 'gold',
        validator: (value) => ['gold', 'gray', 'white'].includes(value),
    },
    text: {
        type: String,
        default: '',
    },
});

const sizeClasses = {
    sm: 'w-4 h-4',
    md: 'w-6 h-6',
    lg: 'w-8 h-8',
    xl: 'w-12 h-12',
};

const colorClasses = {
    gold: 'text-gold',
    gray: 'text-gray-400',
    white: 'text-white',
};
</script>

<template>
    <div class="flex flex-col items-center justify-center gap-3">
        <svg 
            :class="['animate-spin', sizeClasses[size], colorClasses[color]]"
            xmlns="http://www.w3.org/2000/svg" 
            fill="none" 
            viewBox="0 0 24 24"
        >
            <circle 
                class="opacity-25" 
                cx="12" cy="12" r="10" 
                stroke="currentColor" 
                stroke-width="4"
            />
            <path 
                class="opacity-75" 
                fill="currentColor" 
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            />
        </svg>
        <span v-if="text" class="text-sm text-gray-500">{{ text }}</span>
    </div>
</template>

<style scoped>
.text-gold {
    color: #C9A55C;
}
</style>
