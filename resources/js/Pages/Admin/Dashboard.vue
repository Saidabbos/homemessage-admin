<script setup>
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
    const labels = {
        'NEW': 'Yangi',
        'CONFIRMING': 'Tasdiqlanmoqda',
        'CONFIRMED': 'Tasdiqlangan',
        'IN_PROGRESS': 'Jarayonda',
        'COMPLETED': 'Tugallangan',
        'CANCELLED': 'Bekor qilingan',
    };
    return labels[status] || status;
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
            <h1 class="text-2xl font-bold text-gray-800">Dispetcher Dashboard</h1>
            <p class="text-gray-500 text-sm">Bugungi buyurtmalar va holat</p>
        </div>

        <!-- Order Status Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <!-- New Orders -->
            <div class="bg-white rounded-xl shadow-sm border-l-4 border-blue-500 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-3xl font-bold text-blue-600">{{ orderStats?.new || 0 }}</p>
                        <p class="text-sm text-gray-500">Yangi</p>
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
                        <p class="text-sm text-gray-500">Tasdiqlanmoqda</p>
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
                        <p class="text-sm text-gray-500">Tasdiqlangan</p>
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
                        <p class="text-sm text-gray-500">Jarayonda</p>
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
                        <p class="text-sm text-gray-500">Bugun tugadi</p>
                    </div>
                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
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
                        Bugungi buyurtmalar
                    </h2>
                    <Link href="/admin/orders" class="text-sm text-blue-600 hover:text-blue-800">
                        Barchasi →
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
                                <span class="font-medium text-gray-800">{{ order.customer || 'Mijoz' }}</span>
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
                                {{ order.payment_status === 'PAID' ? 'To\'langan' : 'To\'lanmagan' }}
                            </div>
                        </div>
                    </Link>
                </div>

                <div v-else class="p-8 text-center text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p>Bugun buyurtma yo'q</p>
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
                            Kelayotgan buyurtmalar
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
                        Kelayotgan buyurtmalar yo'q
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <h2 class="font-semibold text-gray-800 mb-4">Umumiy statistika</h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Jami buyurtmalar</span>
                            <span class="font-semibold">{{ stats?.total_orders || 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Faol masterlar</span>
                            <span class="font-semibold">{{ stats?.total_masters || 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Mijozlar</span>
                            <span class="font-semibold">{{ stats?.total_customers || 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Xizmat turlari</span>
                            <span class="font-semibold">{{ stats?.total_service_types || 0 }}</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm p-5">
                    <h2 class="font-semibold text-gray-800 mb-4">Tez harakatlar</h2>
                    <div class="space-y-2">
                        <Link href="/admin/orders?status=NEW" class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                            <svg class="w-5 h-5 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span class="text-sm font-medium text-blue-700">Yangi buyurtmalarni ko'rish</span>
                        </Link>
                        <Link href="/admin/masters" class="flex items-center p-3 bg-green-50 hover:bg-green-100 rounded-lg transition">
                            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-sm font-medium text-green-700">Masterlar jadvali</span>
                        </Link>
                        <Link href="/admin/customers" class="flex items-center p-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition">
                            <svg class="w-5 h-5 text-purple-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span class="text-sm font-medium text-purple-700">Mijozlar ro'yxati</span>
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
                Baholash statistikasi
            </h2>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Rating Stats Cards -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Master Rating -->
                    <div class="bg-gradient-to-br from-yellow-400 to-orange-500 rounded-xl p-4 text-white">
                        <div class="text-3xl font-bold">{{ formatRating(ratingStats?.avg_master_rating) }}</div>
                        <div class="text-sm opacity-90">Master reytingi</div>
                        <div class="flex mt-2">
                            <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= Math.round(ratingStats?.avg_master_rating || 0) ? 'text-white' : 'text-white/40'" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Client Rating -->
                    <div class="bg-gradient-to-br from-green-400 to-emerald-500 rounded-xl p-4 text-white">
                        <div class="text-3xl font-bold">{{ formatRating(ratingStats?.avg_client_rating) }}</div>
                        <div class="text-sm opacity-90">Mijoz reytingi</div>
                        <div class="flex mt-2">
                            <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= Math.round(ratingStats?.avg_client_rating || 0) ? 'text-white' : 'text-white/40'" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Total Ratings -->
                    <div class="bg-white rounded-xl p-4 shadow-sm border">
                        <div class="text-3xl font-bold text-gray-800">{{ ratingStats?.total_ratings || 0 }}</div>
                        <div class="text-sm text-gray-500">Jami baholar</div>
                    </div>

                    <!-- Pending Ratings -->
                    <div class="bg-white rounded-xl p-4 shadow-sm border">
                        <div class="text-3xl font-bold text-orange-500">{{ ratingStats?.pending_ratings || 0 }}</div>
                        <div class="text-sm text-gray-500">Kutilmoqda</div>
                    </div>
                </div>

                <!-- Top Masters -->
                <div class="bg-white rounded-xl shadow-sm">
                    <div class="px-5 py-4 border-b">
                        <h3 class="font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-yellow-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                            Top masterlar
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
                                <p class="text-xs text-gray-500">{{ master.rating_count }} ta baho</p>
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
                        Hali baholar yo'q
                    </div>
                </div>

                <!-- Recent Ratings -->
                <div class="bg-white rounded-xl shadow-sm">
                    <div class="px-5 py-4 border-b">
                        <h3 class="font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            So'nggi baholar
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
                                    {{ rating.type === 'client_to_master' ? 'Mijoz → Master' : 'Master → Mijoz' }}
                                </span>
                                <span class="mx-2">•</span>
                                <span>{{ rating.rated_at }}</span>
                            </div>
                            <p v-if="rating.feedback" class="text-xs text-gray-600 mt-1 truncate">"{{ rating.feedback }}"</p>
                        </div>
                    </div>
                    <div v-else class="p-6 text-center text-gray-500 text-sm">
                        Hali baholar yo'q
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
