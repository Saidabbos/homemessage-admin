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
  master: {
    id: number;
    first_name: string;
    last_name: string;
    full_name: string;
    phone: string;
    email: string;
    photo_url: string;
    birth_date: string;
    gender: string;
    experience_years: number;
    status: boolean;
    service_types: number[];
    oils: number[];
    pressure_levels: number[];
    telegram_id?: string;
    telegram_username?: string;
    notify_telegram: boolean;
    notify_sms: boolean;
    uz?: { bio: string };
    ru?: { bio: string };
    en?: { bio: string };
  };
  serviceTypes: Array<{ id: number; name: { uz?: string } | string }>;
  oils: Array<{ id: number; name: { uz?: string } | string }>;
  pressureLevels: Array<{ id: number; name: { uz?: string } | string }>;
}>();

const activeTab = ref('uz');
const photoPreview = ref<string | null>(props.master.photo_url);

const languageTabs = [
  { key: 'uz', label: "O'zbek", flag: '🇺🇿' },
  { key: 'ru', label: 'Русский', flag: '🇷🇺' },
  { key: 'en', label: 'English', flag: '🇬🇧' },
];

const form = useForm({
  _method: 'PUT',
  first_name: props.master.first_name,
  last_name: props.master.last_name,
  phone: props.master.phone,
  email: props.master.email || '',
  password: '',
  photo: null as File | null,
  birth_date: props.master.birth_date || '',
  gender: props.master.gender,
  experience_years: props.master.experience_years,
  status: props.master.status,
  notify_telegram: props.master.notify_telegram ?? true,
  notify_sms: props.master.notify_sms ?? true,
  service_types: props.master.service_types || [],
  oils: props.master.oils || [],
  pressure_levels: props.master.pressure_levels || [],
  uz: { bio: props.master.uz?.bio || '' },
  ru: { bio: props.master.ru?.bio || '' },
  en: { bio: props.master.en?.bio || '' },
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
  if (idx > -1) arr.splice(idx, 1);
  else arr.push(id);
};

const submit = () => {
  form.patch(route('admin.masters.update', props.master.id));
};

const getName = (item: { name: { uz?: string } | string }) => {
  return typeof item.name === 'object' ? item.name?.uz : item.name;
};
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-bold tracking-tight">{{ t('masters.edit', 'Masterni tahrirlash') }}</h1>
      <p class="text-muted-foreground">{{ master.full_name }}</p>
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
                  <Label>{{ t('masters.firstName', 'Ism') }} <span class="text-destructive">*</span></Label>
                  <Input v-model="form.first_name" />
                  <p v-if="form.errors.first_name" class="text-destructive text-xs">{{ form.errors.first_name }}</p>
                </div>

                <div class="space-y-2">
                  <Label>{{ t('masters.lastName', 'Familiya') }} <span class="text-destructive">*</span></Label>
                  <Input v-model="form.last_name" />
                  <p v-if="form.errors.last_name" class="text-destructive text-xs">{{ form.errors.last_name }}</p>
                </div>

                <div class="space-y-2">
                  <Label>{{ t('masters.phone', 'Telefon') }} <span class="text-destructive">*</span></Label>
                  <Input v-model="form.phone" />
                  <p v-if="form.errors.phone" class="text-destructive text-xs">{{ form.errors.phone }}</p>
                </div>

                <div class="space-y-2">
                  <Label>{{ t('masters.email', 'Email') }} <span class="text-destructive">*</span></Label>
                  <Input type="email" v-model="form.email" />
                  <p v-if="form.errors.email" class="text-destructive text-xs">{{ form.errors.email }}</p>
                </div>

                <div class="space-y-2">
                  <Label>{{ t('masters.password', 'Yangi parol') }}</Label>
                  <Input type="password" v-model="form.password" :placeholder="t('masters.newPassword', 'Bo\'sh qoldiring...')" />
                  <p class="text-xs text-muted-foreground">{{ t('masters.passwordChangeHint', 'O\'zgartirmaslik uchun bo\'sh qoldiring') }}</p>
                  <p v-if="form.errors.password" class="text-destructive text-xs">{{ form.errors.password }}</p>
                </div>

                <div class="space-y-2">
                  <Label>{{ t('masters.birthDate', 'Tug\'ilgan sana') }}</Label>
                  <Input type="date" v-model="form.birth_date" />
                </div>

                <div class="space-y-2">
                  <Label>{{ t('masters.gender', 'Jinsi') }} <span class="text-destructive">*</span></Label>
                  <Select v-model="form.gender">
                    <SelectTrigger><SelectValue /></SelectTrigger>
                    <SelectContent>
                      <SelectItem value="female">{{ t('masters.female', 'Ayol') }}</SelectItem>
                      <SelectItem value="male">{{ t('masters.male', 'Erkak') }}</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <div class="space-y-2">
                  <Label>{{ t('masters.experience', 'Tajriba (yil)') }} <span class="text-destructive">*</span></Label>
                  <Input type="number" v-model="form.experience_years" min="0" max="50" />
                </div>
              </div>

              <div class="flex items-center justify-between rounded-lg border p-4">
                <Label>{{ t('common.active', 'Faol') }}</Label>
                <Switch v-model:checked="form.status" />
              </div>

              <!-- Telegram Connection Status -->
              <div class="rounded-lg border p-4 space-y-4">
                <div class="flex items-center justify-between">
                  <div>
                    <Label class="text-base font-medium">📲 Telegram</Label>
                    <p class="text-sm text-muted-foreground">
                      {{ master.telegram_id ? (master.telegram_username ? '@' + master.telegram_username : 'Ulangan') : 'Ulanmagan' }}
                    </p>
                  </div>
                  <span v-if="master.telegram_id" class="text-green-600 text-xl">✅</span>
                  <span v-else class="text-muted-foreground text-xl">❌</span>
                </div>
                
                <div v-if="!master.telegram_id" class="text-sm text-muted-foreground bg-muted/50 p-3 rounded">
                  Master @h_m_UZ_bot ga /start yuborishi va telefon raqamini ulashi kerak.
                </div>

                <div class="flex items-center justify-between pt-2 border-t">
                  <div>
                    <Label>Telegram xabarlari</Label>
                    <p class="text-xs text-muted-foreground">Buyurtmalar haqida Telegram DM</p>
                  </div>
                  <Switch v-model:checked="form.notify_telegram" :disabled="!master.telegram_id" />
                </div>

                <div class="flex items-center justify-between">
                  <div>
                    <Label>SMS xabarlari</Label>
                    <p class="text-xs text-muted-foreground">Buyurtmalar haqida SMS</p>
                  </div>
                  <Switch v-model:checked="form.notify_sms" />
                </div>
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
                <Textarea v-model="form.uz.bio" rows="4" />
              </div>
              <div v-show="activeTab === 'ru'">
                <Textarea v-model="form.ru.bio" rows="4" />
              </div>
              <div v-show="activeTab === 'en'">
                <Textarea v-model="form.en.bio" rows="4" />
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
                <AvatarFallback class="text-4xl">{{ master.first_name?.charAt(0) }}</AvatarFallback>
              </Avatar>

              <label class="cursor-pointer">
                <Button type="button" variant="secondary" as="span">
                  {{ t('masters.changePhoto', 'Rasmni o\'zgartirish') }}
                </Button>
                <input type="file" accept="image/*" class="hidden" @change="handlePhotoChange" />
              </label>
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
                {{ form.processing ? t('common.saving', 'Saqlanmoqda...') : t('common.update', 'Yangilash') }}
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
