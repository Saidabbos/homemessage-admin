<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const { t, locale } = useI18n();

const props = defineProps({
    master: Object,
    month: String,
    ordersByDate: Object,
    totalOrders: Number,
});

const currentMonth = ref(props.month);
const selectedDate = ref(null);

// Calendar helpers
const monthStart = computed(() => {
    const [year, month] = currentMonth.value.split('-');
    return new Date(year, month - 1, 1);
});

const monthEnd = computed(() => {
    const [year, month] = currentMonth.value.split('-');
    return new Date(year, month, 0);
});

const monthName = computed(() => {
    const names = {
        uz: ['Yanvar', 'Fevral', 'Mart', 'Aprel', 'May', 'Iyun', 'Iyul', 'Avgust', 'Sentyabr', 'Oktyabr', 'Noyabr', 'Dekabr'],
        ru: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
        en: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December']
    };
    const monthIndex = monthStart.value.getMonth();
    const year = monthStart.value.getFullYear();
    return `${names[locale.value]?.[monthIndex] || names.uz[monthIndex]} ${year}`;
});

const weekDays = computed(() => {
    const days = {
        uz: ['Du', 'Se', 'Ch', 'Pa', 'Ju', 'Sh', 'Ya'],
        ru: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'],
        en: ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su']
    };
    return days[locale.value] || days.uz;
});

