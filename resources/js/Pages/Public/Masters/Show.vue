<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'

const { t } = useI18n()

const page = usePage()
const authUser = computed(() => page.props.auth?.user)
const canBook = computed(() => !authUser.value || authUser.value.role === 'customer')
const dashboardUrl = computed(() => {
    if (!authUser.value) return '/auth/login'
    if (authUser.value.role === 'master') return '/master/dashboard'
    if (authUser.value.role === 'customer') return '/customer/dashboard'
    return '/admin/dashboard'
})

const props = defineProps({
    master: {
        type: Object,
        required: true
    }
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('uz-UZ').format(price)
}

const serviceIcons = ['hand', 'flower-2', 'heart-pulse', 'sparkles', 'zap', 'droplets']

const getServiceIcon = (index) => {
    return serviceIcons[index % serviceIcons.length]
}
</script>

<template>
    <Head :title="master.full_name" />

    <div class="master-detail-page">
        <!-- Navigation -->
        <nav class="md-nav">
            <Link href="/" class="md-nav-logo">
                <svg class="md-nav-logo-icon" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m3 4.5a4.5 4.5 0 1 1-4.5-4.5M12 16.5V15m4.5-3a4.5 4.5 0 1 0-4.5-4.5M16.5 12H15"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                <span class="md-nav-logo-text">HOMEMASSAGE</span>
            </Link>

            <div class="md-nav-links">
                <Link href="/" class="md-nav-link">{{ t('common.home') }}</Link>
                <a href="/#services" class="md-nav-link">{{ t('landing.nav.services') }}</a>
                <Link href="/masters" class="md-nav-link active">{{ t('landing.nav.masters') }}</Link>
                <a href="/#testimonials" class="md-nav-link">{{ t('landing.nav.testimonials') }}</a>
                <a href="/#contact" class="md-nav-link">{{ t('landing.contact.badge') }}</a>
            </div>

            <div class="md-nav-right">
                <Link v-if="canBook" href="/booking" class="md-nav-cta">
                    {{ t('landing.nav.bookNow') }}
                </Link>

                <!-- Auth: logged in -->
                <div v-if="authUser" class="md-nav-user">
                    <Link :href="dashboardUrl" class="md-nav-user-link">
                        <span class="md-nav-avatar">{{ authUser.avatar }}</span>
                        <span class="md-nav-username">{{ authUser.name }}</span>
                    </Link>
                </div>

                <!-- Auth: not logged in -->
                <Link v-else href="/auth/login" class="md-nav-login">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                    <span>{{ t('public.master.login') }}</span>
                </Link>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="md-hero">
            <div class="md-hero-photo">
                <img
                    :src="master.photo_url || '/images/master-placeholder.svg'"
                    :alt="master.full_name"
                />
            </div>

            <div class="md-hero-info">
                <!-- Breadcrumb -->
                <div class="md-breadcrumb">
                    <Link href="/" class="md-bc-link">{{ t('common.home') }}</Link>
                    <span class="md-bc-sep">/</span>
                    <Link href="/masters" class="md-bc-link">{{ t('landing.nav.masters') }}</Link>
                    <span class="md-bc-sep">/</span>
                    <span class="md-bc-current">{{ master.full_name }}</span>
                </div>

                <!-- Name Block -->
                <div class="md-name-block">
                    <h1 class="md-master-name">{{ master.full_name }}</h1>
                    <p v-if="master.service_types?.length" class="md-master-role">
                        {{ master.service_types.map(st => st.name).join(' & ') }}
                    </p>
                </div>

                <!-- Short Bio -->
                <p v-if="master.bio" class="md-master-desc">{{ master.bio }}</p>

                <!-- Stats Row -->
                <div class="md-stats-row">
                    <div v-if="master.experience_years" class="md-stat">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
                        <div class="md-stat-text">
                            <span class="md-stat-val">{{ master.experience_years }} {{ t('public.masters.years') }}</span>
                            <span class="md-stat-label">{{ t('public.master.yearsExp') }}</span>
                        </div>
                    </div>
                    <div class="md-stat">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <div class="md-stat-text">
                            <span class="md-stat-val">5.0</span>
                            <span class="md-stat-label">{{ t('public.master.rating') }}</span>
                        </div>
                    </div>
                    <div class="md-stat">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <div class="md-stat-text">
                            <span class="md-stat-val">{{ master.completed_orders || '500+' }}</span>
                            <span class="md-stat-label">{{ t('public.master.clients') }}</span>
                        </div>
                    </div>
                </div>

                <!-- CTA Row -->
                <div class="md-cta-row">
                    <Link v-if="canBook" :href="`/booking?master=${master.id}`" class="md-cta-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>{{ t('landing.nav.bookNow') }}</span>
                    </Link>
                    <a href="tel:+998901234567" class="md-cta-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <span>{{ t('public.master.contact') }}</span>
                    </a>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section v-if="master.service_types?.length" class="md-services">
            <div class="md-section-header">
                <div class="md-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/><path d="M5 3v4"/><path d="M19 17v4"/><path d="M3 5h4"/><path d="M17 19h4"/></svg>
                    <span>{{ t('public.master.services') }}</span>
                </div>
                <h2 class="md-section-title">{{ t('public.master.servicesOffered') }}</h2>
                <p class="md-section-subtitle">{{ master.full_name }} {{ t('public.master.servicesBy') }}</p>
            </div>

            <div class="md-services-grid">
                <div
                    v-for="(service, index) in master.service_types"
                    :key="service.id"
                    class="md-service-card"
                >
                    <div class="md-service-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <template v-if="index % 3 === 0">
                                <path d="M18 11V6a2 2 0 0 0-2-2a2 2 0 0 0-2 2"/><path d="M14 10V4a2 2 0 0 0-2-2a2 2 0 0 0-2 2v2"/><path d="M10 10.5V6a2 2 0 0 0-2-2a2 2 0 0 0-2 2v8"/><path d="M18 8a2 2 0 1 1 4 0v6a8 8 0 0 1-8 8h-2c-2.8 0-4.5-.86-5.99-2.34l-3.6-3.6a2 2 0 0 1 2.83-2.82L7 15"/>
                            </template>
                            <template v-else-if="index % 3 === 1">
                                <path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m3 4.5a4.5 4.5 0 1 1-4.5-4.5M12 16.5V15m4.5-3a4.5 4.5 0 1 0-4.5-4.5M16.5 12H15"/><circle cx="12" cy="12" r="3"/>
                            </template>
                            <template v-else>
                                <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/><path d="M3.22 12H9.5l.5-1 2 4.5 2-7 1.5 3.5h5.27"/>
                            </template>
                        </svg>
                    </div>
                    <div class="md-service-info">
                        <h3 class="md-service-name">{{ service.name }}</h3>
                        <p v-if="service.description" class="md-service-desc">{{ service.description }}</p>
                    </div>
                    <!-- Durations with prices -->
                    <div class="md-service-durations">
                        <div
                            v-for="dur in service.durations"
                            :key="dur.id"
                            class="md-service-duration-row"
                        >
                            <div class="md-service-time">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <span>{{ dur.formatted_duration }}</span>
                            </div>
                            <span class="md-service-price">{{ formatPrice(dur.price) }} {{ t('common.sum') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section v-if="master.bio" class="md-about">
            <div class="md-about-info">
                <div class="md-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>{{ t('public.master.biography') }}</span>
                </div>
                <h2 class="md-about-title">{{ t('public.master.aboutTitle') }}</h2>
                <p class="md-about-desc">{{ master.bio }}</p>
            </div>

            <div class="md-about-sidebar">
                <!-- Skills / Service Types -->
                <div v-if="master.service_types?.length" class="md-sidebar-card">
                    <h3 class="md-sidebar-card-title">{{ t('public.master.specialization') }}</h3>
                    <div class="md-skill-tags">
                        <span
                            v-for="st in master.service_types"
                            :key="st.id"
                            class="md-skill-tag"
                        >{{ st.name }}</span>
                    </div>
                </div>

                <!-- Oils -->
                <div v-if="master.oils?.length" class="md-sidebar-card">
                    <h3 class="md-sidebar-card-title">{{ t('public.master.oils') }}</h3>
                    <div class="md-skill-tags">
                        <span
                            v-for="oil in master.oils"
                            :key="oil.id"
                            class="md-skill-tag"
                        >{{ oil.name }}</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="md-cta-section">
            <div class="md-cta-card">
                <h2 class="md-cta-title">{{ master.full_name }} {{ t('public.master.ctaBookWith') }}</h2>
                <p class="md-cta-desc">{{ t('public.master.ctaDesc') }}</p>
                <div class="md-cta-buttons">
                    <Link v-if="canBook" :href="`/booking?master=${master.id}`" class="md-cta-btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>{{ t('public.master.bookNow') }}</span>
                    </Link>
                    <Link href="/masters" class="md-cta-btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                        <span>{{ t('public.master.backToMasters') }}</span>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="md-footer">
            <div class="md-footer-divider"></div>
            <div class="md-footer-main">
                <div class="md-footer-brand">
                    <div class="md-footer-logo">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m3 4.5a4.5 4.5 0 1 1-4.5-4.5M12 16.5V15m4.5-3a4.5 4.5 0 1 0-4.5-4.5M16.5 12H15"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <span>HOMEMASSAGE</span>
                    </div>
                    <p class="md-footer-brand-desc">{{ t('landing.footer.tagline') }}</p>
                </div>
                <div class="md-footer-col">
                    <h4>{{ t('landing.nav.services') }}</h4>
                    <Link v-if="canBook" href="/booking">{{ t('landing.nav.bookNow') }}</Link>
                </div>
                <div class="md-footer-col">
                    <h4>{{ t('landing.footer.company') }}</h4>
                    <Link href="/masters">{{ t('landing.nav.masters') }}</Link>
                </div>
            </div>
            <div class="md-footer-divider"></div>
            <div class="md-footer-bottom">
                <span>&copy; 2026 HomeMassage. {{ t('landing.footer.rights') }}</span>
            </div>
        </footer>
    </div>
</template>
