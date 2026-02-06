<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'

const { t, locale } = useI18n()

const props = defineProps({
    modelValue: {
        type: String,
        default: null
    },
    days: {
        type: Number,
        default: 7
    }
})

const emit = defineEmits(['update:modelValue'])

const selectedDate = ref(props.modelValue)

// Generate dates for the next N days
const dates = computed(() => {
    const result = []
    const today = new Date()
    
    for (let i = 0; i < props.days; i++) {
        const date = new Date(today)
        date.setDate(today.getDate() + i)
        
        result.push({
            date: date.toISOString().split('T')[0],
            dayName: getDayName(date, i === 0),
            dayNumber: date.getDate(),
            month: getMonthName(date),
            isToday: i === 0
        })
    }
    
    return result
})

const getDayName = (date, isToday) => {
    if (isToday) {
        return t('public.date.today')
    }
    
    const days = {
        uz: ['Yak', 'Dush', 'Sesh', 'Chor', 'Pay', 'Jum', 'Shan'],
        ru: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
        en: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
    }
    
    return days[locale.value]?.[date.getDay()] || days.uz[date.getDay()]
}

const getMonthName = (date) => {
    const months = {
        uz: ['Yan', 'Fev', 'Mar', 'Apr', 'May', 'Iyun', 'Iyul', 'Avg', 'Sen', 'Okt', 'Noy', 'Dek'],
        ru: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
        en: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
    }
    
    return months[locale.value]?.[date.getMonth()] || months.uz[date.getMonth()]
}

const selectDate = (dateStr) => {
    selectedDate.value = dateStr
    emit('update:modelValue', dateStr)
}

// Auto-select today on mount if no value
onMounted(() => {
    if (!selectedDate.value && dates.value.length > 0) {
        selectDate(dates.value[0].date)
    }
})
</script>

<template>
    <div class="date-selector">
        <div class="date-chips-container">
            <div class="date-chips">
                <button
                    v-for="day in dates"
                    :key="day.date"
                    class="date-chip"
                    :class="{ 
                        active: selectedDate === day.date,
                        today: day.isToday 
                    }"
                    @click="selectDate(day.date)"
                >
                    <span class="day-name">{{ day.dayName }}</span>
                    <span class="day-number">{{ day.dayNumber }}</span>
                    <span class="day-month">{{ day.month }}</span>
                </button>
            </div>
        </div>
    </div>
</template>
