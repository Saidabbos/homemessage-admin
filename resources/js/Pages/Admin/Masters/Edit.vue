<script setup>
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps({
  master: Object,
  serviceTypes: Array,
});

const activeTab = ref('uz');
const photoPreview = ref(props.master.photo_url);

const form = useForm({
  _method: 'PUT',
  first_name: props.master.first_name,
  last_name: props.master.last_name,
  phone: props.master.phone,
  email: props.master.email || '',
  photo: null,
  birth_date: props.master.birth_date || '',
  gender: props.master.gender,
  experience_years: props.master.experience_years,
  status: props.master.status,
  service_types: props.master.service_types || [],
  uz: { bio: props.master.uz?.bio || '' },
  ru: { bio: props.master.ru?.bio || '' },
  en: { bio: props.master.en?.bio || '' },
});

const handlePhotoChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.photo = file;
    photoPreview.value = URL.createObjectURL(file);
  }
};

const submit = () => {
  form.post(route('admin.masters.update', props.master.id));
};
</script>

<template>
  <div>
    <!-- Content Header -->
    <div class="mb-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-[#1f2d3d]">{{ t('masters.edit') }}</h1>
          <p class="text-sm text-[#6c757d] mt-1">{{ master.full_name }}</p>
        </div>
        <nav class="mt-2 sm:mt-0">
          <ol class="flex items-center text-sm">
            <li><Link href="/admin/dashboard" class="text-[#007bff]">{{ t('common.home') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li><Link href="/admin/masters" class="text-[#007bff]">{{ t('masters.title') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li class="text-[#6c757d]">{{ t('common.edit') }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <form @submit.prevent="submit">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Main Info Card -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded shadow-sm">
            <div class="px-4 py-3 border-b border-gray-200 bg-[#ffc107]">
              <h3 class="font-semibold text-[#1f2d3d]">{{ t('masters.personalInfo') }}</h3>
            </div>
            <div class="p-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- First Name -->
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                    {{ t('masters.firstName') }} <span class="text-[#dc3545]">*</span>
                  </label>
                  <input
                    v-model="form.first_name"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#007bff] focus:border-transparent"
                  />
                  <p v-if="form.errors.first_name" class="mt-1 text-sm text-[#dc3545]">{{ form.errors.first_name }}</p>
                </div>

                <!-- Last Name -->
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                    {{ t('masters.lastName') }} <span class="text-[#dc3545]">*</span>
                  </label>
                  <input
                    v-model="form.last_name"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#007bff] focus:border-transparent"
                  />
                  <p v-if="form.errors.last_name" class="mt-1 text-sm text-[#dc3545]">{{ form.errors.last_name }}</p>
                </div>

                <!-- Phone -->
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                    {{ t('masters.phone') }} <span class="text-[#dc3545]">*</span>
                  </label>
                  <input
                    v-model="form.phone"
                    type="tel"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#007bff] focus:border-transparent"
                  />
                  <p v-if="form.errors.phone" class="mt-1 text-sm text-[#dc3545]">{{ form.errors.phone }}</p>
                </div>

                <!-- Email -->
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                    {{ t('masters.email') }}
                  </label>
                  <input
                    v-model="form.email"
                    type="email"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#007bff] focus:border-transparent"
                  />
                  <p v-if="form.errors.email" class="mt-1 text-sm text-[#dc3545]">{{ form.errors.email }}</p>
                </div>

                <!-- Birth Date -->
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                    {{ t('masters.birthDate') }}
                  </label>
                  <input
                    v-model="form.birth_date"
                    type="date"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#007bff] focus:border-transparent"
                  />
                </div>

                <!-- Gender -->
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                    {{ t('masters.gender') }} <span class="text-[#dc3545]">*</span>
                  </label>
                  <select
                    v-model="form.gender"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#007bff] focus:border-transparent"
                  >
                    <option value="female">{{ t('masters.female') }}</option>
                    <option value="male">{{ t('masters.male') }}</option>
                  </select>
                </div>

                <!-- Experience Years -->
                <div>
                  <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
                    {{ t('masters.experience') }} <span class="text-[#dc3545]">*</span>
                  </label>
                  <input
                    v-model="form.experience_years"
                    type="number"
                    min="0"
                    max="50"
                    class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#007bff] focus:border-transparent"
                  />
                </div>

                <!-- Status -->
                <div class="flex items-center">
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input v-model="form.status" type="checkbox" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#007bff] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#28a745]"></div>
                    <span class="ml-3 text-sm font-medium text-[#1f2d3d]">{{ t('common.active') }}</span>
                  </label>
                </div>
              </div>

              <!-- Service Types -->
              <div class="mt-4">
                <label class="block text-sm font-medium text-[#1f2d3d] mb-2">
                  {{ t('masters.services') }}
                </label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                  <label
                    v-for="serviceType in serviceTypes"
                    :key="serviceType.id"
                    class="flex items-center p-2 border border-gray-200 rounded hover:bg-gray-50 cursor-pointer"
                  >
                    <input
                      v-model="form.service_types"
                      type="checkbox"
                      :value="serviceType.id"
                      class="w-4 h-4 text-[#007bff] border-gray-300 rounded focus:ring-[#007bff]"
                    />
                    <span class="ml-2 text-sm text-[#1f2d3d]">{{ serviceType.name?.uz || serviceType.name }}</span>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Bio Translations -->
          <div class="bg-white rounded shadow-sm mt-4">
            <div class="px-4 py-3 border-b border-gray-200">
              <h3 class="font-semibold text-[#1f2d3d]">{{ t('masters.bio') }}</h3>
            </div>
            <div class="p-4">
              <!-- Language Tabs -->
              <div class="flex border-b border-gray-200 mb-4">
                <button
                  type="button"
                  @click="activeTab = 'uz'"
                  :class="['px-4 py-2 text-sm font-medium border-b-2 -mb-px', activeTab === 'uz' ? 'border-[#007bff] text-[#007bff]' : 'border-transparent text-[#6c757d] hover:text-[#1f2d3d]']"
                >
                  O'zbek
                </button>
                <button
                  type="button"
                  @click="activeTab = 'ru'"
                  :class="['px-4 py-2 text-sm font-medium border-b-2 -mb-px', activeTab === 'ru' ? 'border-[#007bff] text-[#007bff]' : 'border-transparent text-[#6c757d] hover:text-[#1f2d3d]']"
                >
                  Русский
                </button>
                <button
                  type="button"
                  @click="activeTab = 'en'"
                  :class="['px-4 py-2 text-sm font-medium border-b-2 -mb-px', activeTab === 'en' ? 'border-[#007bff] text-[#007bff]' : 'border-transparent text-[#6c757d] hover:text-[#1f2d3d]']"
                >
                  English
                </button>
              </div>

              <div v-show="activeTab === 'uz'">
                <textarea
                  v-model="form.uz.bio"
                  rows="4"
                  class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#007bff] focus:border-transparent"
                  :placeholder="t('masters.enterBio')"
                ></textarea>
              </div>
              <div v-show="activeTab === 'ru'">
                <textarea
                  v-model="form.ru.bio"
                  rows="4"
                  class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#007bff] focus:border-transparent"
                  :placeholder="t('masters.enterBio')"
                ></textarea>
              </div>
              <div v-show="activeTab === 'en'">
                <textarea
                  v-model="form.en.bio"
                  rows="4"
                  class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-[#007bff] focus:border-transparent"
                  :placeholder="t('masters.enterBio')"
                ></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- Photo & Stats Card -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded shadow-sm">
            <div class="px-4 py-3 border-b border-gray-200">
              <h3 class="font-semibold text-[#1f2d3d]">{{ t('masters.photo') }}</h3>
            </div>
            <div class="p-4">
              <div class="flex flex-col items-center">
                <div class="w-32 h-32 rounded-full overflow-hidden bg-gray-100 border-4 border-gray-200 mb-4">
                  <img :src="photoPreview" class="w-full h-full object-cover" />
                </div>
                <label class="cursor-pointer">
                  <span class="px-4 py-2 bg-[#6c757d] text-white text-sm font-medium rounded hover:bg-[#5a6268] transition">
                    {{ t('masters.changePhoto') }}
                  </span>
                  <input
                    type="file"
                    accept="image/*"
                    class="hidden"
                    @change="handlePhotoChange"
                  />
                </label>
              </div>
            </div>
          </div>

          <!-- Stats -->
          <div class="bg-white rounded shadow-sm mt-4">
            <div class="px-4 py-3 border-b border-gray-200">
              <h3 class="font-semibold text-[#1f2d3d]">{{ t('common.statistics') }}</h3>
            </div>
            <div class="p-4 space-y-3">
              <div class="flex justify-between items-center">
                <span class="text-[#6c757d]">{{ t('masters.rating') }}:</span>
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-[#ffc107] mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                  </svg>
                  <span class="font-semibold text-[#1f2d3d]">{{ master.rating }}</span>
                </div>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-[#6c757d]">{{ t('masters.totalOrders') }}:</span>
                <span class="font-semibold text-[#1f2d3d]">{{ master.total_orders }}</span>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="bg-white rounded shadow-sm mt-4 p-4">
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full px-4 py-2 bg-[#ffc107] text-[#1f2d3d] font-medium rounded hover:bg-[#e0a800] transition disabled:opacity-50"
            >
              <span v-if="form.processing">{{ t('common.saving') }}</span>
              <span v-else>{{ t('common.update') }}</span>
            </button>
            <Link
              href="/admin/masters"
              class="block w-full mt-2 px-4 py-2 bg-[#6c757d] text-white font-medium rounded hover:bg-[#5a6268] transition text-center"
            >
              {{ t('common.cancel') }}
            </Link>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>
