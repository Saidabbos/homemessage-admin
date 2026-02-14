<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { ref, computed } from 'vue'

const { t, locale } = useI18n()

const props = defineProps({
    masters: {
        type: Array,
        default: () => []
    },
    serviceTypes: {
        type: Array,
        default: () => []
    }
})

const selectedServiceType = ref(null)
const searchQuery = ref('')

const filteredMasters = computed(() => {
    let result = props.masters
    if (selectedServiceType.value) {
        result = result.filter(master =>
            master.service_types.some(st => st.id === selectedServiceType.value)
        )
    }
    if (searchQuery.value.trim()) {
        const q = searchQuery.value.trim().toLowerCase()
        result = result.filter(master =>
            master.full_name.toLowerCase().includes(q)
        )
    }
    return result
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('uz-UZ').format(price)
}
</script>

<template>
    <Head :title="t('public.masters.title')" />

    <div class="masters-page">
        <!-- Navigation -->
        <nav class="mp-nav">
            <Link href="/" class="mp-nav-logo">
                <svg class="mp-nav-logo-icon" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m3 4.5a4.5 4.5 0 1 1-4.5-4.5M12 16.5V15m4.5-3a4.5 4.5 0 1 0-4.5-4.5M16.5 12H15"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                <span class="mp-nav-logo-text">HOMEMASSAGE</span>
            </Link>

            <div class="mp-nav-links">
                <Link href="/" class="mp-nav-link">{{ t('common.home') }}</Link>
                <a href="/#services" class="mp-nav-link">{{ t('landing.nav.services') }}</a>
                <span class="mp-nav-link active">{{ t('landing.nav.masters') }}</span>
                <a href="/#testimonials" class="mp-nav-link">{{ t('landing.nav.testimonials') }}</a>
                <a href="/#contact" class="mp-nav-link">{{ t('landing.contact.badge') }}</a>
            </div>

            <Link href="/booking" class="mp-nav-cta">
                {{ t('landing.nav.bookNow') }}
            </Link>
        </nav>

        <!-- Hero Section -->
        <section class="mp-hero">
            <div class="mp-hero-header">
                <div class="mp-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span>{{ t('public.masters.badge') }}</span>
                </div>
                <h1 class="mp-hero-title">{{ t('public.masters.heroTitle') }}</h1>
                <p class="mp-hero-subtitle">{{ t('public.masters.heroSubtitle') }}</p>
            </div>

            <!-- Search & Filter -->
            <div class="mp-filter-bar">
                <div class="mp-search-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.3-4.3"/>
                    </svg>
                    <input
                        v-model="searchQuery"
                        type="text"
                        class="mp-search-input"
                        :placeholder="t('public.masters.searchPlaceholder')"
                    />
                </div>
                <div class="mp-filter-tabs">
                    <button
                        class="mp-filter-tab"
                        :class="{ active: !selectedServiceType }"
                        @click="selectedServiceType = null"
                    >
                        {{ t('common.all') }}
                    </button>
                    <button
                        v-for="st in serviceTypes"
                        :key="st.id"
                        class="mp-filter-tab"
                        :class="{ active: selectedServiceType === st.id }"
                        @click="selectedServiceType = st.id"
                    >
                        {{ st.name }}
                    </button>
                </div>
            </div>
        </section>

        <!-- Masters Grid -->
        <section class="mp-grid-section">
            <div v-if="filteredMasters.length === 0" class="mp-empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.4;margin-bottom:16px;">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <p>{{ t('public.masters.empty') }}</p>
            </div>

            <div v-else class="mp-masters-grid">
                <Link
                    v-for="master in filteredMasters"
                    :key="master.id"
                    :href="`/masters/${master.id}`"
                    class="mp-master-card"
                >
                    <div class="mp-card-img">
                        <img
                            :src="master.photo_url || '/images/master-placeholder.svg'"
                            :alt="master.full_name"
                        />
                    </div>
                    <div class="mp-card-info">
                        <h3 class="mp-card-name">{{ master.full_name }}</h3>
                        <p v-if="master.service_types.length" class="mp-card-role">
                            {{ master.service_types.map(st => st.name).join(' & ') }}
                        </p>
                        <p class="mp-card-desc">
                            {{ master.bio || t('landing.masters.defaultBio') }}
                        </p>
                        <div class="mp-card-stats">
                            <span v-if="master.experience_years" class="mp-stat">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
                                {{ master.experience_years }} {{ t('public.masters.years') }}
                            </span>
                            <span class="mp-stat">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="#C8A951" stroke="#C8A951" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                5.0 ({{ master.completed_orders || 0 }})
                            </span>
                        </div>
                        <div class="mp-card-cta">
                            <span>{{ t('public.masters.select') }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </div>
                    </div>
                </Link>
            </div>
        </section>

        <!-- Footer -->
        <footer class="mp-footer">
            <div class="mp-footer-content">
                <span>&copy; 2026 HomeMassage. {{ t('landing.footer.rights') }}</span>
            </div>
        </footer>
    </div>
</template>
