<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
    master: { type: Object, required: true },
})

const logout = () => {
    router.post('/master/logout')
}
</script>

<template>
    <Head :title="t('master.profile.title')" />

    <div class="mp-page">
        <!-- Sidebar -->
        <aside class="mp-sidebar">
            <div class="mp-sidebar-top">
                <Link href="/" class="mp-sidebar-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m3 4.5a4.5 4.5 0 1 1-4.5-4.5M12 16.5V15m4.5-3a4.5 4.5 0 1 0-4.5-4.5M16.5 12H15"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <span>HOMEMASSAGE</span>
                </Link>

                <nav class="mp-nav">
                    <Link href="/master/dashboard" class="mp-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                        <span>{{ t('master.navDashboard') }}</span>
                    </Link>
                    <Link href="/master/orders" class="mp-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span>{{ t('master.navOrders') }}</span>
                    </Link>
                    <Link href="/master/ratings" class="mp-nav-item">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <span>{{ t('master.navRatings') }}</span>
                    </Link>
                    <Link href="/master/profile" class="mp-nav-item active">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>{{ t('master.navProfile') }}</span>
                    </Link>
                </nav>
            </div>

            <div class="mp-sidebar-bottom">
                <div class="mp-sidebar-divider"></div>
                <div class="mp-user-profile">
                    <Link href="/master/dashboard" class="mp-user-link">
                        <div class="mp-user-avatar">
                            <img v-if="master.photo_url" :src="master.photo_url" :alt="master.name" />
                            <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <div class="mp-user-info">
                            <span class="mp-user-name">{{ master.name }}</span>
                            <span class="mp-user-phone">{{ master.phone }}</span>
                        </div>
                    </Link>
                    <button class="mp-logout-btn" @click="logout" :title="t('master.logout')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Area -->
        <main class="mp-main">
            <div class="mp-topbar">
                <h1 class="mp-page-title">{{ t('master.profile.title') }}</h1>
                <p class="mp-page-subtitle">{{ t('master.profile.subtitle') }}</p>
            </div>

            <div class="mp-content">
                <!-- Profile Header -->
                <div class="mp-header-card">
                    <div class="mp-photo">
                        <img v-if="master.photo_url" :src="master.photo_url" :alt="master.name" />
                        <svg v-else xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div class="mp-header-info">
                        <h2 class="mp-name">{{ master.name }}</h2>
                        <p class="mp-role">{{ t('master.profile.massageTherapist') }}</p>
                        <div v-if="master.rating" class="mp-rating-badge">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            {{ master.rating }}
                        </div>
                    </div>
                    <Link href="/master/profile/edit" class="mp-edit-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                        {{ t('common.edit') }}
                    </Link>
                </div>

                <!-- Contact Information -->
                <div class="mp-info-card">
                    <h3 class="mp-info-title">{{ t('master.profile.contactInfo') }}</h3>
                    <div class="mp-info-rows">
                        <div class="mp-info-row">
                            <div class="mp-info-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            </div>
                            <div class="mp-info-content">
                                <span class="mp-info-label">{{ t('master.profile.phone') }}</span>
                                <span class="mp-info-value">{{ master.phone }}</span>
                            </div>
                        </div>
                        <div v-if="master.email" class="mp-info-row">
                            <div class="mp-info-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                            </div>
                            <div class="mp-info-content">
                                <span class="mp-info-label">{{ t('master.profile.email') }}</span>
                                <span class="mp-info-value">{{ master.email }}</span>
                            </div>
                        </div>
                        <div v-if="master.experience_years" class="mp-info-row">
                            <div class="mp-info-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>
                            </div>
                            <div class="mp-info-content">
                                <span class="mp-info-label">{{ t('master.profile.experience') }}</span>
                                <span class="mp-info-value">{{ master.experience_years }} {{ t('master.profile.years') }}</span>
                            </div>
                        </div>
                        <div v-if="master.work_hours" class="mp-info-row">
                            <div class="mp-info-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </div>
                            <div class="mp-info-content">
                                <span class="mp-info-label">{{ t('master.profile.workHours') }}</span>
                                <span class="mp-info-value">{{ master.work_hours }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bio -->
                <div v-if="master.bio" class="mp-info-card">
                    <h3 class="mp-info-title">{{ t('master.profile.bio') }}</h3>
                    <p class="mp-bio">{{ master.bio }}</p>
                </div>

                <!-- Services -->
                <div v-if="master.service_types && master.service_types.length > 0" class="mp-info-card">
                    <h3 class="mp-info-title">{{ t('master.profile.services') }}</h3>
                    <div class="mp-tags">
                        <span v-for="service in master.service_types" :key="service.id" class="mp-tag">
                            {{ service.name }}
                        </span>
                    </div>
                </div>

                <!-- Pressure Levels -->
                <div v-if="master.pressure_levels && master.pressure_levels.length > 0" class="mp-info-card">
                    <h3 class="mp-info-title">{{ t('master.profile.pressureLevels') }}</h3>
                    <div class="mp-tags">
                        <span v-for="level in master.pressure_levels" :key="level.id" class="mp-tag mp-tag-gold">
                            {{ level.name }}
                        </span>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
.mp-header-card {
    position: relative;
}

.mp-edit-btn {
    position: absolute;
    top: 1rem;
    right: 1rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: rgba(251, 191, 36, 0.15);
    border: 1px solid rgba(251, 191, 36, 0.3);
    border-radius: 0.5rem;
    color: #fbbf24;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
}

.mp-edit-btn:hover {
    background: rgba(251, 191, 36, 0.25);
    border-color: rgba(251, 191, 36, 0.5);
}
</style>
