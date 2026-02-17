<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';

defineOptions({ layout: AdminLayout });

const { t, locale } = useI18n();

const props = defineProps<{
  master: any;
}>();

const activeTab = ref('uz');

const languageTabs = [
  { key: 'uz', label: "O'zbek" },
  { key: 'ru', label: 'Русский' },
  { key: 'en', label: 'English' },
];

const getTranslation = (item: any, field: string) => {
  if (!item[field]) return '';
  if (typeof item[field] === 'string') return item[field];
  return item[field][locale.value] || item[field]['uz'] || Object.values(item[field])[0] || '';
};
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">{{ master.full_name }}</h1>
        <p class="text-muted-foreground">{{ t('masters.info', 'Master ma\'lumotlari') }}</p>
      </div>
      <Button variant="outline" as-child>
        <Link :href="route('admin.masters.index')">{{ t('common.back', 'Orqaga') }}</Link>
      </Button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Profile Card -->
      <div class="lg:col-span-1">
        <Card>
          <CardContent class="pt-6">
            <div class="text-center">
              <img
                :src="master.photo_url"
                :alt="master.full_name"
                class="w-32 h-32 rounded-full mx-auto border-4 border-muted object-cover"
              />
              <h3 class="mt-4 text-xl font-semibold">{{ master.full_name }}</h3>
              <p class="text-muted-foreground">{{ master.gender === 'male' ? t('masters.male', 'Erkak') : t('masters.female', 'Ayol') }}</p>
              <div class="mt-2">
                <Badge :variant="master.status ? 'default' : 'destructive'">
                  {{ master.status ? t('common.active', 'Faol') : t('common.inactive', 'Nofaol') }}
                </Badge>
              </div>
            </div>

            <Separator class="my-6" />

            <div class="space-y-2">
              <Button class="w-full" as-child>
                <Link :href="route('admin.masters.edit', master.id)">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                  {{ t('common.edit', 'Tahrirlash') }}
                </Link>
              </Button>
              <Button variant="outline" class="w-full" as-child>
                <Link :href="route('admin.masters.index')">{{ t('common.back', 'Orqaga') }}</Link>
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Details -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Contact Info -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
              {{ t('masters.contactInfo', 'Aloqa ma\'lumotlari') }}
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-muted-foreground">{{ t('masters.phone', 'Telefon') }}</p>
                <p class="font-medium">
                  <a :href="'tel:' + master.phone" class="text-primary hover:underline">{{ master.phone }}</a>
                </p>
              </div>
              <div>
                <p class="text-sm text-muted-foreground">{{ t('masters.email', 'Email') }}</p>
                <p class="font-medium">
                  <a v-if="master.email" :href="'mailto:' + master.email" class="text-primary hover:underline">{{ master.email }}</a>
                  <span v-else class="text-muted-foreground">-</span>
                </p>
              </div>
              <div>
                <p class="text-sm text-muted-foreground">{{ t('masters.birthDate', 'Tug\'ilgan sana') }}</p>
                <p class="font-medium">{{ master.birth_date || '-' }}</p>
              </div>
              <div>
                <p class="text-sm text-muted-foreground">{{ t('masters.experience', 'Tajriba') }}</p>
                <p class="font-medium">{{ master.experience_years }} {{ t('masters.years', 'yil') }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Services -->
        <Card>
          <CardHeader>
            <CardTitle>{{ t('masters.services', 'Xizmatlar') }}</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="master.service_types && master.service_types.length > 0" class="flex flex-wrap gap-2">
              <Badge v-for="service in master.service_types" :key="service.id" variant="secondary">
                {{ getTranslation(service, 'name') }}
              </Badge>
            </div>
            <p v-else class="text-muted-foreground">{{ t('masters.noServices', 'Xizmatlar yo\'q') }}</p>
          </CardContent>
        </Card>

        <!-- Oils -->
        <Card>
          <CardHeader>
            <CardTitle>{{ t('masters.oils', 'Yog\'lar') }}</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="master.oils && master.oils.length > 0" class="flex flex-wrap gap-2">
              <Badge v-for="oil in master.oils" :key="oil.id" variant="outline" class="bg-green-50 text-green-700 border-green-200 dark:bg-green-950 dark:text-green-300 dark:border-green-800">
                {{ getTranslation(oil, 'name') }}
              </Badge>
            </div>
            <p v-else class="text-muted-foreground">{{ t('masters.noOils', 'Yog\'lar yo\'q') }}</p>
          </CardContent>
        </Card>

        <!-- Bio -->
        <Card>
          <CardHeader>
            <CardTitle>{{ t('masters.bio', 'Biografiya') }}</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="flex gap-2 mb-4">
              <Button
                v-for="tab in languageTabs"
                :key="tab.key"
                type="button"
                :variant="activeTab === tab.key ? 'default' : 'outline'"
                size="sm"
                @click="activeTab = tab.key"
              >
                {{ tab.label }}
              </Button>
            </div>

            <div v-show="activeTab === 'uz'">
              <p v-if="master.bio?.uz" class="whitespace-pre-line">{{ master.bio.uz }}</p>
              <p v-else class="text-muted-foreground italic">{{ t('translations.noDescription', 'Tavsif yo\'q') }}</p>
            </div>
            <div v-show="activeTab === 'ru'">
              <p v-if="master.bio?.ru" class="whitespace-pre-line">{{ master.bio.ru }}</p>
              <p v-else class="text-muted-foreground italic">{{ t('translations.noDescription', 'Tavsif yo\'q') }}</p>
            </div>
            <div v-show="activeTab === 'en'">
              <p v-if="master.bio?.en" class="whitespace-pre-line">{{ master.bio.en }}</p>
              <p v-else class="text-muted-foreground italic">{{ t('translations.noDescription', 'Tavsif yo\'q') }}</p>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </div>
</template>
