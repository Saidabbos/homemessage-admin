<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ImageUpload from '@/Components/Admin/ImageUpload.vue';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps({
  oil: Object,
});

const activeTab = ref('uz');

const tabs = [
  { key: 'uz', label: 'O\'zbek', flag: '🇺🇿' },
  { key: 'ru', label: 'Русский', flag: '🇷🇺' },
  { key: 'en', label: 'English', flag: '🇬🇧' },
];

const form = useForm({
  _method: 'PUT',
  slug: props.oil.slug,
  image: null,
  status: props.oil.status,
  uz: {
    name: props.oil.uz?.name || '',
    description: props.oil.uz?.description || '',
  },
  ru: {
    name: props.oil.ru?.name || '',
    description: props.oil.ru?.description || '',
  },
  en: {
    name: props.oil.en?.name || '',
    description: props.oil.en?.description || '',
  },
});

const submit = () => {
  form.patch(route('admin.oils.update', props.oil.id));
};

const deleteOil = () => {
  if (confirm(t('oils.confirmDelete'))) {
    router.delete(route('admin.oils.destroy', props.oil.id));
  }
};
</script>

<template>
  <div>
    <!-- Content Header -->
    <div class="mb-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-[#1f2d3d]">{{ t('oils.edit') }}</h1>
          <p class="text-sm text-[#6c757d] mt-1">{{ oil.uz?.name || oil.slug }}</p>
        </div>
        <nav class="mt-2 sm:mt-0">
          <ol class="flex items-center text-sm">
            <li><Link href="/admin/dashboard" class="text-[#007bff]">{{ t('common.home') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li><Link href="/admin/oils" class="text-[#007bff]">{{ t('oils.title') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li class="text-[#6c757d]">{{ t('common.edit') }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <form @submit.prevent="submit">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-4">
          <!-- Translations Card with Tabs -->
          <div class="bg-white rounded shadow-sm">
            <div class="px-4 py-3 border-b border-gray-200 bg-[#ffc107] rounded-t">
              <h3 class="font-semibold text-[#1f2d3d] flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                {{ t('translations.title') }}
              </h3>
            </div>

            <!-- Tab Navigation -->
            <div class="border-b border-gray-200">
              <ul class="flex flex-wrap -mb-px">
                <li v-for="tab in tabs" :key="tab.key" class="mr-1">
                  <button
                    type="button"
                    @click="activeTab = tab.key"
                    :class="[
                      'inline-flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 transition',
                      activeTab === tab.key
                        ? 'border-[#007bff] text-[#007bff]'
                        : 'border-transparent text-[#6c757d] hover:text-[#1f2d3d] hover:border-gray-300'
                    ]"
                  >
                    <span>{{ tab.flag }}</span>
                    <span>{{ tab.label }}</span>
                  </button>
                </li>
              </ul>
            </div>

            <!-- Tab Content -->
            <div class="p-4">
              <!-- Uzbek -->
              <div v-show="activeTab === 'uz'" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                    {{ t('translations.name') }} <span class="text-[#dc3545]">*</span>
                  </label>
                  <input
                    type="text"
                    v-model="form.uz.name"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                    :placeholder="t('oils.enterName')"
                  />
                  <div v-if="form.errors['uz.name']" class="text-[#dc3545] text-xs mt-1">{{ form.errors['uz.name'] }}</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">{{ t('translations.description') }}</label>
                  <textarea
                    v-model="form.uz.description"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                    :placeholder="t('oils.enterDescription')"
                  ></textarea>
                </div>
              </div>

              <!-- Russian -->
              <div v-show="activeTab === 'ru'" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                    {{ t('translations.name') }} <span class="text-[#dc3545]">*</span>
                  </label>
                  <input
                    type="text"
                    v-model="form.ru.name"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                    :placeholder="t('oils.enterName')"
                  />
                  <div v-if="form.errors['ru.name']" class="text-[#dc3545] text-xs mt-1">{{ form.errors['ru.name'] }}</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">{{ t('translations.description') }}</label>
                  <textarea
                    v-model="form.ru.description"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                    :placeholder="t('oils.enterDescription')"
                  ></textarea>
                </div>
              </div>

              <!-- English -->
              <div v-show="activeTab === 'en'" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                    {{ t('translations.name') }} <span class="text-[#dc3545]">*</span>
                  </label>
                  <input
                    type="text"
                    v-model="form.en.name"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                    :placeholder="t('oils.enterName')"
                  />
                  <div v-if="form.errors['en.name']" class="text-[#dc3545] text-xs mt-1">{{ form.errors['en.name'] }}</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">{{ t('translations.description') }}</label>
                  <textarea
                    v-model="form.en.description"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                    :placeholder="t('oils.enterDescription')"
                  ></textarea>
                </div>
              </div>
            </div>
          </div>

          <!-- Slug Card -->
          <div class="bg-white rounded shadow-sm">
            <div class="px-4 py-3 border-b border-gray-200">
              <h3 class="font-semibold text-[#1f2d3d] text-sm">{{ t('common.slug') }}</h3>
            </div>
            <div class="p-4">
              <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                {{ t('common.slug') }} <span class="text-[#dc3545]">*</span>
              </label>
              <input
                type="text"
                v-model="form.slug"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm font-mono"
                placeholder="oil-slug"
              />
              <p class="text-xs text-[#6c757d] mt-1">{{ t('validation.slugHint') }}</p>
              <div v-if="form.errors.slug" class="text-[#dc3545] text-xs mt-1">{{ form.errors.slug }}</div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-4">
          <!-- Current Image -->
          <div v-if="oil.image_url" class="bg-white rounded shadow-sm">
            <div class="px-4 py-3 border-b border-gray-200">
              <h3 class="font-semibold text-[#1f2d3d] text-sm">{{ t('common.image') }}</h3>
            </div>
            <div class="p-4">
              <img :src="oil.image_url" :alt="oil.slug" class="w-full rounded border border-gray-200" />
            </div>
          </div>

          <!-- Image Upload Card -->
          <div class="bg-white rounded shadow-sm">
            <div class="px-4 py-3 border-b border-gray-200">
              <h3 class="font-semibold text-[#1f2d3d] text-sm">{{ t('common.image') }}</h3>
            </div>
            <div class="p-4">
              <ImageUpload v-model="form.image" />
              <div v-if="form.errors.image" class="text-[#dc3545] text-xs mt-2">{{ form.errors.image }}</div>
            </div>
          </div>

          <!-- Status Card -->
          <div class="bg-white rounded shadow-sm">
            <div class="px-4 py-3 border-b border-gray-200">
              <h3 class="font-semibold text-[#1f2d3d] text-sm">{{ t('common.status') }}</h3>
            </div>
            <div class="p-4">
              <label class="flex items-center cursor-pointer">
                <input
                  type="checkbox"
                  v-model="form.status"
                  class="w-4 h-4 text-[#007bff] border-gray-300 rounded focus:ring-[#007bff]"
                />
                <span class="ml-2 text-sm text-[#1f2d3d]">{{ t('common.active') }}</span>
              </label>
              <p class="text-xs text-[#6c757d] mt-2">{{ t('oils.activeHint') }}</p>
            </div>
          </div>

          <!-- Actions Card -->
          <div class="bg-white rounded shadow-sm">
            <div class="px-4 py-3 border-b border-gray-200 bg-[#ffc107] rounded-t">
              <h3 class="font-semibold text-[#1f2d3d] text-sm">{{ t('common.actions') }}</h3>
            </div>
            <div class="p-4 space-y-2">
              <button
                type="submit"
                :disabled="form.processing"
                class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#007bff] text-white text-sm font-medium rounded hover:bg-[#0069d9] transition disabled:opacity-50"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ form.processing ? t('common.saving') : t('common.update') }}
              </button>
              <Link
                href="/admin/oils"
                class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#6c757d] text-white text-sm font-medium rounded hover:bg-[#5a6268] transition"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                {{ t('common.back') }}
              </Link>
              <button
                type="button"
                @click="deleteOil"
                class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#dc3545] text-white text-sm font-medium rounded hover:bg-[#c82333] transition"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                {{ t('common.delete') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>
