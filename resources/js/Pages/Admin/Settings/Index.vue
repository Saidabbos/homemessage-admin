<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Alert, AlertDescription } from '@/components/ui/alert';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps<{
  settings: {
    general?: {
      app_name?: string;
      company_phone?: string;
      company_email?: string;
      company_address?: string;
      working_hours_start?: string;
      working_hours_end?: string;
    };
    booking?: {
      min_booking_hours?: number;
      max_booking_days?: number;
      cancellation_hours?: number;
      auto_confirm_booking?: boolean;
    };
    social?: {
      telegram_link?: string;
      instagram_link?: string;
      facebook_link?: string;
    };
    hero?: {
      hero_title?: { uz?: string; ru?: string; en?: string };
      hero_subtitle?: { uz?: string; ru?: string; en?: string };
      hero_badge?: string;
      hero_cta_text?: string;
      hero_view_services_text?: string;
      hero_image?: string;
    };
  };
}>();

const activeLanguage = ref('uz');

const languageTabs = [
  { key: 'uz', label: "O'zbek", flag: '🇺🇿' },
  { key: 'ru', label: 'Русский', flag: '🇷🇺' },
  { key: 'en', label: 'English', flag: '🇬🇧' },
];

const form = useForm({
  app_name: props.settings?.general?.app_name || '',
  company_phone: props.settings?.general?.company_phone || '',
  company_email: props.settings?.general?.company_email || '',
  company_address: props.settings?.general?.company_address || '',
  working_hours_start: props.settings?.general?.working_hours_start || '09:00',
  working_hours_end: props.settings?.general?.working_hours_end || '21:00',
  min_booking_hours: props.settings?.booking?.min_booking_hours || 2,
  max_booking_days: props.settings?.booking?.max_booking_days || 30,
  cancellation_hours: props.settings?.booking?.cancellation_hours || 1,
  auto_confirm_booking: props.settings?.booking?.auto_confirm_booking || false,
  telegram_link: props.settings?.social?.telegram_link || '',
  instagram_link: props.settings?.social?.instagram_link || '',
  facebook_link: props.settings?.social?.facebook_link || '',
  uz: {
    hero_title: props.settings?.hero?.hero_title?.uz || '',
    hero_subtitle: props.settings?.hero?.hero_subtitle?.uz || '',
  },
  ru: {
    hero_title: props.settings?.hero?.hero_title?.ru || '',
    hero_subtitle: props.settings?.hero?.hero_subtitle?.ru || '',
  },
  en: {
    hero_title: props.settings?.hero?.hero_title?.en || '',
    hero_subtitle: props.settings?.hero?.hero_subtitle?.en || '',
  },
  hero_badge: props.settings?.hero?.hero_badge || '',
  hero_cta_text: props.settings?.hero?.hero_cta_text || '',
  hero_view_services_text: props.settings?.hero?.hero_view_services_text || '',
  hero_image: null as File | null,
  hero_image_preview: props.settings?.hero?.hero_image ? `/storage/${props.settings.hero.hero_image}` : null,
});

const handleImageChange = (e: Event) => {
  const target = e.target as HTMLInputElement;
  const file = target.files?.[0];
  if (file) {
    form.hero_image = file;
    const reader = new FileReader();
    reader.onload = (event) => {
      form.hero_image_preview = event.target?.result as string;
    };
    reader.readAsDataURL(file);
  }
};

