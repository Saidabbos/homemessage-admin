<script setup>
/**
 * BaseButton - Reusable button component for public booking flow
 * 
 * @prop {String} variant - Button style: primary | secondary | outline
 * @prop {Boolean} loading - Shows loading spinner
 * @prop {Boolean} disabled - Disables the button
 * @prop {String} type - Button type: button | submit | reset
 * @prop {String} size - Button size: sm | md | lg
 */
defineProps({
    type: {
        type: String,
        default: 'button',
    },
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'secondary', 'outline'].includes(value),
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
    loading: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['click']);

const variantClasses = {
    primary: 'bg-gold hover:bg-gold-dark text-white border-transparent',
    secondary: 'btn-secondary-sage border-transparent',
    outline: 'btn-outline-sage',
};

const sizeClasses = {
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-5 py-2.5 text-base',
    lg: 'px-7 py-3.5 text-lg',
};
</script>

<template>
    <button
        :type="type"
        :disabled="loading || disabled"
        @click="$emit('click', $event)"
        :class="[
            'inline-flex items-center justify-center gap-2 rounded-full font-medium border transition-all duration-200',
            variantClasses[variant],
            sizeClasses[size],
            (loading || disabled) 
                ? 'opacity-50 cursor-not-allowed' 
                : 'cursor-pointer active:scale-95'
        ]"
    >
        <!-- Loading spinner -->
        <svg 
            v-if="loading" 
            class="animate-spin h-4 w-4" 
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
        <slot />
    </button>
</template>

<style scoped>
.bg-gold {
    background-color: var(--c-gold);
}
.bg-gold-dark:hover {
    background-color: #B8944B;
}
.btn-secondary-sage {
    background-color: var(--c-sage-10);
    color: var(--c-forest);
}
.btn-secondary-sage:hover {
    background-color: var(--c-sage-20);
}
.btn-outline-sage {
    background: transparent;
    color: var(--c-sage-dark);
    border: 1px solid var(--c-sage-30);
}
.btn-outline-sage:hover {
    background-color: var(--c-sage-10);
}
</style>
