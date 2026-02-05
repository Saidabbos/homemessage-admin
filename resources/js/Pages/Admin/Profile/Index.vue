<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps({
  user: Object,
});

const activeTab = ref('profile');

const profileForm = useForm({
  name: props.user.name,
  email: props.user.email,
  phone: props.user.phone || '',
});

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const submitProfile = () => {
  profileForm.put(route('admin.profile.update'));
};

const submitPassword = () => {
  passwordForm.put(route('admin.profile.password'), {
    onSuccess: () => {
      passwordForm.reset();
    },
  });
};

const tabs = [
  { id: 'profile', label: 'profile.info' },
  { id: 'password', label: 'profile.security' },
];
</script>

<template>
  <div>
    <!-- Content Header -->
    <div class="mb-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-[#1f2d3d]">{{ t('profile.title') }}</h1>
          <p class="text-sm text-[#6c757d] mt-1">{{ t('profile.subtitle') }}</p>
        </div>
        <nav class="mt-2 sm:mt-0">
          <ol class="flex items-center text-sm">
            <li><Link href="/admin/dashboard" class="text-[#007bff]">{{ t('common.home') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li class="text-[#6c757d]">{{ t('profile.title') }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <!-- User Card -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded shadow-sm">
          <div class="px-4 py-3 border-b border-gray-200 bg-[#17a2b8] rounded-t">
            <h3 class="font-semibold text-white">{{ t('profile.about') }}</h3>
          </div>
          <div class="p-4 text-center">
            <!-- Avatar -->
            <div class="w-24 h-24 mx-auto rounded-full bg-[#007bff] flex items-center justify-center text-white text-3xl font-bold mb-4">
              {{ user.name?.charAt(0).toUpperCase() || 'A' }}
            </div>
            <h4 class="text-lg font-semibold text-[#1f2d3d]">{{ user.name }}</h4>
            <p class="text-sm text-[#6c757d] mt-1">{{ user.email }}</p>
            <div v-if="user.phone" class="text-sm text-[#6c757d] mt-1">{{ user.phone }}</div>

            <hr class="my-4" />

            <div class="text-left space-y-2 text-sm">
              <div class="flex items-center text-[#6c757d]">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span>{{ t('profile.role') }}: <strong class="text-[#1f2d3d]">Admin</strong></span>
              </div>
              <div class="flex items-center text-[#6c757d]">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>{{ t('profile.memberSince') }}: <strong class="text-[#1f2d3d]">{{ new Date(user.created_at).toLocaleDateString() }}</strong></span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Forms -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded shadow-sm">
          <!-- Tabs -->
          <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
              <button
                v-for="tab in tabs"
                :key="tab.id"
                type="button"
                @click="activeTab = tab.id"
                :class="[
                  'px-6 py-3 text-sm font-medium border-b-2 transition-colors',
                  activeTab === tab.id
                    ? 'border-[#007bff] text-[#007bff]'
                    : 'border-transparent text-[#6c757d] hover:text-[#1f2d3d] hover:border-gray-300'
                ]"
              >
                {{ t(tab.label) }}
              </button>
            </nav>
          </div>

          <!-- Profile Form -->
          <form v-show="activeTab === 'profile'" @submit.prevent="submitProfile" class="p-6 space-y-4">
            <!-- Name -->
            <div>
              <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                {{ t('profile.name') }} <span class="text-[#dc3545]">*</span>
              </label>
              <input
                type="text"
                v-model="profileForm.name"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                :placeholder="t('profile.enterName')"
              />
              <div v-if="profileForm.errors.name" class="text-[#dc3545] text-xs mt-1">{{ profileForm.errors.name }}</div>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                {{ t('profile.email') }} <span class="text-[#dc3545]">*</span>
              </label>
              <input
                type="email"
                v-model="profileForm.email"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                :placeholder="t('profile.enterEmail')"
              />
              <div v-if="profileForm.errors.email" class="text-[#dc3545] text-xs mt-1">{{ profileForm.errors.email }}</div>
            </div>

            <!-- Phone -->
            <div>
              <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                {{ t('profile.phone') }}
              </label>
              <input
                type="text"
                v-model="profileForm.phone"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                placeholder="+998 90 123 45 67"
              />
              <div v-if="profileForm.errors.phone" class="text-[#dc3545] text-xs mt-1">{{ profileForm.errors.phone }}</div>
            </div>

            <!-- Submit -->
            <div class="pt-4">
              <button
                type="submit"
                :disabled="profileForm.processing"
                class="inline-flex items-center px-4 py-2 bg-[#007bff] text-white text-sm font-medium rounded hover:bg-[#0069d9] transition disabled:opacity-50"
              >
                <svg v-if="profileForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ profileForm.processing ? t('common.saving') : t('profile.updateProfile') }}
              </button>
            </div>
          </form>

          <!-- Password Form -->
          <form v-show="activeTab === 'password'" @submit.prevent="submitPassword" class="p-6 space-y-4">
            <!-- Current Password -->
            <div>
              <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                {{ t('profile.currentPassword') }} <span class="text-[#dc3545]">*</span>
              </label>
              <input
                type="password"
                v-model="passwordForm.current_password"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                :placeholder="t('profile.enterCurrentPassword')"
              />
              <div v-if="passwordForm.errors.current_password" class="text-[#dc3545] text-xs mt-1">{{ passwordForm.errors.current_password }}</div>
            </div>

            <!-- New Password -->
            <div>
              <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                {{ t('profile.newPassword') }} <span class="text-[#dc3545]">*</span>
              </label>
              <input
                type="password"
                v-model="passwordForm.password"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                :placeholder="t('profile.enterNewPassword')"
              />
              <p class="text-xs text-[#6c757d] mt-1">{{ t('profile.passwordHint') }}</p>
              <div v-if="passwordForm.errors.password" class="text-[#dc3545] text-xs mt-1">{{ passwordForm.errors.password }}</div>
            </div>

            <!-- Confirm Password -->
            <div>
              <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                {{ t('profile.confirmPassword') }} <span class="text-[#dc3545]">*</span>
              </label>
              <input
                type="password"
                v-model="passwordForm.password_confirmation"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:ring-[#007bff] focus:border-[#007bff] text-sm"
                :placeholder="t('profile.enterConfirmPassword')"
              />
            </div>

            <!-- Submit -->
            <div class="pt-4">
              <button
                type="submit"
                :disabled="passwordForm.processing"
                class="inline-flex items-center px-4 py-2 bg-[#ffc107] text-[#1f2d3d] text-sm font-medium rounded hover:bg-[#e0a800] transition disabled:opacity-50"
              >
                <svg v-if="passwordForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ passwordForm.processing ? t('common.saving') : t('profile.changePassword') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
