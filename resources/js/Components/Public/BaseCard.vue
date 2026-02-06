<script setup>
/**
 * BaseCard - Reusable card component for public booking flow
 * 
 * @prop {String} variant - Card style: default | bordered | elevated
 * @prop {Boolean} hoverable - Adds hover effect
 * @prop {Boolean} clickable - Makes card clickable with cursor pointer
 * @prop {String} padding - Padding size: none | sm | md | lg
 */
defineProps({
    variant: {
        type: String,
        default: 'default',
        validator: (value) => ['default', 'bordered', 'elevated'].includes(value),
    },
    hoverable: {
        type: Boolean,
        default: false,
    },
    clickable: {
        type: Boolean,
        default: false,
    },
    padding: {
        type: String,
        default: 'md',
        validator: (value) => ['none', 'sm', 'md', 'lg'].includes(value),
    },
});

defineEmits(['click']);

const variantClasses = {
    default: 'bg-white border border-gray-100',
    bordered: 'bg-white border-2 border-gray-200',
    elevated: 'bg-white shadow-lg shadow-gray-100/50',
};

const paddingClasses = {
    none: 'p-0',
    sm: 'p-3',
    md: 'p-5',
    lg: 'p-7',
};
</script>

<template>
    <div
        @click="clickable && $emit('click', $event)"
        :class="[
            'rounded-2xl transition-all duration-200',
            variantClasses[variant],
            paddingClasses[padding],
            hoverable ? 'hover:shadow-xl hover:shadow-gray-100/50 hover:-translate-y-1' : '',
            clickable ? 'cursor-pointer' : '',
        ]"
    >
        <slot />
    </div>
</template>
