<script setup>
import { useI18n } from 'vue-i18n';
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const form = useForm({
  name: '',
  email: '',
  phone: '',
  password: '',
  status: true,
});

const submit = () => {
  form.post(route('admin.dispatchers.store'));
};
</script>

<template>
  <div>
    <!-- Content Header -->
    <div class="mb-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-[#1f2d3d]">{{ t('dispatchers.new') }}</h1>
          <p class="text-sm text-[#6c757d] mt-1">{{ t('dispatchers.createSubtitle') }}</p>
        </div>
        <nav class="mt-2 sm:mt-0">
          <ol class="flex items-center text-sm">
            <li><Link href="/admin/dashboard" class="text-[#007bff]">{{ t('common.home') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li><Link href="/admin/dispatchers" class="text-[#007bff]">{{ t('dispatchers.title') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li class="text-[#6c757d]">{{ t('common.create') }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <form @submit.prevent="submit">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-4">
          <!-- Personal Info Card -->
          <div class="bg-white rounded shadow-sm">
            <div class="px-4 py-3 border-b border-gray-200 bg-[#6f42c1] rounded-t">
              <h3 class="font-semibold text-white">{{ t('dispatchers.personalInfo') }}</h3>
            </div>
            <div class="p-4 space-y-4">
              <!-- Name -->
              <div>
                <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                  {{ t('dispatchers.name') }} <span class="text-[#dc3545]">*</span>
                </label>
                <input
                  type="text"
                  v-model="form.name"
                  class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                  :placeholder="t('dispatchers.enterName')"
                />
                <div v-if="form.errors.name" class="text-[#dc3545] text-xs mt-1">{{ form.errors.name }}</div>
              </div>

              <!-- Email -->
              <div>
                <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                  {{ t('dispatchers.email') }} <span class="text-[#dc3545]">*</span>
                </label>
                <input
                  type="email"
                  v-model="form.email"
                  class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                  :placeholder="t('dispatchers.enterEmail')"
                />
                <div v-if="form.errors.email" class="text-[#dc3545] text-xs mt-1">{{ form.errors.email }}</div>
              </div>

              <!-- Phone -->
              <div>
                <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                  {{ t('dispatchers.phone') }}
                </label>
                <input
                  type="text"
                  v-model="form.phone"
                  class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                  placeholder="+998 90 123 45 67"
                />
                <div v-if="form.errors.phone" class="text-[#dc3545] text-xs mt-1">{{ form.errors.phone }}</div>
              </div>

              <!-- Password -->
              <div>
                <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                  {{ t('dispatchers.password') }} <span class="text-[#dc3545]">*</span>
                </label>
                <input
                  type="password"
                  v-model="form.password"
                  class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                  :placeholder="t('dispatchers.enterPassword')"
                />
                <div v-if="form.errors.password" class="text-[#dc3545] text-xs mt-1">{{ form.errors.password }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-4">
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
            <div class="px-4 py-3 border-b border-gray-200 bg-[#28a745] rounded-t">
              <h3 class="font-semibold text-white text-sm">{{ t('common.actions') }}</h3>
            </div>
            <div class="p-4 space-y-2">
              <button
                type="submit"
                :disabled="form.processing"
                class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#007bff] text-white text-sm font-medium rounded hover:bg-[#0069d9] transition disabled:opacity-50"
              >
                {{ form.processing ? t('common.saving') : t('common.save') }}
              </button>
              <Link
                href="/admin/dispatchers"
                class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#6c757d] text-white text-sm font-medium rounded hover:bg-[#5a6268] transition"
              >
                {{ t('common.cancel') }}
              </Link>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>
