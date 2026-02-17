<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Checkbox } from '@/components/ui/checkbox';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps<{
  serviceTypes: Array<{ id: number; name: { uz?: string } | string }>;
  oils: Array<{ id: number; name: { uz?: string } | string }>;
  pressureLevels: Array<{ id: number; name: { uz?: string } | string }>;
}>();

const activeTab = ref('uz');
const photoPreview = ref<string | null>(null);

const form = useForm({
  first_name: '',
  last_name: '',
  phone: '',
  email: '',
  password: '',
  photo: null as File | null,
  birth_date: '',
  gender: 'female',
  experience_years: 0,
  status: true,
  service_types: [] as number[],
  oils: [] as number[],
  pressure_levels: [] as number[],
  uz: { bio: '' },
  ru: { bio: '' },
  en: { bio: '' },
});

const handlePhotoChange = (e: Event) => {
  const target = e.target as HTMLInputElement;
  const file = target.files?.[0];
  if (file) {
    form.photo = file;
    photoPreview.value = URL.createObjectURL(file);
  }
};

const toggleArrayItem = (arr: number[], id: number) => {
  const idx = arr.indexOf(id);
  if (idx > -1) {
    arr.splice(idx, 1);
  } else {
    arr.push(id);
  }
};

const submit = () => {
  form.post(route('admin.masters.store'));
};

const getName = (item: { name: { uz?: string } | string }) => {
  return typeof item.name === 'object' ? item.name?.uz : item.name;
};

