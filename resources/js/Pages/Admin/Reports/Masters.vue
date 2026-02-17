<script setup>
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

defineOptions({ layout: AdminLayout });

const props = defineProps({
    filters: Object,
    masters: Array,
    summary: Object,
});

const localFilters = ref({
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
});

const applyFilters = () => {
    router.get('/admin/reports/masters', localFilters.value, { preserveState: true });
};

const formatMoney = (amount) => {
    return new Intl.NumberFormat('uz-UZ').format(amount || 0);
};

const maxRevenue = props.masters?.length 
    ? Math.max(...props.masters.map(m => m.revenue || 0), 1) 
    : 1;

// Chart configurations
const ordersChartData = computed(() => ({
    labels: props.masters?.slice(0, 8).map(m => m.name?.length > 10 ? m.name.substring(0, 10) + '...' : m.name) || [],
    datasets: [{
        label: 'Buyurtmalar',
        data: props.masters?.slice(0, 8).map(m => m.total_orders) || [],
        backgroundColor: 'rgba(59, 130, 246, 0.8)',
        borderRadius: 6,
    }]
}));

const revenueChartData = computed(() => ({
    labels: props.masters?.slice(0, 8).map(m => m.name?.length > 10 ? m.name.substring(0, 10) + '...' : m.name) || [],
    datasets: [{
        label: 'Daromad',
        data: props.masters?.slice(0, 8).map(m => m.revenue / 1000) || [], // in thousands
        backgroundColor: 'rgba(16, 185, 129, 0.8)',
        borderRadius: 6,
    }]
}));

const horizontalBarOptions = {
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: { stepSize: 1 }
        },
        y: {
            grid: { display: false },
        }
    }
};

const revenueBarOptions = {
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: (ctx) => formatMoney(ctx.raw * 1000) + ' so\'m'
            }
        }
    },
    scales: {
        x: {
            grid: { display: false },
        },
        y: {
            grid: { display: false },
        }
    }
};
</script>

<template>
    <div>
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <div class="flex items-center gap-3">
                    <Link href="/admin/reports" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </Link>
                    <h1 class="text-2xl font-bold text-gray-800">Masterlar hisoboti</h1>
                </div>
                <p class="text-gray-500 text-sm mt-1">Masterlar ishlash statistikasi</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
            <div class="flex items-end gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Boshlanish</label>
                    <input type="date" v-model="localFilters.date_from" class="px-3 py-2 border rounded-lg text-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tugash</label>
                    <input type="date" v-model="localFilters.date_to" class="px-3 py-2 border rounded-lg text-sm" />
                </div>
                <button @click="applyFilters" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition text-sm">
                    Qo'llash
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-2xl font-bold text-gray-800">{{ summary?.total_masters || 0 }}</p>
                <p class="text-sm text-gray-500">Faol masterlar</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-2xl font-bold text-blue-600">{{ summary?.total_orders || 0 }}</p>
                <p class="text-sm text-gray-500">Jami buyurtmalar</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-2xl font-bold text-green-600">{{ formatMoney(summary?.total_revenue) }}</p>
                <p class="text-sm text-gray-500">Jami daromad</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-2xl font-bold text-purple-600">{{ summary?.avg_orders || 0 }}</p>
                <p class="text-sm text-gray-500">O'rtacha buyurtma/master</p>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Orders Chart -->
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Buyurtmalar soni bo'yicha
                </h3>
                <div v-if="masters?.length" class="h-[250px]">
                    <Bar :data="ordersChartData" :options="horizontalBarOptions" />
                </div>
                <div v-else class="text-gray-500 text-center py-12">Ma'lumot yo'q</div>
            </div>

            <!-- Revenue Chart -->
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Daromad bo'yicha (ming so'm)
                </h3>
                <div v-if="masters?.length" class="h-[250px]">
                    <Bar :data="revenueChartData" :options="revenueBarOptions" />
                </div>
                <div v-else class="text-gray-500 text-center py-12">Ma'lumot yo'q</div>
            </div>
        </div>

        <!-- Masters Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b">
                <h2 class="font-semibold text-gray-800">Masterlar reytingi</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">#</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Master</th>
                            <th class="px-4 py-3 text-center font-medium text-gray-600">Buyurtmalar</th>
                            <th class="px-4 py-3 text-center font-medium text-gray-600">Tugallangan</th>
                            <th class="px-4 py-3 text-center font-medium text-gray-600">Bekor</th>
                            <th class="px-4 py-3 text-center font-medium text-gray-600">Completion %</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Daromad</th>
                            <th class="px-4 py-3 text-center font-medium text-gray-600">Reyting</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Daromad %</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="(master, i) in masters" :key="master.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                                    :class="i === 0 ? 'bg-yellow-100 text-yellow-700' : i === 1 ? 'bg-gray-200 text-gray-700' : i === 2 ? 'bg-orange-100 text-orange-700' : 'text-gray-500'">
                                    {{ i + 1 }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img v-if="master.photo" :src="master.photo" class="w-10 h-10 rounded-full object-cover" />
                                    <div v-else class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-medium">
                                        {{ master.name?.charAt(0) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-800">{{ master.name }}</div>
                                        <div class="text-xs text-gray-500">{{ master.phone }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center font-semibold text-gray-800">{{ master.total_orders }}</td>
                            <td class="px-4 py-3 text-center text-green-600">{{ master.completed_orders }}</td>
                            <td class="px-4 py-3 text-center text-red-600">{{ master.cancelled_orders }}</td>
                            <td class="px-4 py-3 text-center">
                                <span :class="master.completion_rate >= 80 ? 'text-green-600' : master.completion_rate >= 50 ? 'text-yellow-600' : 'text-red-600'" class="font-medium">
                                    {{ master.completion_rate }}%
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-green-600">{{ formatMoney(master.revenue) }}</td>
                            <td class="px-4 py-3 text-center">
                                <div v-if="master.rating" class="flex items-center justify-center gap-1">
                                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                    <span class="font-medium">{{ master.rating }}</span>
                                </div>
                                <span v-else class="text-gray-400">-</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="w-24 h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-green-500 rounded-full" :style="{ width: (master.revenue / maxRevenue * 100) + '%' }"></div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="!masters?.length" class="p-8 text-center text-gray-500">
                Masterlar topilmadi
            </div>
        </div>
    </div>
</template>
