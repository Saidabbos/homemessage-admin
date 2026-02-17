<script setup>
import { ref, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    filters: Object,
    summary: Object,
    byStatus: Object,
    byServiceType: Array,
    byMaster: Array,
    byDay: Array,
    orders: Array,
    masters: Array,
    serviceTypes: Array,
    statuses: Array,
    paymentStatuses: Array,
});

const localFilters = ref({
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
    master_id: props.filters?.master_id || '',
    service_type_id: props.filters?.service_type_id || '',
    status: props.filters?.status || '',
    payment_status: props.filters?.payment_status || '',
});

const applyFilters = () => {
    router.get('/admin/reports', localFilters.value, { preserveState: true });
};

const resetFilters = () => {
    localFilters.value = {
        date_from: '',
        date_to: '',
        master_id: '',
        service_type_id: '',
        status: '',
        payment_status: '',
    };
    router.get('/admin/reports');
};

const exportCsv = () => {
    const params = new URLSearchParams(localFilters.value);
    window.location.href = '/admin/reports/export?' + params.toString();
};

const formatMoney = (amount) => {
    return new Intl.NumberFormat('uz-UZ').format(amount || 0);
};

const getStatusColor = (status) => {
    const colors = {
        'NEW': 'bg-blue-100 text-blue-800',
        'CONFIRMING': 'bg-yellow-100 text-yellow-800',
        'CONFIRMED': 'bg-green-100 text-green-800',
        'WAITING_PAYMENT': 'bg-orange-100 text-orange-800',
        'PAID': 'bg-emerald-100 text-emerald-800',
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
        'WAITING_PAYMENT': 'To\'lov kutilmoqda',
        'PAID': 'To\'langan',
        'IN_PROGRESS': 'Jarayonda',
        'COMPLETED': 'Tugallangan',
        'CANCELLED': 'Bekor qilingan',
    };
    return labels[status] || status;
};

const getPaymentColor = (status) => {
    return status === 'PAID' ? 'text-green-600' : status === 'PENDING' ? 'text-yellow-600' : 'text-red-500';
};

const showOrders = ref(false);
</script>