const calendarDays = computed(() => {
    const days = [];
    const firstDayOfWeek = (monthStart.value.getDay() + 6) % 7; // Monday = 0
    const totalDays = monthEnd.value.getDate();
    
    // Empty cells before first day
    for (let i = 0; i < firstDayOfWeek; i++) {
        days.push({ day: null, date: null });
    }
    
    // Days of month
    for (let day = 1; day <= totalDays; day++) {
        const [year, month] = currentMonth.value.split('-');
        const dateStr = `${year}-${month.padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const orders = props.ordersByDate?.[dateStr] || [];
        days.push({
            day,
            date: dateStr,
            orders,
            hasOrders: orders.length > 0,
            isToday: dateStr === new Date().toISOString().split('T')[0],
        });
    }
    
    return days;
});

const selectedDateOrders = computed(() => {
    if (!selectedDate.value) return [];
    return props.ordersByDate?.[selectedDate.value] || [];
});

const selectedDateFormatted = computed(() => {
    if (!selectedDate.value) return '';
    const [year, month, day] = selectedDate.value.split('-');
    const date = new Date(year, month - 1, day);
    const weekDayNames = {
        uz: ['Yakshanba', 'Dushanba', 'Seshanba', 'Chorshanba', 'Payshanba', 'Juma', 'Shanba'],
        ru: ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'],
        en: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
    };
    const weekDay = weekDayNames[locale.value]?.[date.getDay()] || weekDayNames.uz[date.getDay()];
    return `${day} ${monthName.value.split(' ')[0]} - ${weekDay}`;
});

const changeMonth = (delta) => {
    const [year, month] = currentMonth.value.split('-').map(Number);
    const newDate = new Date(year, month - 1 + delta, 1);
    const newMonth = `${newDate.getFullYear()}-${String(newDate.getMonth() + 1).padStart(2, '0')}`;
    
    router.get(route('admin.masters.schedule', props.master.id), {
        month: newMonth
    }, {
        preserveState: true,
        only: ['ordersByDate', 'month', 'totalOrders']
    });
    
    currentMonth.value = newMonth;
    selectedDate.value = null;
};

const selectDate = (dateStr) => {
    if (dateStr) {
        selectedDate.value = selectedDate.value === dateStr ? null : dateStr;
    }
};

const getStatusColor = (status) => {
    const colors = {
        'NEW': 'bg-blue-100 text-blue-800',
        'CONFIRMING': 'bg-yellow-100 text-yellow-800',
        'CONFIRMED': 'bg-green-100 text-green-800',
        'IN_PROGRESS': 'bg-purple-100 text-purple-800',
        'COMPLETED': 'bg-gray-100 text-gray-800',
        'CANCELLED': 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getStatusLabel = (status) => {
    const labels = {
        'NEW': t('orders.statuses.NEW'),
        'CONFIRMING': t('orders.statuses.CONFIRMING'),
        'CONFIRMED': t('orders.statuses.CONFIRMED'),
        'IN_PROGRESS': t('orders.statuses.IN_PROGRESS'),
        'COMPLETED': t('orders.statuses.COMPLETED'),
        'CANCELLED': t('orders.statuses.CANCELLED'),
    };
    return labels[status] || status;
};
</script>

<template>
    <div class="p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                <Link :href="route('admin.masters.index')" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">{{ master.full_name }}</h1>
                    <p class="text-gray-500">{{ t('masters.schedule') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Link :href="route('admin.masters.show', master.id)" class="btn btn-secondary">
                    {{ t('common.view') }}
                </Link>
                <Link :href="route('admin.masters.edit', master.id)" class="btn btn-primary">
                    {{ t('common.edit') }}
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Calendar -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow">
                    <!-- Month Navigation -->
                    <div class="flex items-center justify-between p-4 border-b">
                        <button @click="changeMonth(-1)" class="p-2 hover:bg-gray-100 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <h2 class="text-lg font-semibold">{{ monthName }}</h2>
                        <button @click="changeMonth(1)" class="p-2 hover:bg-gray-100 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="p-4">
                        <!-- Week days header -->
                        <div class="grid grid-cols-7 mb-2">
                            <div v-for="day in weekDays" :key="day" class="text-center text-sm font-medium text-gray-500 py-2">
                                {{ day }}
                            </div>
                        </div>

                        <!-- Days grid -->
                        <div class="grid grid-cols-7 gap-1">
                            <div
                                v-for="(cell, index) in calendarDays"
                                :key="index"
                                @click="selectDate(cell.date)"
                                :class="[
                                    'relative min-h-[80px] p-2 rounded-lg transition-all',
                                    cell.day ? 'cursor-pointer hover:bg-gray-50' : '',
                                    cell.isToday ? 'bg-blue-50 border-2 border-blue-300' : 'border border-gray-100',
                                    selectedDate === cell.date ? 'ring-2 ring-blue-500 bg-blue-50' : '',
                                ]"
                            >
                                <span
                                    v-if="cell.day"
                                    :class="[
                                        'text-sm font-medium',
                                        cell.isToday ? 'text-blue-600' : 'text-gray-700'
                                    ]"
                                >
                                    {{ cell.day }}
                                </span>

                                <!-- Order indicators -->
                                <div v-if="cell.hasOrders" class="mt-1 space-y-1">
                                    <div
                                        v-for="(order, i) in cell.orders.slice(0, 3)"
                                        :key="order.id"
                                        :class="[
                                            'text-xs px-1 py-0.5 rounded truncate',
                                            getStatusColor(order.status)
                                        ]"
                                    >
                                        {{ order.time_start }}
                                    </div>
                                    <div v-if="cell.orders.length > 3" class="text-xs text-gray-500 text-center">
                                        +{{ cell.orders.length - 3 }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="px-4 py-3 border-t bg-gray-50 rounded-b-lg">
                        <span class="text-sm text-gray-600">
                            {{ t('masters.total_orders_month') }}: <strong>{{ totalOrders }}</strong>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Selected Day Orders -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow sticky top-6">
                    <div class="p-4 border-b">
                        <h3 class="font-semibold text-gray-800">
                            {{ selectedDate ? selectedDateFormatted : t('masters.select_day') }}
                        </h3>
                    </div>

                    <div class="p-4">
                        <div v-if="!selectedDate" class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p>{{ t('masters.click_day_to_view') }}</p>
                        </div>

                        <div v-else-if="selectedDateOrders.length === 0" class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p>{{ t('masters.no_orders_day') }}</p>
                        </div>

                        <div v-else class="space-y-3">
                            <Link
                                v-for="order in selectedDateOrders"
                                :key="order.id"
                                :href="route('admin.orders.show', order.id)"
                                class="block p-3 border rounded-lg hover:bg-gray-50 transition-colors"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-mono text-sm text-blue-600">{{ order.order_number }}</span>
                                    <span :class="['text-xs px-2 py-0.5 rounded', getStatusColor(order.status)]">
                                        {{ getStatusLabel(order.status) }}
                                    </span>
                                </div>
                                <div class="text-sm">
                                    <div class="flex items-center gap-2 text-gray-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>{{ order.time_start }} - {{ order.time_end }}</span>
                                        <span class="text-gray-400">({{ order.duration_minutes }} min)</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-gray-600 mt-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                        <span>{{ order.service_name }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-gray-600 mt-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span>{{ order.customer_name }}</span>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.btn {
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-weight: 500;
    transition: color 0.15s, background-color 0.15s;
}
.btn-primary {
    background-color: #2563eb;
    color: white;
}
.btn-primary:hover {
    background-color: #1d4ed8;
}
.btn-secondary {
    background-color: #f3f4f6;
    color: #374151;
}
.btn-secondary:hover {
    background-color: #e5e7eb;
}
</style>