const submit = () => {
  form.put(route('admin.settings.update'), {
    forceFormData: true,
  });
};
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-bold tracking-tight">{{ t('settings.title', 'Sozlamalar') }}</h1>
      <p class="text-muted-foreground">{{ t('settings.subtitle', 'Tizim sozlamalari') }}</p>
    </div>

    <form @submit.prevent="submit">
      <Card>
        <Tabs default-value="general" class="w-full">
          <CardHeader class="pb-0">
            <TabsList class="grid w-full grid-cols-4">
              <TabsTrigger value="general">{{ t('settings.general', 'Umumiy') }}</TabsTrigger>
              <TabsTrigger value="booking">{{ t('settings.booking', 'Bron') }}</TabsTrigger>
              <TabsTrigger value="social">{{ t('settings.social', 'Ijtimoiy') }}</TabsTrigger>
              <TabsTrigger value="hero">{{ t('settings.hero', 'Hero') }}</TabsTrigger>
            </TabsList>
          </CardHeader>

          <CardContent class="pt-6">
            <!-- General Settings -->
            <TabsContent value="general" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label for="app_name">{{ t('settings.appName', 'Ilova nomi') }}</Label>
                  <Input id="app_name" v-model="form.app_name" :placeholder="t('settings.enterAppName', 'Ilova nomini kiriting')" />
                  <p v-if="form.errors.app_name" class="text-destructive text-xs">{{ form.errors.app_name }}</p>
                </div>

                <div class="space-y-2">
                  <Label for="company_phone">{{ t('settings.companyPhone', 'Telefon') }}</Label>
                  <Input id="company_phone" v-model="form.company_phone" placeholder="+998 90 123 45 67" />
                  <p v-if="form.errors.company_phone" class="text-destructive text-xs">{{ form.errors.company_phone }}</p>
                </div>

                <div class="space-y-2">
                  <Label for="company_email">{{ t('settings.companyEmail', 'Email') }}</Label>
                  <Input id="company_email" type="email" v-model="form.company_email" placeholder="info@example.com" />
                  <p v-if="form.errors.company_email" class="text-destructive text-xs">{{ form.errors.company_email }}</p>
                </div>

                <div class="space-y-2">
                  <Label for="company_address">{{ t('settings.companyAddress', 'Manzil') }}</Label>
                  <Input id="company_address" v-model="form.company_address" :placeholder="t('settings.enterAddress', 'Manzilni kiriting')" />
                  <p v-if="form.errors.company_address" class="text-destructive text-xs">{{ form.errors.company_address }}</p>
                </div>

                <div class="space-y-2">
                  <Label for="working_hours_start">{{ t('settings.workingHoursStart', 'Ish boshlanishi') }}</Label>
                  <Input id="working_hours_start" type="time" v-model="form.working_hours_start" />
                  <p v-if="form.errors.working_hours_start" class="text-destructive text-xs">{{ form.errors.working_hours_start }}</p>
                </div>

                <div class="space-y-2">
                  <Label for="working_hours_end">{{ t('settings.workingHoursEnd', 'Ish tugashi') }}</Label>
                  <Input id="working_hours_end" type="time" v-model="form.working_hours_end" />
                  <p v-if="form.errors.working_hours_end" class="text-destructive text-xs">{{ form.errors.working_hours_end }}</p>
                </div>
              </div>
            </TabsContent>

            <!-- Booking Settings -->
            <TabsContent value="booking" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label for="min_booking_hours">{{ t('settings.minBookingHours', 'Min soat') }}</Label>
                  <Input id="min_booking_hours" type="number" v-model="form.min_booking_hours" min="1" max="72" />
                  <p class="text-xs text-muted-foreground">{{ t('settings.minBookingHoursHint', 'Oldindan bron qilish (soat)') }}</p>
                  <p v-if="form.errors.min_booking_hours" class="text-destructive text-xs">{{ form.errors.min_booking_hours }}</p>
                </div>

                <div class="space-y-2">
                  <Label for="max_booking_days">{{ t('settings.maxBookingDays', 'Max kunlar') }}</Label>
                  <Input id="max_booking_days" type="number" v-model="form.max_booking_days" min="1" max="90" />
                  <p class="text-xs text-muted-foreground">{{ t('settings.maxBookingDaysHint', 'Kelajakka bron (kun)') }}</p>
                  <p v-if="form.errors.max_booking_days" class="text-destructive text-xs">{{ form.errors.max_booking_days }}</p>
                </div>

                <div class="space-y-2">
                  <Label for="cancellation_hours">{{ t('settings.cancellationHours', 'Bekor qilish (soat)') }}</Label>
                  <Input id="cancellation_hours" type="number" v-model="form.cancellation_hours" min="0" max="48" />
                  <p class="text-xs text-muted-foreground">{{ t('settings.cancellationHoursHint', 'Bepul bekor qilish vaqti') }}</p>
                  <p v-if="form.errors.cancellation_hours" class="text-destructive text-xs">{{ form.errors.cancellation_hours }}</p>
                </div>

                <div class="space-y-2 flex items-center justify-between rounded-lg border p-4">
                  <div class="space-y-0.5">
                    <Label>{{ t('settings.autoConfirmBooking', 'Avtomatik tasdiqlash') }}</Label>
                    <p class="text-xs text-muted-foreground">{{ t('settings.autoConfirmHint', 'Bronlarni avtomatik tasdiqlash') }}</p>
                  </div>
                  <Switch v-model:checked="form.auto_confirm_booking" />
                </div>
              </div>
            </TabsContent>

            <!-- Social Settings -->
            <TabsContent value="social" class="space-y-4">
              <div class="space-y-4">
                <div class="space-y-2">
                  <Label for="telegram_link" class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#0088cc]" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M11.944 0A12 12 0 1 0 24 12.056A12.014 12.014 0 0 0 11.944 0Zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472c-.18 1.898-.96 6.504-1.36 8.629c-.168.9-.499 1.201-.82 1.23c-.696.065-1.225-.46-1.9-.902c-1.056-.693-1.653-1.124-2.678-1.8c-1.185-.78-.417-1.21.258-1.91c.177-.184 3.247-2.977 3.307-3.23c.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345c-.48.33-.913.49-1.302.48c-.428-.008-1.252-.241-1.865-.44c-.752-.245-1.349-.374-1.297-.789c.027-.216.325-.437.893-.663c3.498-1.524 5.83-2.529 6.998-3.014c3.332-1.386 4.025-1.627 4.476-1.635Z"/>
                    </svg>
                    Telegram
                  </Label>
                  <Input id="telegram_link" type="url" v-model="form.telegram_link" placeholder="https://t.me/username" />
                  <p v-if="form.errors.telegram_link" class="text-destructive text-xs">{{ form.errors.telegram_link }}</p>
                </div>

                <div class="space-y-2">
                  <Label for="instagram_link" class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#E4405F]" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/>
                    </svg>
                    Instagram
                  </Label>
                  <Input id="instagram_link" type="url" v-model="form.instagram_link" placeholder="https://instagram.com/username" />
                  <p v-if="form.errors.instagram_link" class="text-destructive text-xs">{{ form.errors.instagram_link }}</p>
                </div>

                <div class="space-y-2">
                  <Label for="facebook_link" class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#1877F2]" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    Facebook
                  </Label>
                  <Input id="facebook_link" type="url" v-model="form.facebook_link" placeholder="https://facebook.com/pagename" />
                  <p v-if="form.errors.facebook_link" class="text-destructive text-xs">{{ form.errors.facebook_link }}</p>
                </div>
              </div>
            </TabsContent>

            <!-- Hero Settings -->
            <TabsContent value="hero" class="space-y-6">
              <Alert>
                <AlertDescription>
                  {{ t('settings.heroInfoText', 'Bu sozlamalar landing sahifaning hero qismiga ta\'sir qiladi.') }}
                </AlertDescription>
              </Alert>

              <!-- Hero Image -->
              <Card>
                <CardHeader>
                  <CardTitle class="text-base">{{ t('settings.heroImage', 'Hero rasmi') }}</CardTitle>
                </CardHeader>
                <CardContent>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <div v-if="form.hero_image_preview" class="relative overflow-hidden rounded-lg border">
                        <img :src="form.hero_image_preview" alt="Hero preview" class="w-full h-64 object-cover" />
                        <Button
                          type="button"
                          variant="destructive"
                          size="sm"
                          class="absolute top-2 right-2"
                          @click="form.hero_image = null; form.hero_image_preview = null"
                        >
                          O'chirish
                        </Button>
                      </div>
                      <div v-else class="rounded-lg border-2 border-dashed h-64 flex items-center justify-center text-muted-foreground">
                        Rasm tanlanmagan
                      </div>
                    </div>

                    <div>
                      <label class="block cursor-pointer">
                        <div class="border-2 border-dashed border-primary rounded-lg p-6 text-center hover:bg-muted/50 transition">
                          <svg class="w-8 h-8 mx-auto mb-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                          </svg>
                          <p class="font-medium text-sm">{{ t('settings.clickOrDrag', 'Rasm tanlang') }}</p>
                          <p class="text-xs text-muted-foreground mt-1">PNG, JPG, WebP. Max 5MB</p>
                        </div>
                        <input type="file" @change="handleImageChange" accept="image/*" class="hidden" />
                      </label>
                      <p v-if="form.errors.hero_image" class="text-destructive text-xs mt-2">{{ form.errors.hero_image }}</p>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <!-- Translatable Fields -->
              <Card>
                <CardHeader>
                  <CardTitle class="text-base flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                    </svg>
                    {{ t('translations.title', 'Tarjimalar') }}
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div class="flex gap-2 mb-4">
                    <Button
                      v-for="tab in languageTabs"
                      :key="tab.key"
                      type="button"
                      :variant="activeLanguage === tab.key ? 'default' : 'outline'"
                      size="sm"
                      @click="activeLanguage = tab.key"
                    >
                      {{ tab.flag }} {{ tab.label }}
                    </Button>
                  </div>

                  <!-- Uzbek -->
                  <div v-show="activeLanguage === 'uz'" class="space-y-4">
                    <div class="space-y-2">
                      <Label>{{ t('settings.heroTitle', 'Hero sarlavha') }}</Label>
                      <Input v-model="form.uz.hero_title" placeholder="Premium massaj xizmati..." />
                      <p v-if="form.errors['uz.hero_title']" class="text-destructive text-xs">{{ form.errors['uz.hero_title'] }}</p>
                    </div>
                    <div class="space-y-2">
                      <Label>{{ t('settings.heroSubtitle', 'Hero tavsif') }}</Label>
                      <Textarea v-model="form.uz.hero_subtitle" rows="3" placeholder="Uyingizga premium massaj xizmati..." />
                      <p v-if="form.errors['uz.hero_subtitle']" class="text-destructive text-xs">{{ form.errors['uz.hero_subtitle'] }}</p>
                    </div>
                  </div>

                  <!-- Russian -->
                  <div v-show="activeLanguage === 'ru'" class="space-y-4">
                    <div class="space-y-2">
                      <Label>{{ t('settings.heroTitle', 'Hero sarlavha') }}</Label>
                      <Input v-model="form.ru.hero_title" placeholder="Премиум массаж..." />
                      <p v-if="form.errors['ru.hero_title']" class="text-destructive text-xs">{{ form.errors['ru.hero_title'] }}</p>
                    </div>
                    <div class="space-y-2">
                      <Label>{{ t('settings.heroSubtitle', 'Hero tavsif') }}</Label>
                      <Textarea v-model="form.ru.hero_subtitle" rows="3" placeholder="Премиум массаж в вашем доме..." />
                      <p v-if="form.errors['ru.hero_subtitle']" class="text-destructive text-xs">{{ form.errors['ru.hero_subtitle'] }}</p>
                    </div>
                  </div>

                  <!-- English -->
                  <div v-show="activeLanguage === 'en'" class="space-y-4">
                    <div class="space-y-2">
                      <Label>{{ t('settings.heroTitle', 'Hero sarlavha') }}</Label>
                      <Input v-model="form.en.hero_title" placeholder="Premium massage service..." />
                      <p v-if="form.errors['en.hero_title']" class="text-destructive text-xs">{{ form.errors['en.hero_title'] }}</p>
                    </div>
                    <div class="space-y-2">
                      <Label>{{ t('settings.heroSubtitle', 'Hero tavsif') }}</Label>
                      <Textarea v-model="form.en.hero_subtitle" rows="3" placeholder="Premium massage service at your home..." />
                      <p v-if="form.errors['en.hero_subtitle']" class="text-destructive text-xs">{{ form.errors['en.hero_subtitle'] }}</p>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <!-- Non-translatable -->
              <div class="grid grid-cols-1 gap-4">
                <div class="space-y-2">
                  <Label for="hero_badge">{{ t('settings.heroBadge', 'Hero badge') }}</Label>
                  <Input id="hero_badge" v-model="form.hero_badge" placeholder="✦ Premium xizmat" />
                  <p class="text-xs text-muted-foreground">{{ t('settings.heroBadgeHint', 'Yuqoridagi kichik matn') }}</p>
                  <p v-if="form.errors.hero_badge" class="text-destructive text-xs">{{ form.errors.hero_badge }}</p>
                </div>

                <div class="space-y-2">
                  <Label for="hero_cta_text">{{ t('settings.heroCtaText', 'CTA tugma matni') }}</Label>
                  <Input id="hero_cta_text" v-model="form.hero_cta_text" placeholder="Seansni Band Qiling" />
                  <p v-if="form.errors.hero_cta_text" class="text-destructive text-xs">{{ form.errors.hero_cta_text }}</p>
                </div>

                <div class="space-y-2">
                  <Label for="hero_view_services_text">{{ t('settings.heroViewServicesText', 'Xizmatlar tugmasi') }}</Label>
                  <Input id="hero_view_services_text" v-model="form.hero_view_services_text" placeholder="Xizmatlarga o'tish" />
                  <p v-if="form.errors.hero_view_services_text" class="text-destructive text-xs">{{ form.errors.hero_view_services_text }}</p>
                </div>
              </div>
            </TabsContent>

            <!-- Submit Button -->
            <div class="pt-6 border-t mt-6">
              <Button type="submit" :disabled="form.processing">
                <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ form.processing ? t('common.saving', 'Saqlanmoqda...') : t('common.save', 'Saqlash') }}
              </Button>
            </div>
          </CardContent>
        </Tabs>
      </Card>
    </form>
  </div>
</template>
