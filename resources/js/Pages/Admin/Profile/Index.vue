<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Separator } from '@/components/ui/separator';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps<{
  user: {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    created_at: string;
  };
}>();

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
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-bold tracking-tight">{{ t('profile.title', 'Profil') }}</h1>
      <p class="text-muted-foreground">{{ t('profile.subtitle', 'Profil sozlamalari') }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- User Card -->
      <Card>
        <CardHeader>
          <CardTitle>{{ t('profile.about', 'Ma\'lumot') }}</CardTitle>
        </CardHeader>
        <CardContent class="text-center space-y-4">
          <Avatar class="w-24 h-24 mx-auto text-3xl">
            <AvatarFallback class="bg-primary text-primary-foreground text-2xl font-bold">
              {{ user.name?.charAt(0).toUpperCase() || 'A' }}
            </AvatarFallback>
          </Avatar>
          
          <div>
            <h4 class="text-lg font-semibold">{{ user.name }}</h4>
            <p class="text-sm text-muted-foreground">{{ user.email }}</p>
            <p v-if="user.phone" class="text-sm text-muted-foreground">{{ user.phone }}</p>
          </div>

          <Separator />

          <div class="text-left space-y-3 text-sm">
            <div class="flex items-center text-muted-foreground">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
              </svg>
              <span>{{ t('profile.role', 'Role') }}: <strong class="text-foreground">Admin</strong></span>
            </div>
            <div class="flex items-center text-muted-foreground">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
              <span>{{ t('profile.memberSince', 'A\'zo') }}: <strong class="text-foreground">{{ new Date(user.created_at).toLocaleDateString() }}</strong></span>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Forms -->
      <div class="lg:col-span-2">
        <Card>
          <Tabs default-value="profile" class="w-full">
            <CardHeader class="pb-0">
              <TabsList class="grid w-full grid-cols-2">
                <TabsTrigger value="profile">{{ t('profile.info', 'Ma\'lumotlar') }}</TabsTrigger>
                <TabsTrigger value="password">{{ t('profile.security', 'Xavfsizlik') }}</TabsTrigger>
              </TabsList>
            </CardHeader>
            
            <CardContent class="pt-6">
              <!-- Profile Form -->
              <TabsContent value="profile">
                <form @submit.prevent="submitProfile" class="space-y-4">
                  <div class="space-y-2">
                    <Label for="name">
                      {{ t('profile.name', 'Ism') }} <span class="text-destructive">*</span>
                    </Label>
                    <Input
                      id="name"
                      v-model="profileForm.name"
                      :placeholder="t('profile.enterName', 'Ismingizni kiriting')"
                    />
                    <p v-if="profileForm.errors.name" class="text-destructive text-xs">{{ profileForm.errors.name }}</p>
                  </div>

                  <div class="space-y-2">
                    <Label for="email">
                      {{ t('profile.email', 'Email') }} <span class="text-destructive">*</span>
                    </Label>
                    <Input
                      id="email"
                      type="email"
                      v-model="profileForm.email"
                      :placeholder="t('profile.enterEmail', 'Emailingizni kiriting')"
                    />
                    <p v-if="profileForm.errors.email" class="text-destructive text-xs">{{ profileForm.errors.email }}</p>
                  </div>

                  <div class="space-y-2">
                    <Label for="phone">{{ t('profile.phone', 'Telefon') }}</Label>
                    <Input
                      id="phone"
                      v-model="profileForm.phone"
                      placeholder="+998 90 123 45 67"
                    />
                    <p v-if="profileForm.errors.phone" class="text-destructive text-xs">{{ profileForm.errors.phone }}</p>
                  </div>

                  <div class="pt-4">
                    <Button type="submit" :disabled="profileForm.processing">
                      <svg v-if="profileForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      {{ profileForm.processing ? t('common.saving', 'Saqlanmoqda...') : t('profile.updateProfile', 'Saqlash') }}
                    </Button>
                  </div>
                </form>
              </TabsContent>

              <!-- Password Form -->
              <TabsContent value="password">
                <form @submit.prevent="submitPassword" class="space-y-4">
                  <div class="space-y-2">
                    <Label for="current_password">
                      {{ t('profile.currentPassword', 'Joriy parol') }} <span class="text-destructive">*</span>
                    </Label>
                    <Input
                      id="current_password"
                      type="password"
                      v-model="passwordForm.current_password"
                      :placeholder="t('profile.enterCurrentPassword', 'Joriy parolni kiriting')"
                    />
                    <p v-if="passwordForm.errors.current_password" class="text-destructive text-xs">{{ passwordForm.errors.current_password }}</p>
                  </div>

                  <div class="space-y-2">
                    <Label for="password">
                      {{ t('profile.newPassword', 'Yangi parol') }} <span class="text-destructive">*</span>
                    </Label>
                    <Input
                      id="password"
                      type="password"
                      v-model="passwordForm.password"
                      :placeholder="t('profile.enterNewPassword', 'Yangi parolni kiriting')"
                    />
                    <p class="text-xs text-muted-foreground">{{ t('profile.passwordHint', 'Kamida 8 ta belgi') }}</p>
                    <p v-if="passwordForm.errors.password" class="text-destructive text-xs">{{ passwordForm.errors.password }}</p>
                  </div>

                  <div class="space-y-2">
                    <Label for="password_confirmation">
                      {{ t('profile.confirmPassword', 'Parolni tasdiqlang') }} <span class="text-destructive">*</span>
                    </Label>
                    <Input
                      id="password_confirmation"
                      type="password"
                      v-model="passwordForm.password_confirmation"
                      :placeholder="t('profile.enterConfirmPassword', 'Parolni qayta kiriting')"
                    />
                  </div>

                  <div class="pt-4">
                    <Button type="submit" variant="secondary" :disabled="passwordForm.processing">
                      <svg v-if="passwordForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      {{ passwordForm.processing ? t('common.saving', 'Saqlanmoqda...') : t('profile.changePassword', 'Parolni o\'zgartirish') }}
                    </Button>
                  </div>
                </form>
              </TabsContent>
            </CardContent>
          </Tabs>
        </Card>
      </div>
    </div>
  </div>
</template>
