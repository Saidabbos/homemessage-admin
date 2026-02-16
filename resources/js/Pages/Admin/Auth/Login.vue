<script setup>
import { useForm, Head } from '@inertiajs/vue3';

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
    <Head title="Admin | Kirish" />

    <div class="al-page">
        <div class="al-box">
            <div class="al-header">
                <h1 class="al-title">Golden Touch</h1>
                <p class="al-subtitle">Admin Paneli</p>
            </div>

            <div class="al-body">
                <p class="al-hint">Tizimga kirish uchun ma'lumotlarni kiriting</p>

                <form @submit.prevent="submit">
                    <div class="al-field">
                        <label class="al-label" for="email">Email</label>
                        <div class="al-input-wrap">
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="al-input"
                                :class="{ 'al-input-error': form.errors.email }"
                                placeholder="admin@example.com"
                                autocomplete="email"
                                autofocus
                            />
                            <span class="al-input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="4" width="20" height="16" rx="2"/>
                                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                                </svg>
                            </span>
                        </div>
                        <p v-if="form.errors.email" class="al-error">{{ form.errors.email }}</p>
                    </div>

                    <div class="al-field">
                        <label class="al-label" for="password">Parol</label>
                        <div class="al-input-wrap">
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="al-input"
                                :class="{ 'al-input-error': form.errors.password }"
                                placeholder="Parol"
                                autocomplete="current-password"
                            />
                            <span class="al-input-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </span>
                        </div>
                        <p v-if="form.errors.password" class="al-error">{{ form.errors.password }}</p>
                    </div>

                    <div class="al-row">
                        <label class="al-check">
                            <input type="checkbox" v-model="form.remember" />
                            <span>Eslab qolish</span>
                        </label>
                    </div>

                    <button type="submit" class="al-btn" :disabled="form.processing">
                        <svg v-if="form.processing" class="al-btn-spinner" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                        </svg>
                        <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                            <polyline points="10 17 15 12 10 7"/>
                            <line x1="15" y1="12" x2="3" y2="12"/>
                        </svg>
                        {{ form.processing ? 'Kirish...' : 'Kirish' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