const languageTabs = [
  { key: 'uz', label: "O'zbek", flag: '🇺🇿' },
  { key: 'ru', label: 'Русский', flag: '🇷🇺' },
  { key: 'en', label: 'English', flag: '🇬🇧' },
];
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-bold tracking-tight">{{ t('masters.new', 'Yangi master') }}</h1>
      <p class="text-muted-foreground">{{ t('masters.newSubtitle', 'Master ma\'lumotlarini kiriting') }}</p>
    </div>

    <form @submit.prevent="submit">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
          <Card>
            <CardHeader>
              <CardTitle>{{ t('masters.personalInfo', 'Shaxsiy ma\'lumotlar') }}</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label for="first_name">{{ t('masters.firstName', 'Ism') }} <span class="text-destructive">*</span></Label>
                  <Input id="first_name" v-model="form.first_name" :placeholder="t('masters.enterFirstName', 'Ismini kiriting')" />
                  <p v-if="form.errors.first_name" class="text-destructive text-xs">{{ form.errors.first_name }}</p>
                </div>

                <div class="space-y-2">
                  <Label for="last_name">{{ t('masters.lastName', 'Familiya') }} <span class="text-destructive">*</span></Label>
                  <Input id="last_name" v-model="form.last_name" :placeholder="t('masters.enterLastName', 'Familiyasini kiriting')" />
                  <p v-if="form.errors.last_name" class="text-destructive text-xs">{{ form.errors.last_name }}</p>
                </div>

                <div class="space-y-2">
                  <Label for="phone">{{ t('masters.phone', 'Telefon') }} <span class="text-destructive">*</span></Label>
                  <Input id="phone" v-model="form.phone" placeholder="+998 90 123 45 67" />
                  <p v-if="form.errors.phone" class="text-destructive text-xs">{{ form.errors.phone }}</p>
                </div>

                <div class="space-y-2">
                  <Label for="email">{{ t('masters.email', 'Email') }} <span class="text-destructive">*</span></Label>
                  <Input id="email" type="email" v-model="form.email" placeholder="email@example.com" />
                  <p v-if="form.errors.email" class="text-destructive text-xs">{{ form.errors.email }}</p>
                </div>

                <div class="space-y-2">
                  <Label for="password">{{ t('masters.password', 'Parol') }} <span class="text-destructive">*</span></Label>
                  <Input id="password" type="password" v-model="form.password" :placeholder="t('masters.enterPassword', 'Parolni kiriting')" />
                  <p class="text-xs text-muted-foreground">{{ t('masters.passwordHint', 'Kamida 8 belgi') }}</p>
                  <p v-if="form.errors.password" class="text-destructive text-xs">{{ form.errors.password }}</p>
                </div>

                <div class="space-y-2">
                  <Label for="birth_date">{{ t('masters.birthDate', 'Tug\'ilgan sana') }}</Label>
                  <Input id="birth_date" type="date" v-model="form.birth_date" />
                  <p v-if="form.errors.birth_date" class="text-destructive text-xs">{{ form.errors.birth_date }}</p>
                </div>

                <div class="space-y-2">
                  <Label>{{ t('masters.gender', 'Jinsi') }} <span class="text-destructive">*</span></Label>
                  <Select v-model="form.gender">
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="female">{{ t('masters.female', 'Ayol') }}</SelectItem>
                      <SelectItem value="male">{{ t('masters.male', 'Erkak') }}</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <div class="space-y-2">
                  <Label for="experience">{{ t('masters.experience', 'Tajriba (yil)') }} <span class="text-destructive">*</span></Label>
                  <Input id="experience" type="number" v-model="form.experience_years" min="0" max="50" />
                  <p v-if="form.errors.experience_years" class="text-destructive text-xs">{{ form.errors.experience_years }}</p>
                </div>
              </div>

              <div class="flex items-center justify-between rounded-lg border p-4">
                <div class="space-y-0.5">
                  <Label>{{ t('common.active', 'Faol') }}</Label>
                  <p class="text-xs text-muted-foreground">Master faol holatda</p>
                </div>
                <Switch v-model:checked="form.status" />
              </div>

              <!-- Service Types -->
              <div class="space-y-3">
                <Label>{{ t('masters.services', 'Xizmatlar') }}</Label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                  <div
                    v-for="st in serviceTypes"
                    :key="st.id"
                    class="flex items-center space-x-2 rounded-lg border p-3 cursor-pointer hover:bg-muted/50"
                    @click="toggleArrayItem(form.service_types, st.id)"
                  >
                    <Checkbox :checked="form.service_types.includes(st.id)" />
                    <span class="text-sm">{{ getName(st) }}</span>
                  </div>
                </div>
              </div>

              <!-- Oils -->
              <div class="space-y-3">
                <Label>{{ t('masters.oils', 'Yog\'lar') }}</Label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                  <div
                    v-for="oil in oils"
                    :key="oil.id"
                    class="flex items-center space-x-2 rounded-lg border p-3 cursor-pointer hover:bg-muted/50"
                    @click="toggleArrayItem(form.oils, oil.id)"
                  >
                    <Checkbox :checked="form.oils.includes(oil.id)" />
                    <span class="text-sm">{{ getName(oil) }}</span>
                  </div>
                </div>
              </div>

              <!-- Pressure Levels -->
              <div class="space-y-3">
                <Label>{{ t('masters.pressureLevels', 'Kuch darajasi') }}</Label>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                  <div
                    v-for="pl in pressureLevels"
                    :key="pl.id"
                    class="flex items-center space-x-2 rounded-lg border p-3 cursor-pointer hover:bg-muted/50"
                    @click="toggleArrayItem(form.pressure_levels, pl.id)"
                  >
                    <Checkbox :checked="form.pressure_levels.includes(pl.id)" />
                    <span class="text-sm">{{ getName(pl) }}</span>
                  </div>
                </div>
              </div>
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
                  {{ tab.flag }} {{ tab.label }}
                </Button>
              </div>

              <div v-show="activeTab === 'uz'">
                <Textarea v-model="form.uz.bio" rows="4" :placeholder="t('masters.enterBio', 'Biografiya...')" />
              </div>
              <div v-show="activeTab === 'ru'">
                <Textarea v-model="form.ru.bio" rows="4" :placeholder="t('masters.enterBio', 'Biografiya...')" />
              </div>
              <div v-show="activeTab === 'en'">
                <Textarea v-model="form.en.bio" rows="4" :placeholder="t('masters.enterBio', 'Biografiya...')" />
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Photo -->
          <Card>
            <CardHeader>
              <CardTitle>{{ t('masters.photo', 'Rasm') }}</CardTitle>
            </CardHeader>
            <CardContent class="flex flex-col items-center space-y-4">
              <Avatar class="w-32 h-32">
                <AvatarImage v-if="photoPreview" :src="photoPreview" />
                <AvatarFallback class="text-4xl">
                  <svg class="w-12 h-12 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                </AvatarFallback>
              </Avatar>

              <label class="cursor-pointer">
                <Button type="button" variant="secondary" as="span">
                  {{ t('masters.selectPhoto', 'Rasm tanlash') }}
                </Button>
                <input type="file" accept="image/*" class="hidden" @change="handlePhotoChange" />
              </label>
              <p class="text-xs text-muted-foreground">JPG, PNG. Max 2MB</p>
              <p v-if="form.errors.photo" class="text-destructive text-xs">{{ form.errors.photo }}</p>
            </CardContent>
          </Card>

          <!-- Actions -->
          <Card>
            <CardContent class="pt-6 space-y-2">
              <Button type="submit" class="w-full" :disabled="form.processing">
                <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ form.processing ? t('common.saving', 'Saqlanmoqda...') : t('common.save', 'Saqlash') }}
              </Button>
              <Button type="button" variant="outline" class="w-full" as-child>
                <Link href="/admin/masters">{{ t('common.cancel', 'Bekor qilish') }}</Link>
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    </form>
  </div>
</template>
