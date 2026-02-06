<script setup>
/**
 * BaseChip - Reusable chip/badge component for slot statuses
 * 
 * @prop {String} color - Chip color: green | yellow | gray | red | gold
 * @prop {String} size - Chip size: sm | md | lg
 * @prop {Boolean} selectable - Makes chip selectable/clickable
 * @prop {Boolean} selected - Shows selected state
 */
defineProps({
    color: {
        type: String,
        default: 'gray',
        validator: (value) => ['green', 'yellow', 'gray', 'red', 'gold'].includes(value),
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
    selectable: {
        type: Boolean,
        default: false,
    },
    selected: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['click']);

// Color mapping for slot statuses:
// green = FREE (available)
// yellow = PENDING (awaiting confirmation)
// gray = RESERVED (booked)
// red = BLOCKED (unavailable)
// gold = brand accent

const colorClasses = {
    green: {
        base: 'bg-green-100 text-green-700 border-green-200',
        selected: 'bg-green-500 text-white border-green-500',
    },
    yellow: {
        base: 'bg-yellow-100 text-yellow-700 border-yellow-200',
        selected: 'bg-yellow-500 text-white border-yellow-500',
    },
    gray: {
        base: 'bg-gray-100 text-gray-600 border-gray-200',
        selected: 'bg-gray-500 text-white border-gray-500',
    },
    red: {
        base: 'bg-red-100 text-red-700 border-red-200',
        selected: 'bg-red-500 text-white border-red-500',
    },
    gold: {
        base: 'bg-amber-100 text-amber-700 border-amber-200',
        selected: 'bg-gold text-white border-gold',
    },
};

const sizeClasses = {
    sm: 'px-2 py-0.5 text-xs',
    md: 'px-3 py-1 text-sm',
    lg: 'px-4 py-1.5 text-base',
};
</script>

<template>
    <span
        @click="selectable && $emit('click', $event)"
        :class="[
            'inline-flex items-center justify-center rounded-full font-medium border transition-all duration-200',
            sizeClasses[size],
            selected ? colorClasses[color].selected : colorClasses[color].base,
            selectable ? 'cursor-pointer hover:opacity-80 active:scale-95' : '',
        ]"
    >
        <slot />
    </span>
</template>

<style scoped>
.bg-gold {
    background-color: #C9A55C;
}
.border-gold {
    border-color: #C9A55C;
}
</style>
