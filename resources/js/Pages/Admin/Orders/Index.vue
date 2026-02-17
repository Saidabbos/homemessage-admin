<script setup>
import { ref, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/UI/Pagination.vue';

// shadcn-vue components
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';

const debounce = (fn, delay) => {
  let timeoutId;
  return (...args) => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => fn(...args), delay);
  };
};

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps({
  orders: Object,
  filters: Object,
  masters: Array,
  statuses: Array,
  paymentStatuses: Array,
  statusCounts: Object,
  isNewOrdersPage: {
    type: Boolean,
    default: false,
  },
});

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const paymentStatusFilter = ref(props.filters?.payment_status || '');
const masterFilter = ref(props.filters?.master_id || '');

const applyFilters = debounce(() => {
  router.get(
    props.isNewOrdersPage ? route('admin.orders.new') : route('admin.orders.index'),
    {
      search: search.value || undefined,
      status: statusFilter.value || undefined,
      payment_status: paymentStatusFilter.value || undefined,
      master_id: masterFilter.value || undefined,
    },
    { preserveState: true, replace: true }
  );
}, 300);

watch([search, statusFilter, paymentStatusFilter, masterFilter], applyFilters);

const resetFilters = () => {
  search.value = '';
  statusFilter.value = '';
  paymentStatusFilter.value = '';
  masterFilter.value = '';
};

const getStatusVariant = (status) => {
  const variants = {
    'NEW': 'default',
    'CONFIRMING': 'secondary',
    'CONFIRMED': 'default',
    'IN_PROGRESS': 'secondary',
    'COMPLETED': 'outline',
    'CANCELLED': 'destructive',
  };
  return variants[status] || 'outline';
};

const getStatusColor = (status) => {
  const colors = {
    'NEW': 'bg-blue-500/10 text-blue-700 hover:bg-blue-500/20',
    'CONFIRMING': 'bg-yellow-500/10 text-yellow-700 hover:bg-yellow-500/20',
    'CONFIRMED': 'bg-green-500/10 text-green-700 hover:bg-green-500/20',
    'IN_PROGRESS': 'bg-purple-500/10 text-purple-700 hover:bg-purple-500/20',
    'COMPLETED': 'bg-gray-500/10 text-gray-700 hover:bg-gray-500/20',
    'CANCELLED': 'bg-red-500/10 text-red-700 hover:bg-red-500/20',
  };
  return colors[status] || 'bg-gray-500/10 text-gray-700';
};

const getPaymentColor = (status) => {
  const colors = {
    'NOT_PAID': 'bg-red-500/10 text-red-700',
    'PAID': 'bg-green-500/10 text-green-700',
    'REFUNDED': 'bg-yellow-500/10 text-yellow-700',
  };
  return colors[status] || 'bg-gray-500/10 text-gray-700';
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('uz-UZ');
};

const formatArrivalWindow = (order) => {
  if (!order.arrival_window_start || !order.arrival_window_end) return '-';
  const start = order.arrival_window_start?.substring(0, 5);
  const end = order.arrival_window_end?.substring(0, 5);
  return `${start} – ${end}`;
};

// Tree table
const groupColors = ['#3b82f6', '#22c55e', '#f97316', '#8b5cf6', '#ec4899', '#06b6d4'];
const expandedGroups = ref({});

