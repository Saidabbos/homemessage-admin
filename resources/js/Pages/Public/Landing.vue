<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { ref, computed, onMounted, onUnmounted } from 'vue'

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
    serviceTypes: Array,
    masters: Array,
    testimonials: Array,
    stats: Object,
    hero: Object,
})

const mobileMenuOpen = ref(false)
const navScrolled = ref(false)

const heroTitle = props.hero?.title || t('landing.hero.title')
const heroSubtitle = props.hero?.subtitle || t('landing.hero.subtitle')
const heroBadge = props.hero?.badge || t('landing.hero.badge')
const heroCtaText = props.hero?.cta_text || t('landing.hero.cta')
const heroViewServicesText = props.hero?.view_services_text || t('landing.hero.viewServices')
const heroImage = props.hero?.image || null

const defaultServices = [
    {
        name: 'Klassik Massaj',
        description: 'Tanani bo\'shashtirish va qon aylanishini yaxshilash uchun an\'anaviy uslubdagi massaj.',
        price: '200 000 so\'m',
        duration: '60 daqiqa',
        icon: 'hand',
        image: 'https://images.unsplash.com/photo-1620051844584-15ac31d5fccd?w=600&q=80',
    },
    {
        name: 'Sport Massaj',
        description: 'Sportchilar va faol hayot kechiruvchilar uchun chuqur to\'qimali massaj turi.',
        price: '250 000 so\'m',
        duration: '90 daqiqa',
        icon: 'zap',
        image: 'https://images.unsplash.com/photo-1706353399656-210cca727a33?w=600&q=80',
    },
    {
        name: 'Aromaterapiya',
        description: 'Tabiiy efir moylari yordamida tana va ruhni tinchlantiruvchi maxsus massaj.',
        price: '280 000 so\'m',
        duration: '75 daqiqa',
        icon: 'droplets',
        image: 'https://images.unsplash.com/photo-1561546994-c0e1409157d4?w=600&q=80',
    },
    {
        name: 'Tosh Massaj',
        description: 'Issiq bazalt toshlari bilan mushaklarni chuqur bo\'shashtirish va stressni bartaraf etish.',
        price: '350 000 so\'m',
        duration: '90 daqiqa',
        icon: 'flame',
        image: 'https://images.unsplash.com/photo-1769011218290-6ab77ae85883?w=600&q=80',
    },
]

const services = (props.serviceTypes && props.serviceTypes.length > 0)
    ? props.serviceTypes.map((s, i) => ({
        name: s.name,
        description: s.description || defaultServices[i]?.description || '',
        price: s.base_price ? `${Number(s.base_price).toLocaleString('uz')} so'm` : defaultServices[i]?.price,
        duration: s.durations?.[0]?.duration_minutes
            ? `${s.durations[0].duration_minutes} daqiqa`
            : defaultServices[i]?.duration,
        icon: defaultServices[i]?.icon || 'hand',
        image: s.image_url || defaultServices[i]?.image,
    }))
    : defaultServices

let scrollContainer = ref(null)
let rafId = null

function handleScroll() {
    if (!scrollContainer.value) return
    const st = scrollContainer.value.scrollTop
    navScrolled.value = st > 80

    if (rafId) return
    rafId = requestAnimationFrame(() => {
        applyParallax(st)
        rafId = null
    })
}

function applyParallax(scrollTop) {
    const vh = window.innerHeight
    const sections = scrollContainer.value.querySelectorAll('.snap-section, .contact-footer-wrapper')

    sections.forEach(section => {
        const rect = section.getBoundingClientRect()
        const sectionTop = rect.top
        const progress = -sectionTop / vh // 0 = at viewport top, 1 = scrolled past

        // Parallax for section header
        const header = section.querySelector('.section-header')
        if (header) {
            const ty = progress * 30
            header.style.transform = `translateY(${ty}px)`
        }

        // Parallax for grid content (cards move slower)
        const grid = section.querySelector('.services-grid, .masters-grid, .testimonials-grid, .contact-row')
        if (grid) {
            const ty = progress * -20
            grid.style.transform = `translateY(${ty}px)`
        }

        // Fade-in based on visibility
        const ratio = 1 - Math.abs(sectionTop) / vh
        if (ratio > 0 && ratio <= 1) {
            section.style.setProperty('--section-visibility', Math.min(ratio * 1.5, 1))
        }
    })

    // Hero parallax — content moves up faster, bg stays
    const heroContent = scrollContainer.value.querySelector('.hero-center')
    if (heroContent) {
        const ty = scrollTop * 0.35
        const opacity = Math.max(1 - scrollTop / (vh * 0.6), 0)
        heroContent.style.transform = `translateY(${ty}px)`
        heroContent.style.opacity = opacity
    }

    // Hero stats parallax — moves slightly slower
    const heroStats = scrollContainer.value.querySelector('.hero-stats')
    if (heroStats) {
        const ty = scrollTop * 0.2
        heroStats.style.transform = `translateY(${ty}px)`
    }
}

