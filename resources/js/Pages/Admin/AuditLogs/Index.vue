<script setup>
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

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
    localFilters.value = {
        search: '',
        action: '',
        user_id: '',
        auditable_type: '',
        date_from: '',
        date_to: '',
    };
    router.get('/admin/audit-logs');
};

const getActionColor = (action) => {
    const colors = {
        'created': 'bg-green-100 text-green-800',
        'updated': 'bg-blue-100 text-blue-800',
        'deleted': 'bg-red-100 text-red-800',
        'status_changed': 'bg-yellow-100 text-yellow-800',
        'slot_changed': 'bg-purple-100 text-purple-800',
        'payment_received': 'bg-emerald-100 text-emerald-800',
        'note_added': 'bg-gray-100 text-gray-800',
        'assigned': 'bg-indigo-100 text-indigo-800',
        'login': 'bg-cyan-100 text-cyan-800',
        'logout': 'bg-orange-100 text-orange-800',
    };
    return colors[action] || 'bg-gray-100 text-gray-600';
};

const getActionLabel = (action) => {
    const labels = {
        'created': t('auditLogs.actions.created'),
        'updated': t('auditLogs.actions.updated'),
        'deleted': t('auditLogs.actions.deleted'),
        'status_changed': t('auditLogs.actions.statusChanged'),
        'slot_changed': t('auditLogs.actions.slotChanged'),
        'payment_received': t('auditLogs.actions.paymentReceived'),
        'note_added': t('auditLogs.actions.noteAdded'),
        'assigned': t('auditLogs.actions.assigned'),
        'login': t('auditLogs.actions.login'),
        'logout': t('auditLogs.actions.logout'),
    };
    return labels[action] || action;
};

const getModelName = (type) => {
    if (!type) return '-';
    const parts = type.split('\\');
    return parts[parts.length - 1];
};

const formatDate = (date) => {
    return new Date(date).toLocaleString('uz-UZ', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const showChanges = ref(null);

const toggleChanges = (id) => {
    showChanges.value = showChanges.value === id ? null : id;
};

const formatJson = (data) => {
    if (!data) return '-';
    try {
        return JSON.stringify(data, null, 2);
    } catch {
        return String(data);
    }
};
</script>

<template>
    <div>
        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-[#343a40]">{{ t('auditLogs.title') }}</h1>
            <nav class="text-sm text-[#6c757d]">
                <Link href="/admin/dashboard" class="text-[#007bff] hover:underline">{{ t('common.home') }}</Link>
                <span class="mx-2">/</span>
                <span>{{ t('auditLogs.title') }}</span>
            </nav>
        </div>

        <!-- Filters Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
            <div class="p-4 border-b border-gray-200">
                <h3 class="font-medium text-[#343a40]">{{ t('common.filters') }}</h3>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('common.search') }}</label>
                        <input
                            v-model="localFilters.search"
                            type="text"
                            :placeholder="t('auditLogs.searchPlaceholder')"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-[#007bff] focus:border-[#007bff]"
                            @keyup.enter="applyFilters"
                        />
                    </div>

                    <!-- Action -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('auditLogs.action') }}</label>
                        <select
                            v-model="localFilters.action"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-[#007bff] focus:border-[#007bff]"
                        >
                            <option value="">{{ t('common.all') }}</option>
                            <option v-for="action in actions" :key="action.value" :value="action.value">
                                {{ action.label }}
                            </option>
                        </select>
                    </div>

                    <!-- User -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('auditLogs.user') }}</label>
                        <select
                            v-model="localFilters.user_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-[#007bff] focus:border-[#007bff]"
                        >
                            <option value="">{{ t('common.all') }}</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">
                                {{ user.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Model Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('auditLogs.model') }}</label>
                        <select
                            v-model="localFilters.auditable_type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-[#007bff] focus:border-[#007bff]"
                        >
                            <option value="">{{ t('common.all') }}</option>
                            <option v-for="type in types" :key="type.value" :value="type.value">
                                {{ type.label }}
                            </option>
                        </select>
                    </div>

                    <!-- Date From -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('common.dateFrom') }}</label>
                        <input
                            v-model="localFilters.date_from"
                            type="date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-[#007bff] focus:border-[#007bff]"
                        />
                    </div>

                    <!-- Date To -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ t('common.dateTo') }}</label>
                        <input
                            v-model="localFilters.date_to"
                            type="date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-[#007bff] focus:border-[#007bff]"
                        />
                    </div>
                </div>

                <div class="flex gap-2 mt-4">
                    <button
                        @click="applyFilters"
                        class="px-4 py-2 bg-[#007bff] text-white rounded-md hover:bg-[#0069d9] text-sm"
                    >
                        {{ t('common.apply') }}
                    </button>
                    <button
                        @click="resetFilters"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm"
                    >
                        {{ t('common.reset') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="font-medium text-[#343a40]">
                    {{ t('auditLogs.totalRecords', { count: logs.total }) }}
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('auditLogs.date') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('auditLogs.user') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('auditLogs.action') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('auditLogs.model') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('auditLogs.comment') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ t('auditLogs.changes') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <template v-for="log in logs.data" :key="log.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                                    {{ formatDate(log.created_at) }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span v-if="log.user" class="font-medium text-gray-900">{{ log.user.name }}</span>
                                    <span v-else class="text-gray-400">{{ t('auditLogs.system') }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span :class="['px-2 py-1 text-xs font-medium rounded-full', getActionColor(log.action)]">
                                        {{ getActionLabel(log.action) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="text-gray-600">{{ getModelName(log.auditable_type) }}</span>
                                    <span v-if="log.auditable_id" class="text-gray-400 ml-1">#{{ log.auditable_id }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate">
                                    {{ log.comment || '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 font-mono text-xs">
                                    {{ log.ip_address || '-' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button
                                        v-if="log.old_values || log.new_values"
                                        @click="toggleChanges(log.id)"
                                        class="text-[#007bff] hover:text-[#0056b3] text-sm"
                                    >
                                        {{ showChanges === log.id ? t('auditLogs.hide') : t('auditLogs.show') }}
                                    </button>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                            </tr>
                            <!-- Expanded row for changes -->
                            <tr v-if="showChanges === log.id" class="bg-gray-50">
                                <td colspan="7" class="px-4 py-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div v-if="log.old_values">
                                            <h4 class="text-sm font-medium text-gray-700 mb-2">{{ t('auditLogs.oldValues') }}</h4>
                                            <pre class="bg-red-50 p-3 rounded text-xs overflow-x-auto text-red-800">{{ formatJson(log.old_values) }}</pre>
                                        </div>
                                        <div v-if="log.new_values">
                                            <h4 class="text-sm font-medium text-gray-700 mb-2">{{ t('auditLogs.newValues') }}</h4>
                                            <pre class="bg-green-50 p-3 rounded text-xs overflow-x-auto text-green-800">{{ formatJson(log.new_values) }}</pre>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>

                        <tr v-if="logs.data.length === 0">
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                {{ t('auditLogs.noRecords') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="logs.last_page > 1" class="p-4 border-t border-gray-200 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    {{ t('common.showing', { from: logs.from, to: logs.to, total: logs.total }) }}
                </div>
                <div class="flex gap-1">
                    <Link
                        v-for="link in logs.links"
                        :key="link.label"
                        :href="link.url"
                        :class="[
                            'px-3 py-1 text-sm rounded',
                            link.active
                                ? 'bg-[#007bff] text-white'
                                : link.url
                                    ? 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                    : 'bg-gray-50 text-gray-400 cursor-not-allowed'
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
