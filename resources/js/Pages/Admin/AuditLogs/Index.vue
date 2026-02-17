<script setup>
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
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
    logs: Object,
    filters: Object,
    actions: Array,
    types: Array,
    users: Array,
});

const localFilters = ref({
    search: props.filters?.search || '',
    action: props.filters?.action || '',
    user_id: props.filters?.user_id || '',
    auditable_type: props.filters?.auditable_type || '',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
});

const applyFilters = () => {
    router.get('/admin/audit-logs', localFilters.value, { preserveState: true });
};

const resetFilters = () => {
    localFilters.value = { search: '', action: '', user_id: '', auditable_type: '', date_from: '', date_to: '' };
    router.get('/admin/audit-logs');
};

const getActionColor = (action) => {
    const colors = {
        'created': 'bg-green-500/10 text-green-700',
        'updated': 'bg-blue-500/10 text-blue-700',
        'deleted': 'bg-red-500/10 text-red-700',
        'status_changed': 'bg-yellow-500/10 text-yellow-700',
        'slot_changed': 'bg-purple-500/10 text-purple-700',
        'payment_received': 'bg-emerald-500/10 text-emerald-700',
        'note_added': 'bg-gray-500/10 text-gray-700',
        'assigned': 'bg-indigo-500/10 text-indigo-700',
        'login': 'bg-cyan-500/10 text-cyan-700',
        'logout': 'bg-orange-500/10 text-orange-700',
    };
    return colors[action] || 'bg-gray-500/10 text-gray-700';
};

const getActionLabel = (action) => {
    const labels = {
        'created': t('auditLogs.actions.created') || 'Yaratildi',
        'updated': t('auditLogs.actions.updated') || 'Yangilandi',
        'deleted': t('auditLogs.actions.deleted') || "O'chirildi",
        'status_changed': t('auditLogs.actions.statusChanged') || "Holat o'zgardi",
        'slot_changed': t('auditLogs.actions.slotChanged') || "Vaqt o'zgardi",
        'payment_received': t('auditLogs.actions.paymentReceived') || "To'lov qabul qilindi",
        'note_added': t('auditLogs.actions.noteAdded') || "Izoh qo'shildi",
        'assigned': t('auditLogs.actions.assigned') || 'Tayinlandi',
        'login': t('auditLogs.actions.login') || 'Kirdi',
        'logout': t('auditLogs.actions.logout') || 'Chiqdi',
    };
    return labels[action] || action;
};

const getModelName = (type) => {
    if (!type) return '-';
    return type.split('\\').pop();
};

const formatDate = (date) => {
    return new Date(date).toLocaleString('uz-UZ', {
        year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit'
    });
};

const showChanges = ref(null);
const toggleChanges = (id) => { showChanges.value = showChanges.value === id ? null : id; };

const formatJson = (data) => {
    if (!data) return '-';
    try { return JSON.stringify(JSON.parse(typeof data === 'string' ? data : JSON.stringify(data)), null, 2); }
    catch { return String(data); }
};

