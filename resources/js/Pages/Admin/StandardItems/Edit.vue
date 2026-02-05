<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps({
  item: Object,
});

const activeTab = ref('uz');

const form = useForm({
  _method: 'PUT',
  slug: props.item.slug,
  icon: props.item.icon || '',
  sort_order: props.item.sort_order,
  status: props.item.status,
  uz: { name: props.item.uz?.name || '', description: props.item.uz?.description || '' },
  ru: { name: props.item.ru?.name || '', description: props.item.ru?.description || '' },
  en: { name: props.item.en?.name || '', description: props.item.en?.description || '' },
});

const submit = () => {
  form.post(route('admin.standard-items.update', props.item.id));
};

const deleteItem = () => {
  if (confirm(t('standardItems.confirmDelete'))) {
    router.delete(route('admin.standard-items.destroy', props.item.id));
  }
};
</script>

<template>
  <div>
    <!-- Content Header -->
    <div class="mb-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-[#1f2d3d]">{{ t('standardItems.edit') }}</h1>
          <p class="text-sm text-[#6c757d] mt-1">{{ item.uz?.name || item.slug }}</p>
        </div>
        <nav class="mt-2 sm:mt-0">
          <ol class="flex items-center text-sm">
            <li><Link href="/admin/dashboard" class="text-[#007bff]">{{ t('common.home') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li><Link href="/admin/standard-items" class="text-[#007bff]">{{ t('standardItems.title') }}</Link></li>
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
          <!-- Translations Card -->
          <div class="bg-white rounded shadow-sm">
            <div class="px-4 py-3 border-b border-gray-200 bg-[#ffc107] rounded-t">
              <h3 class="font-semibold text-[#1f2d3d]">{{ t('translations.title') }}</h3>
            </div>

            <!-- Tab Navigation -->
            <div class="border-b border-gray-200">
              <ul class="flex flex-wrap -mb-px">
                <li v-for="tab in [{ key: 'uz', label: 'O\'zbek' }, { key: 'ru', label: 'Русский' }, { key: 'en', label: 'English' }]" :key="tab.key">
                  <button
                    type="button"
                    @click="activeTab = tab.key"
                    :class="['inline-flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 transition', activeTab === tab.key ? 'border-[#007bff] text-[#007bff]' : 'border-transparent text-[#6c757d] hover:text-[#1f2d3d]']"
                  >
                    {{ tab.label }}
                  </button>
                </li>
              </ul>
            </div>

            <div class="p-4">
              <div v-for="lang in ['uz', 'ru', 'en']" :key="lang" v-show="activeTab === lang" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                    {{ t('translations.name') }} <span class="text-[#dc3545]">*</span>
                  </label>
                  <input
                    type="text"
                    v-model="form[lang].name"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                    :placeholder="t('standardItems.enterName')"
                  />
                  <div v-if="form.errors[`${lang}.name`]" class="text-[#dc3545] text-xs mt-1">{{ form.errors[`${lang}.name`] }}</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">{{ t('translations.description') }}</label>
                  <textarea
                    v-model="form[lang].description"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                    :placeholder="t('standardItems.enterDescription')"
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
              <input
                type="text"
                v-model="form.slug"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm font-mono"
                placeholder="item-slug"
              />
              <div v-if="form.errors.slug" class="text-[#dc3545] text-xs mt-1">{{ form.errors.slug }}</div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-4">
          <!-- Icon Card -->
          <div class="bg-white rounded shadow-sm">
            <div class="px-4 py-3 border-b border-gray-200">
              <h3 class="font-semibold text-[#1f2d3d] text-sm">{{ t('standardItems.icon') }}</h3>
            </div>
            <div class="p-4">
              <input
                type="text"
                v-model="form.icon"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm text-center text-2xl"
                placeholder="🧺"
              />
            </div>
          </div>

          <!-- Sort Order Card -->
          <div class="bg-white rounded shadow-sm">
            <div class="px-4 py-3 border-b border-gray-200">
              <h3 class="font-semibold text-[#1f2d3d] text-sm">{{ t('standardItems.sortOrder') }}</h3>
            </div>
            <div class="p-4">
              <input
                type="number"
                v-model="form.sort_order"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
              />
            </div>
          </div>

          <!-- Status Card -->
          <div class="bg-white rounded shadow-sm">
            <div class="px-4 py-3 border-b border-gray-200">
              <h3 class="font-semibold text-[#1f2d3d] text-sm">{{ t('common.status') }}</h3>
            </div>
            <div class="p-4">
              <label class="flex items-center cursor-pointer">
                <input type="checkbox" v-model="form.status" class="w-4 h-4 text-[#007bff] border-gray-300 rounded focus:ring-[#007bff]" />
                <span class="ml-2 text-sm text-[#1f2d3d]">{{ t('common.active') }}</span>
              </label>
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
                {{ form.processing ? t('common.saving') : t('common.update') }}
              </button>
              <Link
                href="/admin/standard-items"
                class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#6c757d] text-white text-sm font-medium rounded hover:bg-[#5a6268] transition"
              >
                {{ t('common.back') }}
              </Link>
              <button
                type="button"
                @click="deleteItem"
                class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#dc3545] text-white text-sm font-medium rounded hover:bg-[#c82333] transition"
              >
                {{ t('common.delete') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>
