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
import { Label } from '@/components/ui/label';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Progress } from '@/components/ui/progress';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps<{
    filters: {
        date_from?: string;
        date_to?: string;
    };
    masters: Array<{
        id: number;
        name: string;
        phone: string;
        photo: string | null;
        total_orders: number;
        completed_orders: number;
        cancelled_orders: number;
        completion_rate: number;
        revenue: number;
        rating: number | null;
    }>;
    summary: {
        total_masters: number;
        total_orders: number;
        total_revenue: number;
        avg_orders: number;
    };
}>();

const localFilters = ref({
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
});

const applyFilters = () => {
    router.get('/admin/reports/masters', localFilters.value, { preserveState: true });
};

const formatMoney = (amount: number | undefined) => {
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
        data: props.masters?.slice(0, 8).map(m => m.revenue / 1000) || [],
        backgroundColor: 'rgba(16, 185, 129, 0.8)',
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

const revenueBarOptions = {
    indexAxis: 'y' as const,
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: (ctx: any) => formatMoney(ctx.raw * 1000) + ' so\'m'
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

const getRankClass = (index: number) => {
    if (index === 0) return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300';
    if (index === 1) return 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300';
    if (index === 2) return 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300';
    return 'text-muted-foreground';
};

const getCompletionClass = (rate: number) => {
    if (rate >= 80) return 'text-green-600';
    if (rate >= 50) return 'text-yellow-600';
    return 'text-destructive';
};
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Button variant="ghost" size="icon" as-child>
                <Link href="/admin/reports">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </Link>
            </Button>
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ t('reports.masters_report', 'Masterlar hisoboti') }}</h1>
                <p class="text-muted-foreground">{{ t('reports.masters_stats', 'Masterlar ishlash statistikasi') }}</p>
            </div>
        </div>

        <!-- Filters -->
        <Card>
            <CardContent class="pt-6">
                <div class="flex items-end gap-4">
                    <div class="space-y-2">
                        <Label>{{ t('reports.date_from', 'Boshlanish') }}</Label>
                        <Input type="date" v-model="localFilters.date_from" class="w-auto" />
                    </div>
                    <div class="space-y-2">
                        <Label>{{ t('reports.date_to', 'Tugash') }}</Label>
                        <Input type="date" v-model="localFilters.date_to" class="w-auto" />
                    </div>
                    <Button @click="applyFilters">{{ t('common.apply', 'Qo\'llash') }}</Button>
                </div>
            </CardContent>
        </Card>

        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <Card>
                <CardContent class="pt-6">
                    <p class="text-2xl font-bold">{{ summary?.total_masters || 0 }}</p>
                    <p class="text-sm text-muted-foreground">{{ t('reports.active_masters', 'Faol masterlar') }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="pt-6">
                    <p class="text-2xl font-bold text-blue-600">{{ summary?.total_orders || 0 }}</p>
                    <p class="text-sm text-muted-foreground">{{ t('reports.total_orders', 'Jami buyurtmalar') }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="pt-6">
                    <p class="text-2xl font-bold text-green-600">{{ formatMoney(summary?.total_revenue) }}</p>
                    <p class="text-sm text-muted-foreground">{{ t('reports.total_revenue', 'Jami daromad') }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardContent class="pt-6">
                    <p class="text-2xl font-bold text-purple-600">{{ summary?.avg_orders || 0 }}</p>
                    <p class="text-sm text-muted-foreground">{{ t('reports.avg_per_master', 'O\'rtacha buyurtma/master') }}</p>
                </CardContent>
            </Card>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center text-base">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        {{ t('reports.by_orders_count', 'Buyurtmalar soni bo\'yicha') }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="masters?.length" class="h-[250px]">
                        <Bar :data="ordersChartData" :options="horizontalBarOptions" />
                    </div>
                    <div v-else class="text-muted-foreground text-center py-12">{{ t('common.no_data', 'Ma\'lumot yo\'q') }}</div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center text-base">
                        <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ t('reports.by_revenue', 'Daromad bo\'yicha') }} ({{ t('reports.thousands', 'ming so\'m') }})
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="masters?.length" class="h-[250px]">
                        <Bar :data="revenueChartData" :options="revenueBarOptions" />
                    </div>
                    <div v-else class="text-muted-foreground text-center py-12">{{ t('common.no_data', 'Ma\'lumot yo\'q') }}</div>
                </CardContent>
            </Card>
        </div>

        <!-- Masters Table -->
        <Card>
            <CardHeader>
                <CardTitle>{{ t('reports.masters_ranking', 'Masterlar reytingi') }}</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-12">#</TableHead>
                                <TableHead>{{ t('reports.master', 'Master') }}</TableHead>
                                <TableHead class="text-center">{{ t('reports.orders', 'Buyurtmalar') }}</TableHead>
                                <TableHead class="text-center">{{ t('reports.completed', 'Tugallangan') }}</TableHead>
                                <TableHead class="text-center">{{ t('reports.cancelled', 'Bekor') }}</TableHead>
                                <TableHead class="text-center">Completion %</TableHead>
                                <TableHead class="text-right">{{ t('reports.revenue', 'Daromad') }}</TableHead>
                                <TableHead class="text-center">{{ t('reports.rating', 'Reyting') }}</TableHead>
                                <TableHead class="w-32">{{ t('reports.revenue_bar', 'Daromad %') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="(master, i) in masters" :key="master.id">
                                <TableCell>
                                    <span 
                                        class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                                        :class="getRankClass(i)"
                                    >
                                        {{ i + 1 }}
                                    </span>
                                </TableCell>
                                <TableCell>
                                    <div class="flex items-center gap-3">
                                        <Avatar class="h-10 w-10">
                                            <AvatarImage v-if="master.photo" :src="master.photo" />
                                            <AvatarFallback>{{ master.name?.charAt(0) }}</AvatarFallback>
                                        </Avatar>
                                        <div>
                                            <div class="font-medium">{{ master.name }}</div>
                                            <div class="text-xs text-muted-foreground">{{ master.phone }}</div>
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell class="text-center font-semibold">{{ master.total_orders }}</TableCell>
                                <TableCell class="text-center text-green-600">{{ master.completed_orders }}</TableCell>
                                <TableCell class="text-center text-destructive">{{ master.cancelled_orders }}</TableCell>
                                <TableCell class="text-center">
                                    <span :class="getCompletionClass(master.completion_rate)" class="font-medium">
                                        {{ master.completion_rate }}%
                                    </span>
                                </TableCell>
                                <TableCell class="text-right font-semibold text-green-600">{{ formatMoney(master.revenue) }}</TableCell>
                                <TableCell class="text-center">
                                    <div v-if="master.rating" class="flex items-center justify-center gap-1">
                                        <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                        </svg>
                                        <span class="font-medium">{{ master.rating }}</span>
                                    </div>
                                    <span v-else class="text-muted-foreground">-</span>
                                </TableCell>
                                <TableCell>
                                    <Progress :model-value="(master.revenue / maxRevenue) * 100" class="h-2" />
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="!masters?.length">
                                <TableCell :colspan="9" class="text-center text-muted-foreground py-8">
                                    {{ t('reports.no_masters', 'Masterlar topilmadi') }}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