onMounted(() => {
    if (scrollContainer.value) {
        scrollContainer.value.addEventListener('scroll', handleScroll, { passive: true })
    }
})

onUnmounted(() => {
    if (scrollContainer.value) {
        scrollContainer.value.removeEventListener('scroll', handleScroll)
    }
    if (rafId) cancelAnimationFrame(rafId)
})

const defaultMasters = [
    { name: 'Nilufar Karimova', specialty: 'Klassik & Aromaterapiya', experience: 8, rating: 5.0, photo_url: 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=400&q=80' },
    { name: 'Jasur Raximov', specialty: 'Sport & Chuqur to\'qimali', experience: 12, rating: 4.9, photo_url: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&q=80' },
    { name: 'Madina Azimova', specialty: 'Tosh massaj & SPA', experience: 6, rating: 4.8, photo_url: 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400&q=80' },
    { name: 'Bobur Xasanov', specialty: 'Reabilitatsiya massaji', experience: 15, rating: 5.0, photo_url: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400&q=80' },
]

const mastersList = (props.masters && props.masters.length > 0)
    ? props.masters.map((m, i) => ({
        id: m.id,
        name: m.full_name || `${m.first_name || ''} ${m.last_name || ''}`.trim() || defaultMasters[i]?.name || '',
        specialty: m.service_types?.map(s => s.name).join(' & ') || defaultMasters[i]?.specialty || '',
        experience: m.experience_years || defaultMasters[i]?.experience || 0,
        rating: m.rating || defaultMasters[i]?.rating || 5.0,
        photo_url: m.photo_url || defaultMasters[i]?.photo_url || null,
    }))
    : defaultMasters

const defaultTestimonials = [
    {
        client_name: 'Dilnoza Karimova',
        client_role: 'Doimiy mijoz',
        comment: 'Nilufar xonim juda professional ish qildi. Aromaterapiya seansidan keyin o\'zimni butunlay yangilangan his qildim. Endi har oyda muntazam bron qilib turaman.',
        rating: 5,
    },
    {
        client_name: 'Alisher Raxmatullayev',
        client_role: 'Sportchi',
        comment: 'Jasur akaning sport massaji mushaklardagi og\'riqni butunlay yo\'qotdi. Uyga kelib xizmat ko\'rsatishi juda qulay. Sifat darajasi yuqori!',
        rating: 5,
    },
    {
        client_name: 'Sevara Abdullayeva',
        client_role: 'Uy bekasi',
        comment: 'Homiladorlik davrida tosh massaj seansini olganman. Madina xonim juda ehtiyotkor va mehribon munosabatda bo\'ldi. Ajoyib tajriba!',
        rating: 5,
    },
]

const testimonialsList = (props.testimonials && props.testimonials.length > 0)
    ? props.testimonials.map((item, i) => ({
        client_name: item.client_name || defaultTestimonials[i]?.client_name || '',
        client_role: item.client_role || defaultTestimonials[i]?.client_role || '',
        comment: item.comment || defaultTestimonials[i]?.comment || '',
        rating: item.rating || defaultTestimonials[i]?.rating || 5,
    }))
    : defaultTestimonials

function tr(field) {
    if (typeof field === 'string') return field
    if (field && typeof field === 'object') {
        const locale = document.documentElement.lang || 'uz'
        return field[locale] || field.uz || field.ru || field.en || Object.values(field)[0] || ''
    }
    return ''
}

function getInitials(name) {
    const str = tr(name)
    if (!str) return ''
    return str.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2)
}

function getStarCount(rating) {
    return Math.round(rating)
}

function formatPrice(price) {
    if (typeof price === 'number') {
        return `${price.toLocaleString('uz')} so'm`
    }
    return price
}

// Contact form
const contactForm = ref({
    name: '',
    phone: '',
    service_type: '',
    message: '',
})
const contactSubmitting = ref(false)
const contactSuccess = ref(false)

const serviceOptions = computed(() => {
    if (props.serviceTypes && props.serviceTypes.length > 0) {
        return props.serviceTypes.map(s => ({ id: s.id, name: s.name }))
    }
    return defaultServices.map((s, i) => ({ id: i + 1, name: s.name }))
})

function submitContact() {
    contactSubmitting.value = true
    // Simulate submission (can be replaced with actual API call later)
    setTimeout(() => {
        contactSubmitting.value = false
        contactSuccess.value = true
        contactForm.value = { name: '', phone: '', service_type: '', message: '' }
        setTimeout(() => { contactSuccess.value = false }, 3000)
    }, 1000)
}
</script>

<template>
    <div class="landing-page">
        <Head :title="t('landing.title')" />

        <!-- Fixed Navigation -->
        <nav class="fixed-nav" :class="{ scrolled: navScrolled }">
            <div class="nav-inner">
                <Link href="/" class="nav-logo">
                    <svg class="nav-logo-icon" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m3 4.5a4.5 4.5 0 1 1-4.5-4.5M12 16.5V15m4.5-3a4.5 4.5 0 1 0-4.5-4.5M16.5 12H15"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <span class="nav-logo-text">HOMEMASSAGE</span>
                </Link>

                <!-- Desktop Nav Links -->
                <div class="nav-links">
                    <a href="#hero" class="nav-link active">{{ t('common.home') }}</a>
                    <a href="#services" class="nav-link">{{ t('landing.nav.services') }}</a>
                    <Link href="/masters" class="nav-link">{{ t('landing.nav.masters') }}</Link>
                    <a href="#testimonials" class="nav-link">{{ t('landing.nav.testimonials') }}</a>
                    <a href="#contact" class="nav-link">{{ t('landing.nav.about') }}</a>
                </div>

                <!-- Desktop Right -->
                <div class="nav-right">
                    <Link v-if="canBook" href="/booking" class="nav-cta">
                        <span>{{ t('landing.nav.bookNow') }}</span>
                    </Link>

                    <!-- Auth: logged in -->
                    <div v-if="authUser" class="nav-user">
                        <Link :href="dashboardUrl" class="nav-user-link">
                            <span class="nav-avatar">{{ authUser.avatar }}</span>
                            <span class="nav-username">{{ authUser.name }}</span>
                        </Link>
                    </div>

                    <!-- Auth: not logged in -->
                    <Link v-else href="/auth/login" class="nav-login">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                        <span>{{ t('auth.login') }}</span>
                    </Link>
                </div>

                <!-- Mobile Menu Button -->
                <button class="nav-mobile-toggle" @click="mobileMenuOpen = !mobileMenuOpen">
                    <svg v-if="!mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu Dropdown -->
            <Transition name="slide-down">
                <div v-if="mobileMenuOpen" class="mobile-menu">
                    <a href="#hero" class="mobile-menu-link active" @click="mobileMenuOpen = false">{{ t('common.home') }}</a>
                    <a href="#services" class="mobile-menu-link" @click="mobileMenuOpen = false">{{ t('landing.nav.services') }}</a>
                    <Link href="/masters" class="mobile-menu-link" @click="mobileMenuOpen = false">{{ t('landing.nav.masters') }}</Link>
                    <a href="#testimonials" class="mobile-menu-link" @click="mobileMenuOpen = false">{{ t('landing.nav.testimonials') }}</a>
                    <a href="#contact" class="mobile-menu-link" @click="mobileMenuOpen = false">{{ t('landing.nav.about') }}</a>
                    <Link v-if="canBook" href="/booking" class="mobile-menu-cta" @click="mobileMenuOpen = false">{{ t('landing.nav.bookNow') }}</Link>

                    <!-- Mobile Auth -->
                    <div class="mobile-menu-divider"></div>
                    <Link v-if="authUser" :href="dashboardUrl" class="mobile-menu-user" @click="mobileMenuOpen = false">
                        <span class="mobile-menu-avatar">{{ authUser.avatar }}</span>
                        <span>{{ authUser.name }}</span>
                    </Link>
                    <Link v-else href="/auth/login" class="mobile-menu-login" @click="mobileMenuOpen = false">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                        <span>{{ t('auth.login') }}</span>
                    </Link>
                </div>
            </Transition>
        </nav>

        <!-- Scroll Snap Container -->
        <div class="snap-container" ref="scrollContainer">

            <!-- ==================== HERO SECTION ==================== -->
            <section id="hero" class="snap-section hero-section">
                <div class="hero-bg" :style="heroImage ? { backgroundImage: `url(${heroImage})` } : {}"></div>
                <div class="hero-overlay"></div>

                <div class="hero-content-wrapper">
                    <div class="hero-center">
                        <div class="hero-badge">
                            <svg class="hero-badge-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/>
                            </svg>
                            <span>{{ heroBadge }}</span>
                        </div>

                        <h1 class="hero-headline" v-html="heroTitle.replace(/\n/g, '<br>')"></h1>

                        <p class="hero-sub" v-html="heroSubtitle.replace(/\n/g, '<br>')"></p>

                        <div class="hero-ctas">
                            <Link v-if="canBook" href="/booking" class="hero-cta-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/>
                                </svg>
                                <span>{{ heroCtaText }}</span>
                            </Link>
                            <a href="#services" class="hero-cta-secondary">
                                <span>{{ heroViewServicesText }}</span>
                            </a>
                        </div>

                        <div class="hero-stats">
                            <div class="hero-stat">
                                <span class="hero-stat-num">5000+</span>
                                <span class="hero-stat-label">{{ t('landing.stats.clients') }}</span>
                            </div>
                            <div class="hero-stat">
                                <span class="hero-stat-num">10+</span>
                                <span class="hero-stat-label">Professional masterlar</span>
                            </div>
                            <div class="hero-stat">
                                <span class="hero-stat-num">4.9</span>
                                <span class="hero-stat-label">{{ t('landing.stats.rating') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ==================== SERVICES SECTION ==================== -->
            <section id="services" class="snap-section services-section">
                <div class="section-inner">
                    <!-- Section Header -->
                    <div class="section-header">
                        <div class="section-label">
                            <span class="section-label-line"></span>
                            <span class="section-label-text">XIZMATLAR</span>
                            <span class="section-label-line"></span>
                        </div>
                        <h2 class="section-title">Massaj Turlari</h2>
                        <p class="section-subtitle">Har bir tana va ruhiy holat uchun mos massaj turini tanlang</p>
                    </div>

                    <!-- Cards Grid -->
                    <div class="services-grid">
                        <div v-for="(service, index) in services" :key="index" class="service-card">
                            <div class="service-card-img" :style="{ backgroundImage: `url(${service.image})` }"></div>
                            <div class="service-card-body">
                                <!-- Icon -->
                                <div class="service-icon-box">
                                    <!-- hand -->
                                    <svg v-if="service.icon === 'hand'" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 11V6a2 2 0 0 0-2-2a2 2 0 0 0-2 2"/><path d="M14 10V4a2 2 0 0 0-2-2a2 2 0 0 0-2 2v2"/><path d="M10 10.5V6a2 2 0 0 0-2-2a2 2 0 0 0-2 2v8"/><path d="M18 8a2 2 0 1 1 4 0v6a8 8 0 0 1-8 8h-2c-2.8 0-4.5-.86-5.99-2.34l-3.6-3.6a2 2 0 0 1 2.83-2.82L7 13"/></svg>
                                    <!-- zap -->
                                    <svg v-else-if="service.icon === 'zap'" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"/></svg>
                                    <!-- droplets -->
                                    <svg v-else-if="service.icon === 'droplets'" xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 16.3c2.2 0 4-1.83 4-4.05 0-1.16-.57-2.26-1.71-3.19S7.29 6.75 7 5.3c-.29 1.45-1.14 2.84-2.29 3.76S3 11.1 3 12.25c0 2.22 1.8 4.05 4 4.05z"/><path d="M12.56 6.6A10.97 10.97 0 0 0 14 3.02c.5 2.5 2 4.9 4 6.5s3 3.5 3 5.5a6.98 6.98 0 0 1-11.91 4.97"/></svg>
                                    <!-- flame -->
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
                                </div>
                                <h3 class="service-card-title">{{ service.name }}</h3>
                                <p class="service-card-desc">{{ service.description }}</p>
                                <div class="service-card-price">
                                    <span class="service-price-text">{{ service.price }}</span>
                                    <span class="service-time-text">{{ service.duration }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ==================== MASTERS SECTION ==================== -->
            <section id="masters" class="snap-section masters-section">
                <div class="section-inner masters-inner">
                    <!-- Header with nav arrows -->
                    <div class="masters-header">
                        <div class="masters-header-left">
                            <div class="section-label">
                                <span class="section-label-line"></span>
                                <span class="section-label-text">MASTERLAR</span>
                                <span class="section-label-line"></span>
                            </div>
                            <h2 class="section-title masters-title">Bizning Ustalarimiz</h2>
                            <p class="section-subtitle">Sertifikatlangan va tajribali massaj mutaxassislari</p>
                        </div>
                        <div class="masters-nav-arrows">
                            <Link href="/masters" class="masters-arrow masters-arrow-prev">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                            </Link>
                            <Link href="/masters" class="masters-arrow masters-arrow-next">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </Link>
                        </div>
                    </div>

                    <!-- Mobile centered header -->
                    <div class="masters-header-mobile">
                        <div class="section-label">
                            <span class="section-label-line"></span>
                            <span class="section-label-text">MASTERLAR</span>
                            <span class="section-label-line"></span>
                        </div>
                        <h2 class="section-title masters-title">Bizning Ustalarimiz</h2>
                    </div>

                    <!-- Masters Grid -->
                    <div class="masters-grid">
                        <Link
                            v-for="(master, index) in mastersList"
                            :key="index"
                            :href="master.id ? `/masters/${master.id}` : '/masters'"
                            class="master-card"
                        >
                            <div class="master-card-img">
                                <div v-if="master.photo_url" class="master-photo" :style="{ backgroundImage: `url(${master.photo_url})` }"></div>
                                <div v-else class="master-photo master-photo-placeholder">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                </div>
                            </div>
                            <div class="master-card-info">
                                <span class="master-name">{{ master.name }}</span>
                                <span class="master-specialty">{{ master.specialty }}</span>
                                <div class="master-experience">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
                                    <span>{{ master.experience }} yillik tajriba</span>
                                </div>
                                <div class="master-rating">
                                    <svg v-for="s in 5" :key="s" class="master-star" :class="{ filled: s <= getStarCount(master.rating) }" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    <span class="master-rating-text">{{ master.rating }}</span>
                                </div>
                            </div>
                        </Link>
                    </div>

                    <!-- Mobile pagination dots -->
                    <div class="masters-dots">
                        <span class="masters-dot active"></span>
                        <span class="masters-dot"></span>
                        <span class="masters-dot"></span>
                    </div>
                </div>
            </section>

            <!-- ==================== TESTIMONIALS SECTION ==================== -->
            <section id="testimonials" class="snap-section testimonials-section">
                <div class="section-inner">
                    <!-- Section Header -->
                    <div class="section-header">
                        <div class="section-label">
                            <span class="section-label-line"></span>
                            <span class="section-label-text">{{ t('landing.testimonials.badge') }}</span>
                            <span class="section-label-line"></span>
                        </div>
                        <h2 class="section-title">{{ t('landing.testimonials.title') }}</h2>
                        <p class="section-subtitle">{{ t('landing.testimonials.subtitle') }}</p>
                    </div>

                    <!-- Testimonials Grid -->
                    <div class="testimonials-grid">
                        <div v-for="(item, index) in testimonialsList" :key="index" class="testimonial-card">
                            <!-- Quote Icon -->
                            <svg class="testimonial-quote-icon" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2z"/>
                                <path d="M5 3a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2 1 1 0 0 1 1 1v1a2 2 0 0 1-2 2 1 1 0 0 0-1 1v2a1 1 0 0 0 1 1 6 6 0 0 0 6-6V5a2 2 0 0 0-2-2z"/>
                            </svg>

                            <!-- Comment Text -->
                            <p class="testimonial-text">{{ tr(item.comment) }}</p>

                            <!-- Star Rating -->
                            <div class="testimonial-stars">
                                <svg v-for="s in 5" :key="s" class="testimonial-star" :class="{ filled: s <= item.rating }" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                </svg>
                            </div>

                            <!-- Divider -->
                            <div class="testimonial-divider"></div>

                            <!-- Author Info -->
                            <div class="testimonial-author">
                                <div class="testimonial-avatar">
                                    <span class="testimonial-initials">{{ getInitials(item.client_name) }}</span>
                                </div>
                                <div class="testimonial-author-info">
                                    <span class="testimonial-author-name">{{ tr(item.client_name) }}</span>
                                    <span class="testimonial-author-role">{{ tr(item.client_role) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ==================== CONTACT + FOOTER ==================== -->
            <div class="snap-section contact-footer-wrapper">
            <section id="contact" class="contact-section">
                <div class="section-inner">
                    <!-- Section Header -->
                    <div class="section-header">
                        <div class="section-label">
                            <span class="section-label-line"></span>
                            <span class="section-label-text">{{ t('landing.contact.badge') }}</span>
                            <span class="section-label-line"></span>
                        </div>
                        <h2 class="section-title">{{ t('landing.contact.title') }}</h2>
                        <p class="section-subtitle">{{ t('landing.contact.subtitle') }}</p>
                    </div>

                    <!-- Contact Row: Form + Map/Info -->
                    <div class="contact-row">
                        <!-- Contact Form -->
                        <div class="contact-form-card">
                            <form @submit.prevent="submitContact">
                                <div class="contact-form-row">
                                    <div class="contact-field">
                                        <label class="contact-label">{{ t('landing.contact.name') }}</label>
                                        <input
                                            type="text"
                                            v-model="contactForm.name"
                                            class="contact-input"
                                            :placeholder="t('landing.contact.namePlaceholder')"
                                        />
                                    </div>
                                    <div class="contact-field">
                                        <label class="contact-label">{{ t('landing.contact.phone') }}</label>
                                        <input
                                            type="tel"
                                            v-model="contactForm.phone"
                                            class="contact-input"
                                            placeholder="+998 90 123 45 67"
                                        />
                                    </div>
                                </div>
                                <div class="contact-field">
                                    <label class="contact-label">{{ t('landing.contact.serviceType') }}</label>
                                    <div class="contact-select-wrapper">
                                        <select v-model="contactForm.service_type" class="contact-select">
                                            <option value="" disabled>{{ t('landing.contact.selectService') }}</option>
                                            <option v-for="opt in serviceOptions" :key="opt.id" :value="opt.name">{{ opt.name }}</option>
                                        </select>
                                        <svg class="contact-select-chevron" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                    </div>
                                </div>
                                <div class="contact-field">
                                    <label class="contact-label">{{ t('landing.contact.message') }}</label>
                                    <textarea
                                        v-model="contactForm.message"
                                        class="contact-textarea"
                                        :placeholder="t('landing.contact.messagePlaceholder')"
                                        rows="4"
                                    ></textarea>
                                </div>
                                <button type="submit" class="contact-submit" :disabled="contactSubmitting">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"/><path d="m21.854 2.147-10.94 10.939"/></svg>
                                    <span v-if="contactSubmitting">{{ t('common.saving') }}...</span>
                                    <span v-else-if="contactSuccess">{{ t('landing.contact.sent') }}</span>
                                    <span v-else>{{ t('landing.contact.send') }}</span>
                                </button>
                            </form>
                        </div>

                        <!-- Map & Info -->
                        <div class="contact-info-side">
                            <!-- Map Placeholder -->
                            <div class="contact-map">
                                <div class="contact-map-inner">
                                    <svg class="contact-map-pin" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#C8A951" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
                                </div>
                            </div>

                            <!-- Info Cards -->
                            <div class="contact-info-cards">
                                <div class="contact-info-card">
                                    <div class="contact-info-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
                                    </div>
                                    <div class="contact-info-text">
                                        <span class="contact-info-label">{{ t('landing.contact.address') }}</span>
                                        <span class="contact-info-value">Toshkent sh., Chilonzor tumani, 7-kvartal</span>
                                    </div>
                                </div>
                                <div class="contact-info-card">
                                    <div class="contact-info-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    </div>
                                    <div class="contact-info-text">
                                        <span class="contact-info-label">{{ t('landing.contact.phoneLabel') }}</span>
                                        <span class="contact-info-value">+998 90 123 45 67</span>
                                    </div>
                                </div>
                                <div class="contact-info-card">
                                    <div class="contact-info-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    </div>
                                    <div class="contact-info-text">
                                        <span class="contact-info-label">{{ t('landing.contact.hours') }}</span>
                                        <span class="contact-info-value">Har kuni 09:00 — 22:00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ==================== FOOTER SECTION ==================== -->
            <footer class="site-footer">
                <div class="footer-divider-top"></div>
                <div class="footer-inner">
                    <!-- Main Footer Row -->
                    <div class="footer-main">
                        <!-- Brand Column -->
                        <div class="footer-brand">
                            <div class="footer-logo">
                                <svg class="footer-logo-icon" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m3 4.5a4.5 4.5 0 1 1-4.5-4.5M12 16.5V15m4.5-3a4.5 4.5 0 1 0-4.5-4.5M16.5 12H15"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <span class="footer-logo-text">HOMEMASSAGE</span>
                            </div>
                            <p class="footer-brand-desc">{{ t('landing.footer.tagline') }}</p>
                            <div class="footer-socials">
                                <a href="#" class="footer-social" aria-label="Instagram">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
                                </a>
                                <a href="#" class="footer-social" aria-label="Facebook">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                                </a>
                                <a href="#" class="footer-social" aria-label="Telegram">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"/><path d="m21.854 2.147-10.94 10.939"/></svg>
                                </a>
                            </div>
                        </div>

                        <!-- Services Column -->
                        <div class="footer-col">
                            <h4 class="footer-col-title">{{ t('landing.nav.services') }}</h4>
                            <a href="#services" class="footer-link">Klassik Massaj</a>
                            <a href="#services" class="footer-link">Sport Massaj</a>
                            <a href="#services" class="footer-link">Aromaterapiya</a>
                            <a href="#services" class="footer-link">Tosh Massaj</a>
                        </div>

                        <!-- Company Column -->
                        <div class="footer-col">
                            <h4 class="footer-col-title">{{ t('landing.footer.company') }}</h4>
                            <a href="#" class="footer-link">{{ t('landing.nav.about') }}</a>
                            <Link href="/masters" class="footer-link">{{ t('landing.nav.masters') }}</Link>
                            <a href="#testimonials" class="footer-link">{{ t('landing.nav.testimonials') }}</a>
                            <a href="#contact" class="footer-link">{{ t('landing.footer.contact') }}</a>
                        </div>

                        <!-- Support Column -->
                        <div class="footer-col">
                            <h4 class="footer-col-title">{{ t('landing.footer.support') }}</h4>
                            <a href="#" class="footer-link">FAQ</a>
                            <a href="#" class="footer-link">{{ t('landing.footer.privacy') }}</a>
                            <a href="#" class="footer-link">{{ t('landing.footer.terms') }}</a>
                            <a href="#" class="footer-link">{{ t('landing.footer.helpCenter') }}</a>
                        </div>
                    </div>

                    <!-- Bottom Divider -->
                    <div class="footer-divider"></div>

                    <!-- Bottom Row -->
                    <div class="footer-bottom">
                        <span class="footer-copyright">&copy; 2026 HomeMassage. {{ t('landing.footer.rights') }}</span>
                        <div class="footer-made-with">
                            <span>Ishlab chiqildi</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="#C8A951" stroke="#C8A951" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                            <span>bilan</span>
                        </div>
                    </div>
                </div>
            </footer>
            </div>

        </div>
    </div>
</template>
