<script setup lang="ts">
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
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

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Label } from '@/components/ui/label';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps<{
    filters: {
        date_from?: string;
        date_to?: string;
        master_id?: string;
        service_type_id?: string;
        status?: string;
        payment_status?: string;
    };
    summary: {
        total_orders: number;
        completed_orders: number;
        cancelled_orders: number;
        total_revenue: number;
        paid_orders: number;
        unpaid_orders: number;
        avg_order_value: number;
    };
    byStatus: Record<string, number>;
    byServiceType: Array<{ name: string; count: number }>;
    byMaster: Array<{ name: string; count: number }>;
    byDay: Array<{ date: string; count: number }>;
    orders: Array<{
        id: number;
        order_number: string;
        created_at: string;
        customer_name: string;
        customer_phone: string;
        master_name: string;
        service_name: string;
        total_amount: number;
        status: string;
        payment_status: string;
    }>;
    masters: Array<{ id: number; name: string }>;
    serviceTypes: Array<{ id: number; name: string }>;
    statuses: Array<{ value: string; label: string }>;
    paymentStatuses: Array<{ value: string; label: string }>;
}>();

const localFilters = ref({
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
    master_id: props.filters?.master_id || 'all',
    service_type_id: props.filters?.service_type_id || 'all',
    status: props.filters?.status || 'all',
    payment_status: props.filters?.payment_status || 'all',
});

const applyFilters = () => {
    const params = { ...localFilters.value };
    if (params.master_id === 'all') params.master_id = undefined;
    if (params.service_type_id === 'all') params.service_type_id = undefined;
    if (params.status === 'all') params.status = undefined;
    if (params.payment_status === 'all') params.payment_status = undefined;
    if (!params.date_from) params.date_from = undefined;
    if (!params.date_to) params.date_to = undefined;
    router.get('/admin/reports', params, { preserveState: true });
};

const resetFilters = () => {
    localFilters.value = {
        date_from: '',
        date_to: '',
        master_id: 'all',
        service_type_id: 'all',
        status: 'all',
        payment_status: 'all',
    };
    router.get('/admin/reports');
};

const exportCsv = () => {
    const params = new URLSearchParams(localFilters.value as any);
    window.location.href = '/admin/reports/export?' + params.toString();
};

const formatMoney = (amount: number | undefined) => {
    return new Intl.NumberFormat('uz-UZ').format(amount || 0);
};

const getStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
    const variants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
        'NEW': 'default',
        'CONFIRMING': 'secondary',
        'CONFIRMED': 'default',
        'WAITING_PAYMENT': 'secondary',
        'PAID': 'default',
        'IN_PROGRESS': 'secondary',
        'COMPLETED': 'outline',
        'CANCELLED': 'destructive',
    };
    return variants[status] || 'outline';
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
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

const showOrders = ref(false);

// Chart configurations
const servicesChartData = computed(() => ({
    labels: props.byServiceType?.map(s => s.name?.length > 15 ? s.name.substring(0, 15) + '...' : s.name) || [],
    datasets: [{
        data: props.byServiceType?.map(s => s.count) || [],
        backgroundColor: [
            'rgba(99, 102, 241, 0.8)',
            'rgba(139, 92, 246, 0.8)',
            'rgba(168, 85, 247, 0.8)',
            'rgba(217, 70, 239, 0.8)',
            'rgba(236, 72, 153, 0.8)',
            'rgba(244, 114, 182, 0.8)',
            'rgba(251, 146, 60, 0.8)',
            'rgba(234, 179, 8, 0.8)',
        ],
        borderRadius: 6,
    }]
}));

const mastersChartData = computed(() => ({
    labels: props.byMaster?.slice(0, 6).map(m => m.name?.length > 12 ? m.name.substring(0, 12) + '...' : m.name) || [],
    datasets: [{
        data: props.byMaster?.slice(0, 6).map(m => m.count) || [],
        backgroundColor: 'rgba(16, 185, 129, 0.8)',
        borderRadius: 6,
    }]
}));

const daysChartData = computed(() => ({
    labels: props.byDay?.slice(-14).map(d => {
        if (!d.date) return '-';
        if (typeof d.date === 'string' && d.date.includes('.')) {
            const parts = d.date.split('.');
            return parts.length >= 2 ? `${parts[0]}.${parts[1]}` : d.date;
        }
        const date = new Date(d.date);
        if (isNaN(date.getTime())) return d.date;
        return `${date.getDate()}.${String(date.getMonth() + 1).padStart(2, '0')}`;
    }) || [],
    datasets: [{
        label: 'Buyurtmalar',
        data: props.byDay?.slice(-14).map(d => d.count) || [],
        backgroundColor: 'rgba(59, 130, 246, 0.8)',
        borderRadius: 6,
    }]
}));

