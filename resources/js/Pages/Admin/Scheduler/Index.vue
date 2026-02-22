<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
// shadcn-vue components
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps({
    runs: Object,
    stats: Object,
});

const runningCommand = ref(null);

const runCommand = (command) => {
    runningCommand.value = command;
    router.post(route('admin.scheduler.run'), { command }, {
        preserveScroll: true,
        onFinish: () => {
            runningCommand.value = null;
        },
    });
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString('uz-UZ', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
};

const getStatusBadge = (status) => {
    switch (status) {
        case 'success':
            return 'default';
        case 'failed':
            return 'destructive';
        case 'running':
            return 'secondary';
        default:
            return 'outline';
    }
};

const getStatusText = (status) => {
    switch (status) {
        case 'success':
            return 'Muvaffaqiyatli';
        case 'failed':
            return 'Xatolik';
        case 'running':
            return 'Ishlayapti';
        default:
            return status;
    }
};
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Scheduler Monitor</h1>
                <p class="text-sm text-gray-500 mt-1">Avtomatik vazifalar monitoringi</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium text-gray-500">Jami ishga tushirishlar</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ stats.total_runs }}</div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium text-gray-500">Muvaffaqiyatli</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-green-600">{{ stats.successful }}</div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium text-gray-500">Xatoliklar</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-red-600">{{ stats.failed }}</div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium text-gray-500">Bugun o'zgartirilgan</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold text-blue-600">{{ stats.records_today }}</div>
                </CardContent>
            </Card>
        </div>

        <!-- Manual Run -->
        <Card>
            <CardHeader>
                <CardTitle>Qo'lda ishga tushirish</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="flex flex-wrap gap-3">
                    <Button
                        @click="runCommand('orders:process-statuses')"
                        :disabled="runningCommand !== null"
                    >
                        <svg v-if="runningCommand === 'orders:process-statuses'" class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Buyurtmalar statusini yangilash
                    </Button>

                    <Button
                        variant="outline"
                        @click="runCommand('otp:cleanup')"
                        :disabled="runningCommand !== null"
                    >
                        <svg v-if="runningCommand === 'otp:cleanup'" class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        OTP tozalash
                    </Button>
                </div>
            </CardContent>
        </Card>

        <!-- Runs Table -->
        <Card>
            <CardHeader>
                <CardTitle>Ishga tushirishlar tarixi</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-3 px-4 font-medium">Buyruq</th>
                                <th class="text-left py-3 px-4 font-medium">Status</th>
                                <th class="text-left py-3 px-4 font-medium">O'zgartirilgan</th>
                                <th class="text-left py-3 px-4 font-medium">Vaqt</th>
                                <th class="text-left py-3 px-4 font-medium">Boshlangan</th>
                                <th class="text-left py-3 px-4 font-medium">Tafsilotlar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="run in runs.data" :key="run.id" class="border-b hover:bg-gray-50">
                                <td class="py-3 px-4 font-mono text-xs">{{ run.command }}</td>
                                <td class="py-3 px-4">
                                    <Badge :variant="getStatusBadge(run.status)">
                                        {{ getStatusText(run.status) }}
                                    </Badge>
                                </td>
                                <td class="py-3 px-4">{{ run.records_processed }}</td>
                                <td class="py-3 px-4">{{ run.duration_ms ? run.duration_ms + 'ms' : '-' }}</td>
                                <td class="py-3 px-4 text-xs">{{ formatDate(run.started_at) }}</td>
                                <td class="py-3 px-4 text-xs max-w-xs truncate">
                                    <span v-if="run.details">
                                        {{ JSON.stringify(run.details) }}
                                    </span>
                                    <span v-else-if="run.error" class="text-red-600">
                                        {{ run.error }}
                                    </span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                            </tr>
                            <tr v-if="runs.data.length === 0">
                                <td colspan="6" class="py-8 text-center text-gray-500">
                                    Hali hech qanday ishga tushirish yo'q
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="runs.last_page > 1" class="flex justify-center gap-2 mt-4">
                    <Button
                        v-for="page in runs.last_page"
                        :key="page"
                        :variant="page === runs.current_page ? 'default' : 'outline'"
                        size="sm"
                        @click="router.get(route('admin.scheduler.index', { page }))"
                    >
                        {{ page }}
                    </Button>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
