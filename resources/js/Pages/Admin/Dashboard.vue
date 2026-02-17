<script setup>
import { computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps({
    stats: Object,
    orderStats: Object,
    todayOrders: Array,
    upcomingOrders: Array,
    recentOrders: Array,
    ratingStats: Object,
    recentRatings: Array,
    topMasters: Array,
    revenueStats: Object,
    ordersTrend: Array,
    paymentStats: Object,
    popularServices: Array,
    masterPerformance: Array,
});

const formatMoney = (amount) => {
    return new Intl.NumberFormat('uz-UZ').format(amount || 0);
};

const maxTrendOrders = computed(() => {
    return Math.max(...(props.ordersTrend?.map(d => d.orders) || [1]), 1);
});

const maxTrendRevenue = computed(() => {
    return Math.max(...(props.ordersTrend?.map(d => d.revenue) || [1]), 1);
});

const getStatusColor = (status) => {
    const colors = {
        'NEW': 'bg-blue-100 text-blue-800',
        'CONFIRMING': 'bg-yellow-100 text-yellow-800',
        'CONFIRMED': 'bg-green-100 text-green-800',
        'IN_PROGRESS': 'bg-purple-100 text-purple-800',
        'COMPLETED': 'bg-gray-100 text-gray-600',
        'CANCELLED': 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-600';
};

const getStatusLabel = (status) => {
    return t(`orderStatuses.${status}`) || status;
};

const getPaymentColor = (status) => {
    return status === 'PAID' ? 'text-green-600' : 'text-red-500';
};

const formatRating = (rating) => {
    return rating ? parseFloat(rating).toFixed(1) : '-';
};

const getRatingStars = (rating) => {
    const full = Math.floor(rating || 0);
    const half = (rating || 0) - full >= 0.5;
    return { full, half, empty: 5 - full - (half ? 1 : 0) };
};
</script>

<template>
    <div>
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">{{ t('admin.dashboard.title') }}</h1>
            <p class="text-gray-500 text-sm">{{ t('admin.dashboard.subtitle') }}</p>
        </div>

        <!-- Order Status Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <!-- New Orders -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-blue-500 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-3xl font-bold text-blue-600">{{ orderStats?.new || 0 }}</p>
                        <p class="text-sm text-gray-500">{{ t('admin.dashboard.statusNew') }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Confirming -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-yellow-500 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-3xl font-bold text-yellow-600">{{ orderStats?.confirming || 0 }}</p>
                        <p class="text-sm text-gray-500">{{ t('admin.dashboard.statusConfirming') }}</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Confirmed -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-green-500 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-3xl font-bold text-green-600">{{ orderStats?.confirmed || 0 }}</p>
                        <p class="text-sm text-gray-500">{{ t('admin.dashboard.statusConfirmed') }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- In Progress -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-purple-500 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-3xl font-bold text-purple-600">{{ orderStats?.in_progress || 0 }}</p>
                        <p class="text-sm text-gray-500">{{ t('admin.dashboard.statusInProgress') }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Completed Today -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-gray-400 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-3xl font-bold text-gray-600">{{ orderStats?.completed_today || 0 }}</p>
                        <p class="text-sm text-gray-500">{{ t('admin.dashboard.statusCompletedToday') }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl p-4 text-white">
                <p class="text-sm opacity-80">{{ t('admin.dashboard.revenueToday') }}</p>
                <p class="text-2xl font-bold">{{ formatMoney(revenueStats?.today) }}</p>
                <p class="text-xs opacity-70">{{ t('admin.dashboard.sum') }}</p>
            </div>
            <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl p-4 text-white">
                <p class="text-sm opacity-80">{{ t('admin.dashboard.revenueWeek') }}</p>
                <p class="text-2xl font-bold">{{ formatMoney(revenueStats?.week) }}</p>
                <p class="text-xs opacity-70">{{ t('admin.dashboard.sum') }}</p>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl p-4 text-white">
                <p class="text-sm opacity-80">{{ t('admin.dashboard.revenueMonth') }}</p>
                <p class="text-2xl font-bold">{{ formatMoney(revenueStats?.month) }}</p>
                <p class="text-xs opacity-70">{{ t('admin.dashboard.sum') }}</p>
            </div>
            <div class="bg-gradient-to-br from-gray-700 to-gray-900 rounded-xl p-4 text-white">
                <p class="text-sm opacity-80">{{ t('admin.dashboard.revenueTotal') }}</p>
                <p class="text-2xl font-bold">{{ formatMoney(revenueStats?.total) }}</p>
                <p class="text-xs opacity-70">{{ t('admin.dashboard.sum') }}</p>
            </div>
        </div>

        <!-- Orders Trend Chart -->
        <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
            <h2 class="font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                </svg>
                {{ t('admin.dashboard.trendTitle') }}
            </h2>
            <div class="flex items-end justify-between h-40 gap-2">
                <div v-for="day in ordersTrend" :key="day.date" class="flex-1 flex flex-col items-center">
                    <div class="w-full flex flex-col items-center gap-1 flex-1 justify-end">
                        <span class="text-xs font-semibold text-gray-700">{{ day.orders }}</span>
                        <div 
                            class="w-full bg-blue-500 rounded-t transition-all"
                            :style="{ height: (day.orders / maxTrendOrders * 100) + 'px', minHeight: day.orders > 0 ? '8px' : '2px' }"
                        ></div>
                    </div>
                    <div class="mt-2 text-center">
                        <p class="text-xs font-medium text-gray-600">{{ day.day }}</p>
                        <p class="text-xs text-gray-400">{{ day.date }}</p>
                    </div>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-center gap-6 text-sm">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-blue-500 rounded"></span>
                    <span class="text-gray-600">{{ t('admin.dashboard.trendOrders') }}</span>
                </div>
            </div>
        </div>

        <!-- Stats Row: Payment + Services + Master Performance -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Payment Stats -->
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    {{ t('admin.dashboard.paymentStatus') }}
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                            <span class="text-gray-600">{{ t('admin.dashboard.paymentPaid') }}</span>
                        </div>
                        <span class="font-semibold text-green-600">{{ paymentStats?.paid || 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 bg-red-500 rounded-full"></span>
                            <span class="text-gray-600">{{ t('admin.dashboard.paymentUnpaid') }}</span>
                        </div>
                        <span class="font-semibold text-red-600">{{ paymentStats?.unpaid || 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 bg-yellow-500 rounded-full"></span>
                            <span class="text-gray-600">{{ t('admin.dashboard.paymentPending') }}</span>
                        </div>
                        <span class="font-semibold text-yellow-600">{{ paymentStats?.pending || 0 }}</span>
                    </div>
                </div>
                <!-- Progress bar -->
                <div class="mt-4 h-2 bg-gray-100 rounded-full overflow-hidden flex">
                    <div class="bg-green-500 h-full" :style="{ width: (paymentStats?.paid / (paymentStats?.paid + paymentStats?.unpaid + paymentStats?.pending || 1) * 100) + '%' }"></div>
                    <div class="bg-yellow-500 h-full" :style="{ width: (paymentStats?.pending / (paymentStats?.paid + paymentStats?.unpaid + paymentStats?.pending || 1) * 100) + '%' }"></div>
                    <div class="bg-red-500 h-full" :style="{ width: (paymentStats?.unpaid / (paymentStats?.paid + paymentStats?.unpaid + paymentStats?.pending || 1) * 100) + '%' }"></div>
                </div>
            </div>

            <!-- Popular Services -->
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    {{ t('admin.dashboard.popularServices') }}
                </h3>
                <div v-if="popularServices?.length" class="space-y-3">
                    <div v-for="(service, i) in popularServices" :key="service.id" class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                            :class="i === 0 ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600'">
                            {{ i + 1 }}
                        </span>
                        <span class="flex-1 text-gray-700 truncate">{{ service.name }}</span>
                        <span class="font-semibold text-gray-800">{{ service.orders_count }}</span>
                    </div>
                </div>
                <div v-else class="text-center text-gray-500 py-4 text-sm">
                    {{ t('admin.dashboard.noData') }}
                </div>
            </div>

            <!-- Master Performance -->
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    {{ t('admin.dashboard.masterPerformance') }}
                </h3>
                <div v-if="masterPerformance?.length" class="space-y-3">
                    <div v-for="master in masterPerformance" :key="master.id" class="flex items-center gap-3">
                        <img v-if="master.photo" :src="master.photo" class="w-8 h-8 rounded-full object-cover" />
                        <div v-else class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-sm">
                            {{ master.name?.charAt(0) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ master.name }}</p>
                            <p class="text-xs text-gray-500">{{ master.orders_count }} {{ t('admin.dashboard.ordersCount') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-green-600">{{ formatMoney(master.revenue) }}</p>
                            <div v-if="master.rating" class="flex items-center justify-end">
                                <svg class="w-3 h-3 text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                <span class="text-xs text-gray-600 ml-1">{{ master.rating }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center text-gray-500 py-4 text-sm">
                    {{ t('admin.dashboard.noData') }}
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Today's Orders -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm">
                <div class="px-5 py-4 border-b flex items-center justify-between">
                    <h2 class="font-semibold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ t('admin.dashboard.todayOrders') }}
                    </h2>
                    <Link href="/admin/orders" class="text-sm text-blue-600 hover:text-blue-800">
                        {{ t('admin.dashboard.viewAll') }} →
                    </Link>
                </div>

                <div v-if="todayOrders?.length" class="divide-y">
                    <Link 
                        v-for="order in todayOrders" 
                        :key="order.id"
                        :href="`/admin/orders/${order.id}`"
                        class="flex items-center px-5 py-3 hover:bg-gray-50 transition"
                    >
                        <div class="w-16 text-center">
                            <span class="text-lg font-semibold text-gray-800">{{ order.time?.split('-')[0] }}</span>
                        </div>
                        <div class="flex-1 ml-4">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-gray-800">{{ order.customer || t('orders.customer') }}</span>
                                <span :class="['text-xs px-2 py-0.5 rounded-full', getStatusColor(order.status)]">
                                    {{ getStatusLabel(order.status) }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-500 mt-0.5">
                                {{ order.service }} • {{ order.master }}
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs font-mono text-gray-400">{{ order.order_number }}</span>
                            <div :class="['text-xs mt-0.5', getPaymentColor(order.payment_status)]">
                                {{ order.payment_status === 'PAID' ? t('admin.dashboard.paymentPaid') : t('admin.dashboard.paymentUnpaid') }}
                            </div>
                        </div>
                    </Link>
                </div>

                <div v-else class="p-8 text-center text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p>{{ t('admin.dashboard.noOrdersToday') }}</p>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Upcoming Orders -->
                <div class="bg-white rounded-xl shadow-sm">
                    <div class="px-5 py-4 border-b">
                        <h2 class="font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ t('admin.dashboard.upcomingOrders') }}
                        </h2>
                    </div>

                    <div v-if="upcomingOrders?.length" class="divide-y">
                        <Link 
                            v-for="order in upcomingOrders" 
                            :key="order.id"
                            :href="`/admin/orders/${order.id}`"
                            class="flex items-center px-5 py-3 hover:bg-gray-50 transition"
                        >
                            <div class="w-12 h-12 bg-gray-100 rounded-lg flex flex-col items-center justify-center">
                                <span class="text-xs text-gray-500">{{ order.date }}</span>
                                <span class="text-sm font-semibold">{{ order.time }}</span>
                            </div>
                            <div class="ml-3 flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ order.customer }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ order.master }}</p>
                            </div>
                            <span :class="['text-xs px-2 py-0.5 rounded-full', getStatusColor(order.status)]">
                                {{ getStatusLabel(order.status) }}
                            </span>
                        </Link>
                    </div>

                    <div v-else class="p-6 text-center text-gray-500 text-sm">
                        {{ t('admin.dashboard.noUpcoming') }}
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <h2 class="font-semibold text-gray-800 mb-4">{{ t('admin.dashboard.generalStats') }}</h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">{{ t('admin.dashboard.totalOrders') }}</span>
                            <span class="font-semibold">{{ stats?.total_orders || 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">{{ t('admin.dashboard.activeMasters') }}</span>
                            <span class="font-semibold">{{ stats?.total_masters || 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">{{ t('admin.dashboard.customers') }}</span>
                            <span class="font-semibold">{{ stats?.total_customers || 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">{{ t('admin.dashboard.serviceTypes') }}</span>
                            <span class="font-semibold">{{ stats?.total_service_types || 0 }}</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <h2 class="font-semibold text-gray-800 mb-4">{{ t('admin.dashboard.quickActions') }}</h2>
                    <div class="space-y-2">
                        <Link href="/admin/orders?status=NEW" class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                            <svg class="w-5 h-5 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span class="text-sm font-medium text-blue-700">{{ t('admin.dashboard.viewNewOrders') }}</span>
                        </Link>
                        <Link href="/admin/masters" class="flex items-center p-3 bg-green-50 hover:bg-green-100 rounded-lg transition">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-sm font-medium text-green-700">{{ t('admin.dashboard.mastersSchedule') }}</span>
                        </Link>
                        <Link href="/admin/customers" class="flex items-center p-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition">
                            <svg class="w-5 h-5 text-purple-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span class="text-sm font-medium text-purple-700">{{ t('admin.dashboard.customersList') }}</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rating Section -->
        <div class="mt-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2 text-yellow-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                {{ t('admin.dashboard.ratingStats') }}
            </h2>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Rating Stats Cards -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Master Rating -->
                    <div class="bg-gradient-to-br from-yellow-400 to-orange-500 rounded-xl p-4 text-white">
                        <div class="text-3xl font-bold">{{ formatRating(ratingStats?.avg_master_rating) }}</div>
                        <div class="text-sm opacity-90">{{ t('admin.dashboard.masterRating') }}</div>
                        <div class="flex mt-2">
                            <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= Math.round(ratingStats?.avg_master_rating || 0) ? 'text-white' : 'text-white/40'" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Client Rating -->
                    <div class="bg-gradient-to-br from-green-400 to-emerald-500 rounded-xl p-4 text-white">
                        <div class="text-3xl font-bold">{{ formatRating(ratingStats?.avg_client_rating) }}</div>
                        <div class="text-sm opacity-90">{{ t('admin.dashboard.clientRating') }}</div>
                        <div class="flex mt-2">
                            <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= Math.round(ratingStats?.avg_client_rating || 0) ? 'text-white' : 'text-white/40'" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Total Ratings -->
                    <div class="bg-white rounded-xl p-4 shadow-sm border">
                        <div class="text-3xl font-bold text-gray-800">{{ ratingStats?.total_ratings || 0 }}</div>
                        <div class="text-sm text-gray-500">{{ t('admin.dashboard.totalRatings') }}</div>
                    </div>

                    <!-- Pending Ratings -->
                    <div class="bg-white rounded-xl p-4 shadow-sm border">
                        <div class="text-3xl font-bold text-orange-500">{{ ratingStats?.pending_ratings || 0 }}</div>
                        <div class="text-sm text-gray-500">{{ t('admin.dashboard.pendingRatings') }}</div>
                    </div>
                </div>

                <!-- Top Masters -->
                <div class="bg-white rounded-xl shadow-sm">
                    <div class="px-5 py-4 border-b">
                        <h3 class="font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-yellow-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                            {{ t('admin.dashboard.topMasters') }}
                        </h3>
                    </div>
                    <div v-if="topMasters?.length" class="divide-y">
                        <div v-for="(master, index) in topMasters" :key="master.id" class="flex items-center px-5 py-3">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold" 
                                :class="index === 0 ? 'bg-yellow-100 text-yellow-700' : index === 1 ? 'bg-gray-100 text-gray-600' : index === 2 ? 'bg-orange-100 text-orange-700' : 'bg-gray-50 text-gray-500'">
                                {{ index + 1 }}
                            </span>
                            <img v-if="master.photo" :src="master.photo" class="w-10 h-10 rounded-full ml-3 object-cover" />
                            <div v-else class="w-10 h-10 rounded-full ml-3 bg-gray-200 flex items-center justify-center text-gray-500 text-sm">
                                {{ master.name?.charAt(0) }}
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="font-medium text-gray-800">{{ master.name }}</p>
                                <p class="text-xs text-gray-500">{{ master.rating_count }} {{ t('admin.dashboard.ratingsCount') }}</p>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                                <span class="ml-1 font-semibold text-gray-800">{{ master.rating }}</span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="p-6 text-center text-gray-500 text-sm">
                        {{ t('admin.dashboard.noRatingsYet') }}
                    </div>
                </div>

                <!-- Recent Ratings -->
                <div class="bg-white rounded-xl shadow-sm">
                    <div class="px-5 py-4 border-b">
                        <h3 class="font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ t('admin.dashboard.recentRatings') }}
                        </h3>
                    </div>
                    <div v-if="recentRatings?.length" class="divide-y">
                        <div v-for="rating in recentRatings" :key="rating.id" class="px-5 py-3">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-gray-800">
                                    {{ rating.type === 'client_to_master' ? rating.customer_name : rating.master_name }}
                                </span>
                                <div class="flex">
                                    <svg v-for="i in 5" :key="i" class="w-3 h-3" :class="i <= rating.overall_rating ? 'text-yellow-400' : 'text-gray-200'" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex items-center text-xs text-gray-500">
                                <span :class="rating.type === 'client_to_master' ? 'text-purple-600' : 'text-green-600'">
                                    {{ rating.type === 'client_to_master' ? t('admin.dashboard.clientToMaster') : t('admin.dashboard.masterToClient') }}
                                </span>
                                <span class="mx-2">•</span>
                                <span>{{ rating.rated_at }}</span>
                            </div>
                            <p v-if="rating.feedback" class="text-xs text-gray-600 mt-1 truncate">"{{ rating.feedback }}"</p>
                        </div>
                    </div>
                    <div v-else class="p-6 text-center text-gray-500 text-sm">
                        {{ t('admin.dashboard.noRatingsYet') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