const horizontalBarOptions = {
    indexAxis: 'y' as const,
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

const verticalBarOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
    },
    scales: {
        x: { grid: { display: false } },
        y: { 
            grid: { color: 'rgba(0,0,0,0.05)' },
            ticks: { stepSize: 1 }
        }
    }
};
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ t('reports.title', 'Hisobotlar') }}</h1>
                <p class="text-muted-foreground">{{ t('reports.subtitle', 'Buyurtmalar va daromad statistikasi') }}</p>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" as-child>
                    <Link href="/admin/reports/masters" class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ t('reports.masters_report', 'Masterlar hisoboti') }}
                    </Link>
                </Button>
                <Button @click="exportCsv" class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    CSV Export
                </Button>
            </div>
        </div>

        <!-- Filters -->
        <Card>
            <CardContent class="pt-6">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div class="space-y-2">
                        <Label>{{ t('reports.date_from', 'Boshlanish') }}</Label>
                        <Input type="date" v-model="localFilters.date_from" />
                    </div>
                    <div class="space-y-2">
                        <Label>{{ t('reports.date_to', 'Tugash') }}</Label>
                        <Input type="date" v-model="localFilters.date_to" />
                    </div>
                    <div class="space-y-2">
                        <Label>{{ t('reports.master', 'Master') }}</Label>
                        <Select v-model="localFilters.master_id">
                            <SelectTrigger>
                                <SelectValue :placeholder="t('common.all', 'Barchasi')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ t('common.all', 'Barchasi') }}</SelectItem>
                                <SelectItem v-for="m in masters" :key="m.id" :value="String(m.id)">{{ m.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-2">
                        <Label>{{ t('reports.service', 'Xizmat') }}</Label>
                        <Select v-model="localFilters.service_type_id">
                            <SelectTrigger>
                                <SelectValue :placeholder="t('common.all', 'Barchasi')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ t('common.all', 'Barchasi') }}</SelectItem>
                                <SelectItem v-for="s in serviceTypes" :key="s.id" :value="String(s.id)">{{ s.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-2">
                        <Label>{{ t('reports.status', 'Status') }}</Label>
                        <Select v-model="localFilters.status">
                            <SelectTrigger>
                                <SelectValue :placeholder="t('common.all', 'Barchasi')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ t('common.all', 'Barchasi') }}</SelectItem>
                                <SelectItem v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-2">
                        <Label>{{ t('reports.payment', 'To\'lov') }}</Label>
                        <Select v-model="localFilters.payment_status">
                            <SelectTrigger>
                                <SelectValue :placeholder="t('common.all', 'Barchasi')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{{ t('common.all', 'Barchasi') }}</SelectItem>
                                <SelectItem v-for="s in paymentStatuses" :key="s.value" :value="s.value">{{ s.label }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
                <div class="flex gap-2 mt-4">
                    <Button @click="applyFilters">{{ t('common.apply', 'Qo\'llash') }}</Button>
                    <Button variant="outline" @click="resetFilters">{{ t('common.clear', 'Tozalash') }}</Button>
                </div>
            </CardContent>
        </Card>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
            <Card>
                <CardContent class="pt-6">
                    <p class="text-2xl font-bold">{{ summary?.total_orders || 0 }}</p>
                    <p class="text-sm text-muted-foreground">{{ t('reports.total_orders', 'Jami buyurtmalar') }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="pt-6">
                    <p class="text-2xl font-bold text-green-600">{{ summary?.completed_orders || 0 }}</p>
                    <p class="text-sm text-muted-foreground">{{ t('reports.completed', 'Tugallangan') }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="pt-6">
                    <p class="text-2xl font-bold text-destructive">{{ summary?.cancelled_orders || 0 }}</p>
                    <p class="text-sm text-muted-foreground">{{ t('reports.cancelled', 'Bekor qilingan') }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="pt-6">
                    <p class="text-2xl font-bold text-emerald-600">{{ formatMoney(summary?.total_revenue) }}</p>
                    <p class="text-sm text-muted-foreground">{{ t('reports.total_revenue', 'Jami daromad') }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="pt-6">
                    <p class="text-2xl font-bold text-blue-600">{{ summary?.paid_orders || 0 }}</p>
                    <p class="text-sm text-muted-foreground">{{ t('reports.paid', 'To\'langan') }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="pt-6">
                    <p class="text-2xl font-bold text-orange-600">{{ summary?.unpaid_orders || 0 }}</p>
                    <p class="text-sm text-muted-foreground">{{ t('reports.unpaid', 'To\'lanmagan') }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="pt-6">
                    <p class="text-2xl font-bold text-purple-600">{{ formatMoney(summary?.avg_order_value) }}</p>
                    <p class="text-sm text-muted-foreground">{{ t('reports.avg_value', 'O\'rtacha summa') }}</p>
                </CardContent>
            </Card>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center text-base">
                        <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        {{ t('reports.by_service', 'Xizmat turlari bo\'yicha') }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="byServiceType?.length" class="h-[220px]">
                        <Bar :data="servicesChartData" :options="horizontalBarOptions" />
                    </div>
                    <div v-else class="text-muted-foreground text-center py-12">{{ t('common.no_data', 'Ma\'lumot yo\'q') }}</div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center text-base">
                        <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ t('reports.by_master', 'Masterlar bo\'yicha') }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="byMaster?.length" class="h-[220px]">
                        <Bar :data="mastersChartData" :options="horizontalBarOptions" />
                    </div>
                    <div v-else class="text-muted-foreground text-center py-12">{{ t('common.no_data', 'Ma\'lumot yo\'q') }}</div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center text-base">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ t('reports.by_day', 'Kunlar bo\'yicha') }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="byDay?.length" class="h-[220px]">
                        <Bar :data="daysChartData" :options="verticalBarOptions" />
                    </div>
                    <div v-else class="text-muted-foreground text-center py-12">{{ t('common.no_data', 'Ma\'lumot yo\'q') }}</div>
                </CardContent>
            </Card>
        </div>

        <!-- Orders Table -->
        <Card>
            <Collapsible v-model:open="showOrders">
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle>{{ t('reports.orders_list', 'Buyurtmalar ro\'yxati') }} ({{ orders?.length || 0 }})</CardTitle>
                    <CollapsibleTrigger as-child>
                        <Button variant="ghost" size="sm">
                            {{ showOrders ? t('common.hide', 'Yashirish') : t('common.show', 'Ko\'rsatish') }}
                        </Button>
                    </CollapsibleTrigger>
                </CardHeader>
                <CollapsibleContent>
                    <CardContent>
                        <div class="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>№</TableHead>
                                        <TableHead>{{ t('reports.date', 'Sana') }}</TableHead>
                                        <TableHead>{{ t('reports.customer', 'Mijoz') }}</TableHead>
                                        <TableHead>{{ t('reports.master', 'Master') }}</TableHead>
                                        <TableHead>{{ t('reports.service', 'Xizmat') }}</TableHead>
                                        <TableHead class="text-right">{{ t('reports.amount', 'Summa') }}</TableHead>
                                        <TableHead class="text-center">{{ t('reports.status', 'Status') }}</TableHead>
                                        <TableHead class="text-center">{{ t('reports.payment', 'To\'lov') }}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="order in orders" :key="order.id">
                                        <TableCell>
                                            <Link :href="`/admin/orders/${order.id}`" class="text-primary hover:underline font-mono text-xs">
                                                {{ order.order_number }}
                                            </Link>
                                        </TableCell>
                                        <TableCell class="text-muted-foreground">{{ order.created_at }}</TableCell>
                                        <TableCell>
                                            <div class="font-medium">{{ order.customer_name }}</div>
                                            <div class="text-xs text-muted-foreground">{{ order.customer_phone }}</div>
                                        </TableCell>
                                        <TableCell>{{ order.master_name || '-' }}</TableCell>
                                        <TableCell>{{ order.service_name }}</TableCell>
                                        <TableCell class="text-right font-semibold">{{ formatMoney(order.total_amount) }}</TableCell>
                                        <TableCell class="text-center">
                                            <Badge :variant="getStatusVariant(order.status)">
                                                {{ getStatusLabel(order.status) }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell class="text-center">
                                            <span :class="order.payment_status === 'PAID' ? 'text-green-600' : order.payment_status === 'PENDING' ? 'text-yellow-600' : 'text-destructive'" class="text-xs font-medium">
                                                {{ order.payment_status === 'PAID' ? '✓' : order.payment_status === 'PENDING' ? '⏳' : '✗' }}
                                            </span>
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-if="!orders?.length">
                                        <TableCell :colspan="8" class="text-center text-muted-foreground py-8">
                                            {{ t('reports.no_orders', 'Buyurtmalar topilmadi') }}
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </CollapsibleContent>
            </Collapsible>
        </Card>
    </div>
</template>