const treeRows = computed(() => {
  const rows = [];
  const grouped = {};
  let colorIdx = 0;

  props.orders.data.forEach(order => {
    if (order.booking_group_id && order.group_count > 1) {
      if (!grouped[order.booking_group_id]) {
        grouped[order.booking_group_id] = {
          color: groupColors[colorIdx % groupColors.length],
          orders: [],
        };
        colorIdx++;
      }
      grouped[order.booking_group_id].orders.push(order);
    }
  });

  const seen = {};
  props.orders.data.forEach(order => {
    if (order.booking_group_id && order.group_count > 1) {
      if (!seen[order.booking_group_id]) {
        seen[order.booking_group_id] = true;
        const group = grouped[order.booking_group_id];
        rows.push({
          type: 'parent',
          order: group.orders[0],
          groupId: order.booking_group_id,
          groupColor: group.color,
          childCount: group.orders.length,
        });
        for (let i = 1; i < group.orders.length; i++) {
          rows.push({
            type: 'child',
            order: group.orders[i],
            groupId: order.booking_group_id,
            groupColor: group.color,
          });
        }
      }
    } else {
      rows.push({ type: 'single', order });
    }
  });

  return rows;
});

const toggleGroup = (groupId) => {
  expandedGroups.value[groupId] = !isExpanded(groupId);
};

const isExpanded = (groupId) => {
  return expandedGroups.value[groupId] !== false;
};