const hasActiveFilters = () => Object.values(localFilters.value).some(v => v);
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{{ t('auditLogs.title') || 'Audit Logs' }}</h1>
                <p class="text-muted-foreground">{{ t('auditLogs.subtitle') || 'Tizim faoliyati tarixi' }}</p>
            </div>
        </div>

        <Card>
            <CardHeader class="pb-4">
                <div class="flex flex-col lg:flex-row lg:items-center gap-4 flex-wrap">
                    <Input v-model="localFilters.search" placeholder="Qidirish..." class="lg:w-48" @keyup.enter="applyFilters" />
                    <Select v-model="localFilters.action">
                        <SelectTrigger class="lg:w-40"><SelectValue placeholder="Harakat" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="">Barchasi</SelectItem>
                            <SelectItem v-for="action in actions" :key="action" :value="action">{{ getActionLabel(action) }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="localFilters.auditable_type">
                        <SelectTrigger class="lg:w-40"><SelectValue placeholder="Model" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="">Barchasi</SelectItem>
                            <SelectItem v-for="type in types" :key="type" :value="type">{{ getModelName(type) }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="localFilters.user_id">
                        <SelectTrigger class="lg:w-40"><SelectValue placeholder="Foydalanuvchi" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="">Barchasi</SelectItem>
                            <SelectItem v-for="user in users" :key="user.id" :value="String(user.id)">{{ user.name }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <Input v-model="localFilters.date_from" type="date" class="lg:w-40" />
                    <Input v-model="localFilters.date_to" type="date" class="lg:w-40" />
                    <Button @click="applyFilters">Qidirish</Button>
                    <Button v-if="hasActiveFilters()" variant="ghost" @click="resetFilters">{{ t('common.reset') }}</Button>
                </div>
            </CardHeader>

            <CardContent class="p-0">
                <Table v-if="logs.data.length > 0">
                    <TableHeader>
                        <TableRow>
                            <TableHead>Sana</TableHead>
                            <TableHead>Foydalanuvchi</TableHead>
                            <TableHead>Harakat</TableHead>
                            <TableHead>Model</TableHead>
                            <TableHead>Tavsif</TableHead>
                            <TableHead class="text-center">{{ t('common.actions') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-for="log in logs.data" :key="log.id">
                            <TableRow>
                                <TableCell class="text-sm text-muted-foreground whitespace-nowrap">{{ formatDate(log.created_at) }}</TableCell>
                                <TableCell>
                                    <div class="font-medium">{{ log.user?.name || 'Tizim' }}</div>
                                    <div v-if="log.ip_address" class="text-xs text-muted-foreground">{{ log.ip_address }}</div>
                                </TableCell>
                                <TableCell><Badge :class="getActionColor(log.action)">{{ getActionLabel(log.action) }}</Badge></TableCell>
                                <TableCell>
                                    <span class="font-mono text-sm">{{ getModelName(log.auditable_type) }}</span>
                                    <span v-if="log.auditable_id" class="text-muted-foreground text-xs ml-1">#{{ log.auditable_id }}</span>
                                </TableCell>
                                <TableCell class="max-w-xs truncate text-muted-foreground">{{ log.description || '-' }}</TableCell>
                                <TableCell class="text-center">
                                    <Button v-if="log.old_values || log.new_values" variant="ghost" size="sm" @click="toggleChanges(log.id)">
                                        {{ showChanges === log.id ? 'Yopish' : "O'zgarishlar" }}
                                    </Button>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="showChanges === log.id" class="bg-muted/30">
                                <TableCell colspan="6" class="p-4">
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div v-if="log.old_values">
                                            <h4 class="font-semibold mb-2 text-red-600">Eski qiymatlar</h4>
                                            <pre class="bg-red-50 dark:bg-red-950 p-3 rounded text-xs overflow-auto max-h-48">{{ formatJson(log.old_values) }}</pre>
                                        </div>
                                        <div v-if="log.new_values">
                                            <h4 class="font-semibold mb-2 text-green-600">Yangi qiymatlar</h4>
                                            <pre class="bg-green-50 dark:bg-green-950 p-3 rounded text-xs overflow-auto max-h-48">{{ formatJson(log.new_values) }}</pre>
                                        </div>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </template>
                    </TableBody>
                </Table>
                <div v-else class="flex flex-col items-center justify-center py-12">
                    <div class="w-16 h-16 bg-muted rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">{{ t('common.noData') }}</h3>
                    <p class="text-muted-foreground">Loglar topilmadi</p>
                </div>
            </CardContent>

            <div v-if="logs.data.length > 0" class="flex items-center justify-between px-6 py-4 border-t">
                <p class="text-sm text-muted-foreground">{{ t('common.total') }}: <strong>{{ logs.total }}</strong></p>
                <Pagination :links="logs.links" />
            </div>
        </Card>
    </div>
</template>
