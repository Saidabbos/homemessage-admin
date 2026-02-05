<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const { t, locale } = useI18n();

const props = defineProps({
  master: Object,
});

const activeTab = ref('uz');

const getTranslation = (item, field) => {
  if (!item[field]) return '';
  if (typeof item[field] === 'string') return item[field];
  return item[field][locale.value] || item[field]['uz'] || Object.values(item[field])[0] || '';
};
</script>

<template>
  <div>
    <!-- Content Header -->
    <div class="mb-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-[#1f2d3d]">{{ master.full_name }}</h1>
          <p class="text-sm text-[#6c757d] mt-1">{{ t('masters.info') }}</p>
        </div>
        <nav class="mt-2 sm:mt-0">
          <ol class="flex items-center text-sm">
            <li><Link href="/admin/dashboard" class="text-[#007bff]">{{ t('common.home') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li><Link href="/admin/masters" class="text-[#007bff]">{{ t('masters.title') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li class="text-[#6c757d]">{{ master.full_name }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <!-- Profile Card -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded shadow-sm">
          <div class="p-6 text-center border-b border-gray-200">
            <img
              :src="master.photo_url"
              :alt="master.full_name"
              class="w-32 h-32 rounded-full mx-auto border-4 border-gray-200 object-cover"
            />
            <h3 class="mt-4 text-xl font-semibold text-[#1f2d3d]">{{ master.full_name }}</h3>
            <p class="text-[#6c757d]">{{ master.gender === 'male' ? t('masters.male') : t('masters.female') }}</p>
            <div class="mt-2">
              <span
                v-if="master.status"
                class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full bg-[#d4edda] text-[#155724]"
              >
                {{ t('common.active') }}
              </span>
              <span
                v-else
                class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full bg-[#f8d7da] text-[#721c24]"
              >
                {{ t('common.inactive') }}
              </span>
            </div>
          </div>

          <!-- Actions -->
          <div class="p-4 border-t border-gray-200">
            <Link
              :href="route('admin.masters.edit', master.id)"
              class="block w-full px-4 py-2 bg-[#ffc107] text-[#1f2d3d] font-medium rounded hover:bg-[#e0a800] transition text-center"
            >
              {{ t('common.edit') }}
            </Link>
            <Link
              href="/admin/masters"
              class="block w-full mt-2 px-4 py-2 bg-[#6c757d] text-white font-medium rounded hover:bg-[#5a6268] transition text-center"
            >
              {{ t('common.back') }}
            </Link>
          </div>
        </div>
      </div>

      <!-- Details -->
      <div class="lg:col-span-2">
        <!-- Contact Info -->
        <div class="bg-white rounded shadow-sm">
          <div class="px-4 py-3 border-b border-gray-200 bg-[#17a2b8]">
            <h3 class="font-semibold text-white">{{ t('masters.contactInfo') }}</h3>
          </div>
          <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="text-sm text-[#6c757d]">{{ t('masters.phone') }}</label>
                <p class="font-medium text-[#1f2d3d]">
                  <a :href="'tel:' + master.phone" class="text-[#007bff] hover:underline">{{ master.phone }}</a>
                </p>
              </div>
              <div>
                <label class="text-sm text-[#6c757d]">{{ t('masters.email') }}</label>
                <p class="font-medium text-[#1f2d3d]">
                  <a v-if="master.email" :href="'mailto:' + master.email" class="text-[#007bff] hover:underline">{{ master.email }}</a>
                  <span v-else class="text-[#6c757d]">-</span>
                </p>
              </div>
              <div>
                <label class="text-sm text-[#6c757d]">{{ t('masters.birthDate') }}</label>
                <p class="font-medium text-[#1f2d3d]">{{ master.birth_date || '-' }}</p>
              </div>
              <div>
                <label class="text-sm text-[#6c757d]">{{ t('masters.experience') }}</label>
                <p class="font-medium text-[#1f2d3d]">{{ master.experience_years }} {{ t('masters.years') }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Services -->
        <div class="bg-white rounded shadow-sm mt-4">
          <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="font-semibold text-[#1f2d3d]">{{ t('masters.services') }}</h3>
          </div>
          <div class="p-4">
            <div v-if="master.service_types && master.service_types.length > 0" class="flex flex-wrap gap-2">
              <span
                v-for="service in master.service_types"
                :key="service.id"
                class="px-3 py-1 bg-[#e7f3ff] text-[#007bff] text-sm font-medium rounded-full"
              >
                {{ getTranslation(service, 'name') }}
              </span>
            </div>
            <p v-else class="text-[#6c757d]">{{ t('masters.noServices') }}</p>
          </div>
        </div>

        <!-- Oils -->
        <div class="bg-white rounded shadow-sm mt-4">
          <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="font-semibold text-[#1f2d3d]">{{ t('masters.oils') }}</h3>
          </div>
          <div class="p-4">
            <div v-if="master.oils && master.oils.length > 0" class="flex flex-wrap gap-2">
              <span
                v-for="oil in master.oils"
                :key="oil.id"
                class="px-3 py-1 bg-[#d4edda] text-[#155724] text-sm font-medium rounded-full"
              >
                {{ getTranslation(oil, 'name') }}
              </span>
            </div>
            <p v-else class="text-[#6c757d]">{{ t('masters.noOils') }}</p>
          </div>
        </div>

        <!-- Bio -->
        <div class="bg-white rounded shadow-sm mt-4">
          <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="font-semibold text-[#1f2d3d]">{{ t('masters.bio') }}</h3>
          </div>
          <div class="p-4">
            <!-- Language Tabs -->
            <div class="flex border-b border-gray-200 mb-4">
              <button
                @click="activeTab = 'uz'"
                :class="['px-4 py-2 text-sm font-medium border-b-2 -mb-px', activeTab === 'uz' ? 'border-[#007bff] text-[#007bff]' : 'border-transparent text-[#6c757d] hover:text-[#1f2d3d]']"
              >
                O'zbek
              </button>
              <button
                @click="activeTab = 'ru'"
                :class="['px-4 py-2 text-sm font-medium border-b-2 -mb-px', activeTab === 'ru' ? 'border-[#007bff] text-[#007bff]' : 'border-transparent text-[#6c757d] hover:text-[#1f2d3d]']"
              >
                Русский
              </button>
              <button
                @click="activeTab = 'en'"
                :class="['px-4 py-2 text-sm font-medium border-b-2 -mb-px', activeTab === 'en' ? 'border-[#007bff] text-[#007bff]' : 'border-transparent text-[#6c757d] hover:text-[#1f2d3d]']"
              >
                English
              </button>
            </div>

            <div v-show="activeTab === 'uz'">
              <p v-if="master.bio?.uz" class="text-[#1f2d3d] whitespace-pre-line">{{ master.bio.uz }}</p>
              <p v-else class="text-[#6c757d] italic">{{ t('translations.noDescription') }}</p>
            </div>
            <div v-show="activeTab === 'ru'">
              <p v-if="master.bio?.ru" class="text-[#1f2d3d] whitespace-pre-line">{{ master.bio.ru }}</p>
              <p v-else class="text-[#6c757d] italic">{{ t('translations.noDescription') }}</p>
            </div>
            <div v-show="activeTab === 'en'">
              <p v-if="master.bio?.en" class="text-[#1f2d3d] whitespace-pre-line">{{ master.bio.en }}</p>
              <p v-else class="text-[#6c757d] italic">{{ t('translations.noDescription') }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
