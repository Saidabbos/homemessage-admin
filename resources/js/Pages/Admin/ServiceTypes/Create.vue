<script setup>
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import NumberInput from '@/Components/Form/NumberInput.vue';
import TextArea from '@/Components/Form/TextArea.vue';
import Checkbox from '@/Components/Form/Checkbox.vue';
import ImageUpload from '@/Components/Admin/ImageUpload.vue';
import Button from '@/Components/UI/Button.vue';

defineOptions({ layout: AdminLayout });

const form = useForm({
  slug: '',
  duration: 60,
  price: 0,
  image: null,
  status: true,
  en: { name: '', description: '' },
  uz: { name: '', description: '' },
  ru: { name: '', description: '' },
});

const generateSlug = () => {
  if (!form.slug && form.uz.name) {
    form.slug = form.uz.name
      .toLowerCase()
      .trim()
      .replace(/[^\w\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-');
  }
};

const submit = () => {
  form.post(route('admin.service-types.store'), {
    preserveScroll: true,
  });
};
</script>

<template>
  <div class="max-w-4xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">➕ Yangi Massage Turi</h1>

    <form @submit.prevent="submit" class="bg-white rounded-lg shadow-md p-8 space-y-6">
      <!-- Basic Information Section -->
      <div class="border-b pb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">📋 Asosiy Ma'lumot</h3>

        <div class="grid grid-cols-2 gap-6">
          <TextInput
            v-model="form.slug"
            label="Slug (URL uchun)"
            :error="form.errors.slug"
            placeholder="e.g., relaxation-massage"
            help="Faqat harf, raqam va tire(-) foydalaning"
          />

          <NumberInput
            v-model="form.duration"
            label="Davomiyligi (minutlar)"
            :error="form.errors.duration"
            :min="15"
            :max="480"
            help="Default: 60 minut. Min: 15, Max: 480"
          />
        </div>

        <div class="grid grid-cols-2 gap-6 mt-4">
          <NumberInput
            v-model="form.price"
            label="Narxi (so'm)"
            :error="form.errors.price"
            placeholder="0"
            step="0.01"
          />

          <ImageUpload
            v-model="form.image"
            label="Rasm"
            :error="form.errors.image"
            help="Maksimal o'lcham: 2MB. Formatlar: JPEG, PNG, GIF"
          />
        </div>

        <div class="mt-4">
          <Checkbox v-model="form.status" label="Faol qilish" />
        </div>
      </div>

      <!-- Translations Section -->
      <div>
        <h3 class="text-lg font-semibold text-gray-800 mb-4">🌍 Tarjimalar</h3>

        <!-- English -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4 rounded">
          <div class="text-sm font-semibold mb-4 flex items-center gap-2">
            <span>🇬🇧</span>
            <span>English</span>
          </div>
          <TextInput
            v-model="form.en.name"
            label="Nomi (English) *"
            :error="form.errors['en.name']"
            placeholder="e.g., Relaxation Massage"
          />
          <TextArea
            v-model="form.en.description"
            label="Tavsifi (English)"
            :error="form.errors['en.description']"
            class="mt-3"
            placeholder="Massage turining batafsil tavsifi..."
          />
        </div>

        <!-- Uzbek -->
        <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4 rounded">
          <div class="text-sm font-semibold mb-4 flex items-center gap-2">
            <span>🇺🇿</span>
            <span>O'zbek</span>
          </div>
          <TextInput
            v-model="form.uz.name"
            label="Nomi (O'zbek) *"
            :error="form.errors['uz.name']"
            placeholder="Masalan, Relaxation Massaji"
            @blur="generateSlug"
          />
          <TextArea
            v-model="form.uz.description"
            label="Tavsifi (O'zbek)"
            :error="form.errors['uz.description']"
            class="mt-3"
            placeholder="Massage turining batafsil tavsifi..."
          />
        </div>

        <!-- Russian -->
        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
          <div class="text-sm font-semibold mb-4 flex items-center gap-2">
            <span>🇷🇺</span>
            <span>Русский</span>
          </div>
          <TextInput
            v-model="form.ru.name"
            label="Nomi (Russian) *"
            :error="form.errors['ru.name']"
            placeholder="Например, Релаксирующий массаж"
          />
          <TextArea
            v-model="form.ru.description"
            label="Tavsifi (Russian)"
            :error="form.errors['ru.description']"
            class="mt-3"
            placeholder="Подробное описание типа массажа..."
          />
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex gap-4 pt-6 border-t">
        <Button type="submit" :loading="form.processing" variant="primary">
          💾 Saqlash
        </Button>

        <Button
          type="button"
          variant="secondary"
          @click="$inertia.visit(route('admin.service-types.index'))"
        >
          ❌ Bekor Qilish
        </Button>
      </div>
    </form>
  </div>
</template>
