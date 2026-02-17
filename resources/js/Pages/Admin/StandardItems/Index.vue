<script setup>
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/UI/Pagination.vue';

import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

defineOptions({ layout: AdminLayout });

const { t, locale } = useI18n();

const props = defineProps({
  items: Object,
  filters: Object,
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');

const applyFilters = () => {
  router.get(route('admin.standard-items.index'), {
    search: search.value || undefined,
    status: status.value || undefined,
  }, { preserveState: true, replace: true });
};

const resetFilters = () => {
  search.value = '';
  status.value = '';
  router.get(route('admin.standard-items.index'));
};

let searchTimeout;
watch(search, () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(applyFilters, 300);
});
watch(status, applyFilters);

const getTranslation = (item, field) => {
  if (!item[field]) return '';
  if (typeof item[field] === 'string') return item[field];
  return item[field][locale.value] || item[field]['uz'] || Object.values(item[field])[0] || '';
};

const deleteItem = (id) => {
  if (confirm(t('standardItems.confirmDelete'))) {
    router.delete(route('admin.standard-items.destroy', id));
  }
};

const hasActiveFilters = () => search.value || status.value;
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">{{ t('standardItems.title') }}</h1>
        <p class="text-muted-foreground">{{ t('standardItems.subtitle') }}</p>
      </div>
      <Button as-child>
        <Link href="/admin/standard-items/create">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          {{ t('common.addNew') }}
        </Link>
      </Button>
    </div>

    <Card>
      <CardHeader class="pb-4">
        <div class="flex flex-col lg:flex-row lg:items-center gap-4">
          <Input v-model="search" :placeholder="t('common.search') + '...'" class="lg:w-64" />
          <Select v-model="status">
            <SelectTrigger class="lg:w-40">
              <SelectValue :placeholder="t('common.allStatuses')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">{{ t('common.allStatuses') }}</SelectItem>
              <SelectItem value="active">{{ t('common.active') }}</SelectItem>
              <SelectItem value="inactive">{{ t('common.inactive') }}</SelectItem>
            </SelectContent>
          </Select>
          <Button v-if="hasActiveFilters()" variant="ghost" @click="resetFilters">{{ t('common.reset') }}</Button>
        </div>
      </CardHeader>

      <CardContent class="p-0">
        <Table v-if="items.data.length > 0">
          <TableHeader>
            <TableRow>
              <TableHead class="w-12">#</TableHead>
              <TableHead>{{ t('common.name') }}</TableHead>
              <TableHead>{{ t('standardItems.description') }}</TableHead>
              <TableHead>{{ t('common.status') }}</TableHead>
              <TableHead class="text-center">{{ t('common.actions') }}</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="(item, index) in items.data" :key="item.id">
              <TableCell class="text-muted-foreground">{{ index + 1 }}</TableCell>
              <TableCell class="font-medium">{{ getTranslation(item, 'name') }}</TableCell>
              <TableCell class="text-muted-foreground max-w-xs truncate">{{ getTranslation(item, 'description') || '-' }}</TableCell>
              <TableCell>
                <Badge v-if="item.status" class="bg-green-500/10 text-green-700">{{ t('common.active') }}</Badge>
                <Badge v-else class="bg-red-500/10 text-red-700">{{ t('common.inactive') }}</Badge>
              </TableCell>
              <TableCell>
                <div class="flex items-center justify-center gap-1">
                  <Button variant="ghost" size="icon" as-child>
                    <Link :href="route('admin.standard-items.edit', item.id)">
                      <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                    </Link>
                  </Button>
                  <Button variant="ghost" size="icon" @click="deleteItem(item.id)">
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
            <svg class="w-8 h-8 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
          </div>
          <h3 class="text-lg font-semibold mb-2">{{ t('common.noData') }}</h3>
          <p class="text-muted-foreground mb-4">{{ t('standardItems.emptyMessage') }}</p>
          <Button as-child><Link href="/admin/standard-items/create">{{ t('standardItems.addFirst') }}</Link></Button>
        </div>
      </CardContent>

      <div v-if="items.data.length > 0" class="flex items-center justify-between px-6 py-4 border-t">
        <p class="text-sm text-muted-foreground">{{ t('common.total') }}: <strong>{{ items.total }}</strong> {{ t('common.records') }}</p>
        <Pagination :links="items.links" />
      </div>
    </Card>
  </div>
</template>
