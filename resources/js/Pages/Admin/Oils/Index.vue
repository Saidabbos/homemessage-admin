<script setup>
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/UI/Pagination.vue';

// shadcn-vue components
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import {
  Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from '@/components/ui/table';
import {
  Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select';

defineOptions({ layout: AdminLayout });

const { t, locale } = useI18n();

const props = defineProps({
  oils: Object,
  filters: Object,
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || 'all');

const applyFilters = () => {
  router.get(route('admin.oils.index'), {
    search: search.value || undefined,
    status: status.value === 'all' ? undefined : status.value,
  }, { preserveState: true, replace: true });
};

const resetFilters = () => {
  search.value = '';
  status.value = 'all';
  router.get(route('admin.oils.index'));
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

const deleteOil = (id) => {
  if (confirm(t('oils.confirmDelete'))) {
    router.delete(route('admin.oils.destroy', id));
  }
};

const hasActiveFilters = () => search.value || (status.value && status.value !== 'all');
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">{{ t('oils.title') }}</h1>
        <p class="text-muted-foreground">{{ t('oils.description') }}</p>
      </div>
      <Button as-child>
        <Link href="/admin/oils/create">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          {{ t('oils.new') }}
        </Link>
      </Button>
    </div>

    <!-- Main Card -->
    <Card>
      <CardHeader class="pb-4">
        <div class="flex flex-col lg:flex-row lg:items-center gap-4">
          <Input v-model="search" :placeholder="t('common.search') + '...'" class="lg:w-64" />
          <Select v-model="status">
            <SelectTrigger class="lg:w-40">
              <SelectValue :placeholder="t('common.allStatuses')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">{{ t('common.allStatuses') }}</SelectItem>
              <SelectItem value="active">{{ t('common.active') }}</SelectItem>
              <SelectItem value="inactive">{{ t('common.inactive') }}</SelectItem>
            </SelectContent>
          </Select>
          <Button v-if="hasActiveFilters()" variant="ghost" @click="resetFilters">
            {{ t('common.reset') }}
          </Button>
        </div>
      </CardHeader>

      <CardContent class="p-0">
        <Table v-if="oils.data.length > 0">
          <TableHeader>
            <TableRow>
              <TableHead class="w-12">#</TableHead>
              <TableHead class="w-16">{{ t('common.image') }}</TableHead>
              <TableHead>{{ t('common.name') }}</TableHead>
              <TableHead>{{ t('oils.benefits') }}</TableHead>
              <TableHead>{{ t('common.status') }}</TableHead>
              <TableHead class="text-center">{{ t('common.actions') }}</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="(oil, index) in oils.data" :key="oil.id">
              <TableCell class="text-muted-foreground">{{ index + 1 }}</TableCell>
              <TableCell>
                <Avatar class="rounded-md">
                  <AvatarImage :src="oil.image_url" :alt="getTranslation(oil, 'name')" />
                  <AvatarFallback class="rounded-md bg-amber-100 text-amber-700">
                    {{ getTranslation(oil, 'name')?.charAt(0) }}
                  </AvatarFallback>
                </Avatar>
              </TableCell>
              <TableCell>
                <div class="font-medium">{{ getTranslation(oil, 'name') }}</div>
                <div class="text-xs text-muted-foreground">{{ oil.slug }}</div>
              </TableCell>
              <TableCell class="max-w-xs">
                <p class="text-sm text-muted-foreground truncate">
                  {{ getTranslation(oil, 'benefits') || '-' }}
                </p>
              </TableCell>
              <TableCell>
                <Badge v-if="oil.status" class="bg-green-500/10 text-green-700">
                  {{ t('common.active') }}
                </Badge>
                <Badge v-else class="bg-red-500/10 text-red-700">
                  {{ t('common.inactive') }}
                </Badge>
              </TableCell>
              <TableCell>
                <div class="flex items-center justify-center gap-1">
                  <Button variant="ghost" size="icon" as-child>
                    <Link :href="route('admin.oils.edit', oil.id)">
                      <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                    </Link>
                  </Button>
                  <Button variant="ghost" size="icon" @click="deleteOil(oil.id)">
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>

        <!-- Empty State -->
        <div v-else class="flex flex-col items-center justify-center py-12">
          <div class="w-16 h-16 bg-muted rounded-full flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
            </svg>
          </div>
          <h3 class="text-lg font-semibold mb-2">{{ t('common.noData') }}</h3>
          <p class="text-muted-foreground mb-4">{{ t('oils.emptyMessage') }}</p>
          <Button as-child>
            <Link href="/admin/oils/create">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              {{ t('oils.addFirst') }}
            </Link>
          </Button>
        </div>
      </CardContent>

      <!-- Pagination -->
      <div v-if="oils.data.length > 0" class="flex items-center justify-between px-6 py-4 border-t">
        <p class="text-sm text-muted-foreground">
          {{ t('common.total') }}: <strong>{{ oils.total }}</strong> {{ t('common.records') }}
        </p>
        <Pagination :links="oils.links" />
      </div>
    </Card>
  </div>
</template>