const isRowVisible = (row) => {
  if (row.type !== 'child') return true;
  return isExpanded(row.groupId);
};
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">
          {{ isNewOrdersPage ? t('orders.newOrders') : t('orders.title') }}
        </h1>
        <p class="text-muted-foreground">{{ t('orders.subtitle') }}</p>
      </div>
    </div>

    <!-- Status Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
      <Card
        v-for="status in statuses"
        :key="status.value"
        class="cursor-pointer transition-all hover:shadow-md"
        :class="{ 'ring-2 ring-primary': statusFilter === status.value }"
        @click="statusFilter = statusFilter === status.value ? '' : status.value"
      >
        <CardContent class="p-4">
          <div class="text-2xl font-bold">{{ statusCounts[status.value] || 0 }}</div>
          <p class="text-xs text-muted-foreground">{{ t(`orderStatuses.${status.value}`) }}</p>
        </CardContent>
      </Card>
    </div>

    <!-- Main Card -->
    <Card>
      <CardHeader class="pb-4">
        <div class="flex flex-col lg:flex-row lg:items-center gap-4">
          <Input
            v-model="search"
            :placeholder="t('orders.searchPlaceholder')"
            class="lg:w-64"
          />
          <Select v-if="!isNewOrdersPage" v-model="statusFilter">
            <SelectTrigger class="lg:w-48">
              <SelectValue :placeholder="t('orders.allStatuses')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">{{ t('orders.allStatuses') }}</SelectItem>
              <SelectItem v-for="status in statuses" :key="status.value" :value="status.value">
                {{ t(`orderStatuses.${status.value}`) }}
              </SelectItem>
            </SelectContent>
          </Select>
          <Select v-model="paymentStatusFilter">
            <SelectTrigger class="lg:w-48">
              <SelectValue :placeholder="t('orders.allPaymentStatuses')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">{{ t('orders.allPaymentStatuses') }}</SelectItem>
              <SelectItem v-for="status in paymentStatuses" :key="status.value" :value="status.value">
                {{ t(`paymentStatuses.${status.value}`) }}
              </SelectItem>
            </SelectContent>
          </Select>
          <Select v-model="masterFilter">
            <SelectTrigger class="lg:w-48">
              <SelectValue :placeholder="t('orders.allMasters')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">{{ t('orders.allMasters') }}</SelectItem>
              <SelectItem v-for="master in masters" :key="master.id" :value="String(master.id)">
                {{ master.first_name }} {{ master.last_name }}
              </SelectItem>
            </SelectContent>
          </Select>
          <Button variant="outline" @click="resetFilters">
            {{ t('common.reset') }}
          </Button>
        </div>
      </CardHeader>

      <CardContent class="p-0">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>{{ t('orders.orderNumber') }}</TableHead>
              <TableHead>{{ t('orders.customer') }}</TableHead>
              <TableHead>{{ t('orders.master') }}</TableHead>
              <TableHead>{{ t('orders.dateTime') }}</TableHead>
              <TableHead>{{ t('orders.service') }}</TableHead>
              <TableHead class="text-center">{{ t('common.status') }}</TableHead>
              <TableHead class="text-center">{{ t('orders.payment') }}</TableHead>
              <TableHead class="text-center">{{ t('common.actions') }}</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <template v-for="row in treeRows" :key="row.order.id">
              <TableRow
                v-if="isRowVisible(row)"
                class="transition-colors"
                :style="row.type !== 'single' ? { borderLeft: `3px solid ${row.groupColor}` } : {}"
                :class="{
                  'bg-muted/30': row.type === 'parent',
                  'bg-muted/10': row.type === 'child',
                }"
              >
                <!-- Order Number -->
                <TableCell class="font-medium">
                  <div class="flex items-center gap-2">
                    <button
                      v-if="row.type === 'parent'"
                      @click="toggleGroup(row.groupId)"
                      class="flex items-center justify-center w-5 h-5 rounded hover:bg-muted transition-colors"
                    >
                      <svg
                        class="w-4 h-4 text-muted-foreground transition-transform"
                        :class="{ 'rotate-90': isExpanded(row.groupId) }"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                      >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                      </svg>
                    </button>
                    <span v-if="row.type === 'child'" class="w-5 pl-2 text-muted-foreground">└</span>
                    <Link :href="route('admin.orders.show', row.order.id)" class="text-primary hover:underline flex items-center gap-2">
                      {{ row.order.order_number }}
                      <Badge
                        v-if="row.type === 'parent'"
                        variant="outline"
                        class="text-xs"
                        :style="{ borderColor: row.groupColor, color: row.groupColor }"
                      >
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ row.childCount }}
                      </Badge>
                    </Link>
                  </div>
                </TableCell>
                <!-- Customer -->
                <TableCell>
                  <div class="font-medium">{{ row.order.customer?.name || '-' }}</div>
                  <div class="text-xs text-muted-foreground">{{ row.order.customer?.phone || row.order.contact_phone }}</div>
                </TableCell>
                <!-- Master -->
                <TableCell>
                  {{ row.order.master?.first_name }} {{ row.order.master?.last_name }}
                </TableCell>
                <!-- Date/Time -->
                <TableCell>
                  <div class="font-medium">{{ formatDate(row.order.booking_date) }}</div>
                  <div class="text-xs text-muted-foreground">{{ formatArrivalWindow(row.order) }}</div>
                </TableCell>
                <!-- Service -->
                <TableCell>
                  {{ row.order.service_type?.name?.uz || row.order.service_type?.name }}
                </TableCell>
                <!-- Status -->
                <TableCell class="text-center">
                  <Badge :class="getStatusColor(row.order.status)">
                    {{ t(`orderStatuses.${row.order.status}`) }}
                  </Badge>
                </TableCell>
                <!-- Payment -->
                <TableCell class="text-center">
                  <Badge :class="getPaymentColor(row.order.payment_status)">
                    {{ t(`paymentStatuses.${row.order.payment_status}`) }}
                  </Badge>
                </TableCell>
                <!-- Actions -->
                <TableCell class="text-center">
                  <Button variant="ghost" size="sm" as-child>
                    <Link :href="route('admin.orders.show', row.order.id)">
                      {{ t('common.view') }}
                    </Link>
                  </Button>
                </TableCell>
              </TableRow>
            </template>
            <TableRow v-if="orders.data.length === 0">
              <TableCell colspan="8" class="h-24 text-center text-muted-foreground">
                {{ t('orders.emptyMessage') }}
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </CardContent>

      <!-- Pagination -->
      <div v-if="orders.data.length > 0" class="flex items-center justify-between px-6 py-4 border-t">
        <p class="text-sm text-muted-foreground">
          {{ t('common.total') }}: {{ orders.total }} {{ t('common.records') }}
        </p>
        <Pagination :links="orders.links" />
      </div>
    </Card>
  </div>
</template>
