<script setup>
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/UI/Pagination.vue';

import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps({
    ratings: Object,
    stats: Object,
    masters: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const type = ref(props.filters?.type || 'all');
const status = ref(props.filters?.status || 'all');
const masterId = ref(props.filters?.master_id || '');
const rating = ref(props.filters?.rating || 'all');

const applyFilters = () => {
    router.get(route('admin.ratings.index'), {
        search: search.value || undefined,
        type: type.value === 'all' ? undefined : type.value,
        status: status.value === 'all' ? undefined : status.value,
        master_id: masterId.value || undefined,
        rating: rating.value === 'all' ? undefined : rating.value,
    }, { preserveState: true, replace: true });
};

const resetFilters = () => {
    search.value = '';
    type.value = 'all';
    status.value = 'all';
    masterId.value = '';
    rating.value = 'all';
    router.get(route('admin.ratings.index'));
};

let searchTimeout;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
});
watch([type, status, masterId, rating], applyFilters);

const formatRating = (val) => val ? parseFloat(val).toFixed(1) : '-';

const deleteRating = (id) => {
    if (confirm(t('ratings.confirmDelete') || 'O\'chirmoqchimisiz?')) {
        router.delete(route('admin.ratings.destroy', id));
    }
};

const hasActiveFilters = () => search.value || (type.value && type.value !== 'all') || (status.value && status.value !== 'all') || masterId.value || (rating.value && rating.value !== 'all');
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ t('ratings.title') || 'Baholar' }}</h1>
                <p class="text-muted-foreground">{{ t('ratings.subtitle') || 'Barcha baholarni ko\'rish va boshqarish' }}</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <Card class="border-l-4 border-l-blue-500">
                <CardContent class="p-4">
                    <div class="text-2xl font-bold text-blue-600">{{ stats?.total || 0 }}</div>
                    <p class="text-sm text-muted-foreground">Jami</p>
                </CardContent>
            </Card>
            <Card class="border-l-4 border-l-green-500">
                <CardContent class="p-4">
                    <div class="text-2xl font-bold text-green-600">{{ stats?.rated || 0 }}</div>
                    <p class="text-sm text-muted-foreground">Baholangan</p>
                </CardContent>
            </Card>
            <Card class="border-l-4 border-l-yellow-500">
                <CardContent class="p-4">
                    <div class="text-2xl font-bold text-yellow-600">{{ stats?.pending || 0 }}</div>
                    <p class="text-sm text-muted-foreground">Kutilmoqda</p>
                </CardContent>
            </Card>
            <Card class="border-l-4 border-l-purple-500">
                <CardContent class="p-4">
                    <div class="text-2xl font-bold text-purple-600">{{ formatRating(stats?.avg_master) }}</div>
                    <p class="text-sm text-muted-foreground">Master o'rtacha</p>
                </CardContent>
            </Card>
            <Card class="border-l-4 border-l-cyan-500">
                <CardContent class="p-4">
                    <div class="text-2xl font-bold text-cyan-600">{{ formatRating(stats?.avg_client) }}</div>
                    <p class="text-sm text-muted-foreground">Mijoz o'rtacha</p>
                </CardContent>
            </Card>
        </div>

        <!-- Main Card -->
        <Card>
            <CardHeader class="pb-4">
                <div class="flex flex-col lg:flex-row lg:items-center gap-4 flex-wrap">
                    <Input v-model="search" placeholder="Qidirish..." class="lg:w-48" />
                    <Select v-model="type">
                        <SelectTrigger class="lg:w-40">
                            <SelectValue placeholder="Turi" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Barchasi</SelectItem>
                            <SelectItem value="client_to_master">Mijoz → Master</SelectItem>
                            <SelectItem value="master_to_client">Master → Mijoz</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="status">
                        <SelectTrigger class="lg:w-40">
                            <SelectValue placeholder="Holat" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Barchasi</SelectItem>
                            <SelectItem value="rated">Baholangan</SelectItem>
                            <SelectItem value="pending">Kutilmoqda</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="rating">
                        <SelectTrigger class="lg:w-32">
                            <SelectValue placeholder="Baho" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Barchasi</SelectItem>
                            <SelectItem value="5">⭐ 5</SelectItem>
                            <SelectItem value="4">⭐ 4</SelectItem>
                            <SelectItem value="3">⭐ 3</SelectItem>
                            <SelectItem value="2">⭐ 2</SelectItem>
                            <SelectItem value="1">⭐ 1</SelectItem>
                        </SelectContent>
                    </Select>
                    <Button v-if="hasActiveFilters()" variant="ghost" @click="resetFilters">{{ t('common.reset') }}</Button>
                </div>
            </CardHeader>

            <CardContent class="p-0">
                <Table v-if="ratings.data.length > 0">
                    <TableHeader>
                        <TableRow>
                            <TableHead>Buyurtma</TableHead>
                            <TableHead>Turi</TableHead>
                            <TableHead>Baholagan</TableHead>
                            <TableHead>Baholangan</TableHead>
                            <TableHead class="text-center">Baho</TableHead>
                            <TableHead>Izoh</TableHead>
                            <TableHead>Sana</TableHead>
                            <TableHead class="text-center">{{ t('common.actions') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="item in ratings.data" :key="item.id">
                            <TableCell>
                                <Link :href="`/admin/orders/${item.order_id}`" class="text-primary hover:underline font-mono text-sm">
                                    {{ item.order?.order_number || '#' + item.order_id }}
                                </Link>
                            </TableCell>
                            <TableCell>
                                <Badge v-if="item.type === 'client_to_master'" class="bg-purple-500/10 text-purple-700">
                                    Mijoz → Master
                                </Badge>
                                <Badge v-else class="bg-cyan-500/10 text-cyan-700">
                                    Master → Mijoz
                                </Badge>
                            </TableCell>
                            <TableCell class="font-medium">
                                {{ item.type === 'client_to_master' ? item.order?.customer?.name : item.order?.master?.full_name }}
                            </TableCell>
                            <TableCell class="text-muted-foreground">
                                {{ item.type === 'client_to_master' ? item.order?.master?.full_name : item.order?.customer?.name }}
                            </TableCell>
                            <TableCell class="text-center">
                                <div v-if="item.overall_rating" class="flex items-center justify-center gap-1">
                                    <span class="text-yellow-500">⭐</span>
                                    <span class="font-bold">{{ item.overall_rating }}</span>
                                </div>
                                <Badge v-else variant="outline" class="text-yellow-600">Kutilmoqda</Badge>
                            </TableCell>
                            <TableCell class="max-w-xs truncate text-muted-foreground">
                                {{ item.feedback || '-' }}
                            </TableCell>
                            <TableCell class="text-sm text-muted-foreground">{{ item.rated_at || '-' }}</TableCell>
                            <TableCell>
                                <div class="flex items-center justify-center gap-1">
                                    <Button variant="ghost" size="icon" as-child>
                                        <Link :href="`/admin/orders/${item.order_id}`">
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </Link>
                                    </Button>
                                    <Button variant="ghost" size="icon" @click="deleteRating(item.id)">
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
                <div v-else class="flex flex-col items-center justify-center py-12">
                    <div class="w-16 h-16 bg-muted rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-muted-foreground" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">{{ t('common.noData') }}</h3>
                    <p class="text-muted-foreground">Baholar topilmadi</p>
                </div>
            </CardContent>

            <div v-if="ratings.data.length > 0" class="flex items-center justify-between px-6 py-4 border-t">
                <p class="text-sm text-muted-foreground">{{ t('common.total') }}: <strong>{{ ratings.total }}</strong></p>
                <Pagination :links="ratings.links" />
            </div>
        </Card>
    </div>
</template>
