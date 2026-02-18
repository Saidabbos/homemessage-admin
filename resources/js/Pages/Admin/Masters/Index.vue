<script setup>
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/UI/Pagination.vue';

// shadcn-vue components
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
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

defineOptions({ layout: AdminLayout });

const { t, locale } = useI18n();

const props = defineProps({
  masters: Object,
  serviceTypes: Array,
  filters: Object,
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const gender = ref(props.filters?.gender || '');
const serviceType = ref(props.filters?.service_type || '');

const applyFilters = () => {
  router.get(route('admin.masters.index'), {
    search: search.value || undefined,
    status: status.value || undefined,
    gender: gender.value || undefined,
    service_type: serviceType.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const resetFilters = () => {
  search.value = '';
  status.value = '';
  gender.value = '';
  serviceType.value = '';
  router.get(route('admin.masters.index'));
};

let searchTimeout;
watch(search, () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(applyFilters, 300);
});

watch([status, gender, serviceType], applyFilters);

const getTranslation = (item, field) => {
  if (!item[field]) return '';
  if (typeof item[field] === 'string') return item[field];
  return item[field][locale.value] || item[field]['uz'] || Object.values(item[field])[0] || '';
};

const deleteMaster = (id) => {
  if (confirm(t('common.confirmDelete'))) {
    router.delete(route('admin.masters.destroy', id));
  }
};

const hasActiveFilters = () => search.value || status.value || gender.value || serviceType.value;
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">{{ t('masters.title') }}</h1>
        <p class="text-muted-foreground">{{ t('masters.subtitle') }}</p>
      </div>
      <Button as-child>
        <Link href="/admin/masters/create">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          {{ t('common.addNew') }}
        </Link>
      </Button>
    </div>

    <!-- Main Card -->
    <Card>
      <CardHeader class="pb-4">
        <div class="flex flex-col lg:flex-row lg:items-center gap-4">
          <Input
            v-model="search"
            :placeholder="t('common.search') + '...'"
            class="lg:w-64"
          />
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
          <Select v-model="gender">
            <SelectTrigger class="lg:w-40">
              <SelectValue :placeholder="t('masters.gender')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">{{ t('masters.gender') }}</SelectItem>
              <SelectItem value="male">{{ t('masters.male') }}</SelectItem>
              <SelectItem value="female">{{ t('masters.female') }}</SelectItem>
            </SelectContent>
          </Select>
          <Select v-model="serviceType">
            <SelectTrigger class="lg:w-48">
              <SelectValue :placeholder="t('masters.services')" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">{{ t('masters.services') }}</SelectItem>
              <SelectItem v-for="st in serviceTypes" :key="st.id" :value="String(st.id)">
                {{ getTranslation(st, 'name') }}
              </SelectItem>
            </SelectContent>
          </Select>
          <Button v-if="hasActiveFilters()" variant="ghost" @click="resetFilters">
            {{ t('common.reset') }}
          </Button>
        </div>
      </CardHeader>

      <CardContent class="p-0">
        <Table v-if="masters.data.length > 0">
          <TableHeader>
            <TableRow>
              <TableHead class="w-12">#</TableHead>
              <TableHead class="w-16">{{ t('common.image') }}</TableHead>
              <TableHead>{{ t('masters.fullName') }}</TableHead>
              <TableHead>{{ t('masters.phone') }}</TableHead>
              <TableHead>{{ t('masters.experience') }}</TableHead>
              <TableHead class="text-center">📲</TableHead>
              <TableHead>{{ t('common.status') }}</TableHead>
              <TableHead class="text-center">{{ t('common.actions') }}</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="(master, index) in masters.data" :key="master.id">
              <TableCell class="text-muted-foreground">{{ index + 1 }}</TableCell>
              <TableCell>
                <Avatar>
                  <AvatarImage :src="master.photo_url" :alt="master.full_name" />
                  <AvatarFallback>{{ master.full_name?.charAt(0) }}</AvatarFallback>
                </Avatar>
              </TableCell>
              <TableCell>
                <div class="font-medium">{{ master.full_name }}</div>
                <div class="text-xs text-muted-foreground">
                  {{ master.gender === 'male' ? t('masters.male') : t('masters.female') }}
                </div>
              </TableCell>
              <TableCell>
                <a :href="'tel:' + master.phone" class="text-primary hover:underline">
                  {{ master.phone }}
                </a>
              </TableCell>
              <TableCell>
                {{ master.experience_years }} {{ t('masters.years') }}
              </TableCell>
              <TableCell class="text-center">
                <span v-if="master.telegram_id" class="text-green-600" :title="master.telegram_username ? '@' + master.telegram_username : 'Ulangan'">✅</span>
                <span v-else class="text-muted-foreground" title="Ulanmagan">—</span>
              </TableCell>
              <TableCell>
                <Badge v-if="master.status" class="bg-green-500/10 text-green-700">
                  {{ t('common.active') }}
                </Badge>
                <Badge v-else variant="destructive" class="bg-red-500/10 text-red-700">
                  {{ t('common.inactive') }}
                </Badge>
              </TableCell>
              <TableCell>
                <div class="flex items-center justify-center gap-1">
                  <Button variant="ghost" size="icon" as-child>
                    <Link :href="route('admin.masters.show', master.id)" :title="t('common.view')">
                      <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      </svg>
                    </Link>
                  </Button>
                  <Button variant="ghost" size="icon" as-child>
                    <Link :href="route('admin.masters.schedule', master.id)" :title="t('masters.schedule')">
                      <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                      </svg>
                    </Link>
                  </Button>
                  <Button variant="ghost" size="icon" as-child>
                    <Link :href="route('admin.masters.edit', master.id)" :title="t('common.edit')">
                      <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                    </Link>
                  </Button>
                  <Button variant="ghost" size="icon" @click="deleteMaster(master.id)" :title="t('common.delete')">
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
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
          </div>
          <h3 class="text-lg font-semibold mb-2">{{ t('common.noData') }}</h3>
          <p class="text-muted-foreground mb-4">{{ t('masters.emptyMessage') }}</p>
          <Button as-child>
            <Link href="/admin/masters/create">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              {{ t('masters.addFirst') }}
            </Link>
          </Button>
        </div>
      </CardContent>

      <!-- Pagination -->
      <div v-if="masters.data.length > 0" class="flex items-center justify-between px-6 py-4 border-t">
        <p class="text-sm text-muted-foreground">
          {{ t('common.total') }}: <strong>{{ masters.total }}</strong> {{ t('common.records') }}
        </p>
        <Pagination :links="masters.links" />
      </div>
    </Card>
  </div>
</template>
