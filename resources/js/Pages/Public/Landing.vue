<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
    serviceTypes: Array,
    masters: Array,
    stats: Object,
})
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">
        <Head title="Welcome" />

        <!-- Navigation -->
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="text-2xl font-bold text-slate-900">Golden Touch</div>
                    <div class="flex gap-4">
                        <Link href="/admin/login" class="px-4 py-2 text-slate-600 hover:text-slate-900">
                            {{ t('common.login') }}
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="text-center">
                <h1 class="text-5xl font-bold text-slate-900 mb-6">
                    Welcome to Golden Touch
                </h1>
                <p class="text-xl text-slate-600 mb-8 max-w-2xl mx-auto">
                    Premium massage booking system. Experience relaxation and wellness.
                </p>
                <div class="flex justify-center gap-4">
                    <Link href="/masters" class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        View Masters
                    </Link>
                    <Link href="/admin/login" class="px-8 py-3 bg-slate-200 text-slate-900 rounded-lg hover:bg-slate-300">
                        Admin Panel
                    </Link>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <div v-if="stats" class="bg-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                    <div>
                        <div class="text-4xl font-bold text-blue-600">{{ stats.years }}+</div>
                        <div class="text-slate-600 mt-2">Years of Experience</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-blue-600">{{ stats.clients }}</div>
                        <div class="text-slate-600 mt-2">Happy Clients</div>
                    </div>
                    <div>
                        <div class="text-4xl font-bold text-blue-600">{{ stats.rating }}</div>
                        <div class="text-slate-600 mt-2">Average Rating</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-slate-900 text-white py-8 mt-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p>&copy; 2026 Golden Touch. All rights reserved.</p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Optional: Add any page-specific styles here */
</style>