<template>
    <div>
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Hisobotlar</h1>
                <p class="text-gray-500 text-sm">Buyurtmalar va daromad statistikasi</p>
            </div>
            <div class="flex gap-2">
                <Link href="/admin/reports/masters" class="px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Masterlar hisoboti
                </Link>
                <button @click="exportCsv" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    CSV Export
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Boshlanish</label>
                    <input type="date" v-model="localFilters.date_from" class="w-full px-3 py-2 border rounded-lg text-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tugash</label>
                    <input type="date" v-model="localFilters.date_to" class="w-full px-3 py-2 border rounded-lg text-sm" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Master</label>
                    <select v-model="localFilters.master_id" class="w-full px-3 py-2 border rounded-lg text-sm">
                        <option value="">Barchasi</option>
                        <option v-for="m in masters" :key="m.id" :value="m.id">{{ m.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Xizmat</label>
                    <select v-model="localFilters.service_type_id" class="w-full px-3 py-2 border rounded-lg text-sm">
                        <option value="">Barchasi</option>
                        <option v-for="s in serviceTypes" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select v-model="localFilters.status" class="w-full px-3 py-2 border rounded-lg text-sm">
                        <option value="">Barchasi</option>
                        <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">To'lov</label>
                    <select v-model="localFilters.payment_status" class="w-full px-3 py-2 border rounded-lg text-sm">
                        <option value="">Barchasi</option>
                        <option v-for="s in paymentStatuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-2 mt-4">
                <button @click="applyFilters" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition text-sm">
                    Qo'llash
                </button>
                <button @click="resetFilters" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm">
                    Tozalash
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-2xl font-bold text-gray-800">{{ summary?.total_orders || 0 }}</p>
                <p class="text-sm text-gray-500">Jami buyurtmalar</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-2xl font-bold text-green-600">{{ summary?.completed_orders || 0 }}</p>
                <p class="text-sm text-gray-500">Tugallangan</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-2xl font-bold text-red-600">{{ summary?.cancelled_orders || 0 }}</p>
                <p class="text-sm text-gray-500">Bekor qilingan</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-2xl font-bold text-emerald-600">{{ formatMoney(summary?.total_revenue) }}</p>
                <p class="text-sm text-gray-500">Jami daromad</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-2xl font-bold text-blue-600">{{ summary?.paid_orders || 0 }}</p>
                <p class="text-sm text-gray-500">To'langan</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-2xl font-bold text-orange-600">{{ summary?.unpaid_orders || 0 }}</p>
                <p class="text-sm text-gray-500">To'lanmagan</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm p-4">
                <p class="text-2xl font-bold text-purple-600">{{ formatMoney(summary?.avg_order_value) }}</p>
                <p class="text-sm text-gray-500">O'rtacha summa</p>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- By Service Type -->
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-800 mb-4">Xizmat turlari bo'yicha</h3>
                <div v-if="byServiceType?.length" class="space-y-3">
                    <div v-for="item in byServiceType" :key="item.name" class="flex items-center justify-between">
                        <span class="text-gray-700 truncate flex-1">{{ item.name }}</span>
                        <div class="text-right ml-2">
                            <span class="font-semibold text-gray-800">{{ item.count }}</span>
                            <span class="text-xs text-gray-500 ml-2">{{ formatMoney(item.revenue) }}</span>
                        </div>
                    </div>
                </div>
                <div v-else class="text-gray-500 text-center py-4">Ma'lumot yo'q</div>
            </div>

            <!-- By Master -->
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-800 mb-4">Masterlar bo'yicha</h3>
                <div v-if="byMaster?.length" class="space-y-3">
                    <div v-for="item in byMaster.slice(0, 5)" :key="item.name" class="flex items-center justify-between">
                        <span class="text-gray-700 truncate flex-1">{{ item.name }}</span>
                        <div class="text-right ml-2">
                            <span class="font-semibold text-gray-800">{{ item.count }}</span>
                            <span class="text-xs text-green-600 ml-2">{{ formatMoney(item.revenue) }}</span>
                        </div>
                    </div>
                </div>
                <div v-else class="text-gray-500 text-center py-4">Ma'lumot yo'q</div>
            </div>

            <!-- By Day Chart -->
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h3 class="font-semibold text-gray-800 mb-4">Kunlar bo'yicha</h3>
                <div v-if="byDay?.length" class="space-y-2 max-h-48 overflow-y-auto">
                    <div v-for="item in byDay" :key="item.date" class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">{{ item.date }}</span>
                        <div>
                            <span class="font-medium text-gray-800">{{ item.count }} ta</span>
                            <span class="text-green-600 ml-2">{{ formatMoney(item.revenue) }}</span>
                        </div>
                    </div>
                </div>
                <div v-else class="text-gray-500 text-center py-4">Ma'lumot yo'q</div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="bg-white rounded-xl shadow-sm">
            <div class="px-5 py-4 border-b flex items-center justify-between">
                <h2 class="font-semibold text-gray-800">
                    Buyurtmalar ro'yxati ({{ orders?.length || 0 }})
                </h2>
                <button @click="showOrders = !showOrders" class="text-sm text-blue-600 hover:text-blue-800">
                    {{ showOrders ? 'Yashirish' : 'Ko\'rsatish' }}
                </button>
            </div>

            <div v-if="showOrders" class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">№</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Sana</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Mijoz</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Master</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Xizmat</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Summa</th>
                            <th class="px-4 py-3 text-center font-medium text-gray-600">Status</th>
                            <th class="px-4 py-3 text-center font-medium text-gray-600">To'lov</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <Link :href="`/admin/orders/${order.id}`" class="text-blue-600 hover:underline font-mono text-xs">
                                    {{ order.order_number }}
                                </Link>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ order.created_at }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800">{{ order.customer_name }}</div>
                                <div class="text-xs text-gray-500">{{ order.customer_phone }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ order.master_name || '-' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ order.service_name }}</td>
                            <td class="px-4 py-3 text-right font-semibold">{{ formatMoney(order.total_amount) }}</td>
                            <td class="px-4 py-3 text-center">
                                <span :class="['text-xs px-2 py-1 rounded-full', getStatusColor(order.status)]">
                                    {{ getStatusLabel(order.status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span :class="['text-xs font-medium', getPaymentColor(order.payment_status)]">
                                    {{ order.payment_status === 'PAID' ? '✓' : order.payment_status === 'PENDING' ? '⏳' : '✗' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="showOrders && !orders?.length" class="p-8 text-center text-gray-500">
                Buyurtmalar topilmadi
            </div>
        </div>
    </div>
</template>
