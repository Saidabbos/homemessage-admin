<script setup>
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import TextInput from '@/Components/Form/TextInput.vue';
import Checkbox from '@/Components/Form/Checkbox.vue';
import Button from '@/Components/UI/Button.vue';

defineOptions({ layout: GuestLayout });

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post(route('admin.login'), {
    preserveScroll: true,
  });
};
</script>

<template>
  <div class="bg-white rounded-lg shadow-2xl p-8">
      <h2 class="text-3xl font-bold text-center mb-2">🏠 HomeMessage</h2>
      <p class="text-center text-gray-600 mb-8">Admin Paneli</p>

      <form @submit.prevent="submit" class="space-y-4">
        <TextInput
          v-model="form.email"
          label="Email"
          :error="form.errors.email"
          placeholder="admin@example.com"
          type="email"
          autocomplete="email"
        />

        <TextInput
          v-model="form.password"
          label="Parol"
          :error="form.errors.password"
          placeholder="••••••••"
          type="password"
          autocomplete="current-password"
        />

        <Checkbox v-model="form.remember" label="Eslab qolish" />

        <Button
          type="submit"
          :loading="form.processing"
          variant="primary"
          class="w-full justify-center"
        >
          🔑 Kirish
        </Button>
      </form>

      <div class="mt-6 text-center text-sm text-gray-600">
        <p>Test Hisob:</p>
        <p class="font-mono text-xs mt-1">admin@example.com</p>
        <p class="font-mono text-xs">password</p>
      </div>
  </div>
</template>
