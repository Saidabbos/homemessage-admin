<script setup>
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
    serviceTypes: Array,
    masters: Array,
    stats: Object,
    content: {
        type: Object,
        default: () => ({})
    }
})

// Carousel state
const currentMasterSlide = ref(0)
const currentTestimonialSlide = ref(0)

// Sample testimonials data
const testimonials = ref([
    {
        id: 1,
        text: "Ajoyib xizmat! Master juda professional va attentive. Bir hafta o'tdi lekin hali ham rahvosh xis qilmoqda.",
        author: "Fatima A.",
        rating: 5
    },
    {
        id: 2,
        text: "Stressdan qutqildi. Tavsiya qilamiz hamjamalarimizga. Saqlanib qoling, eng yaxshi massaj xizmatidirlar!",
        author: "Ahmed M.",
        rating: 5
    },
    {
        id: 3,
        text: "Perfect booking experience. Very professional team and clean facilities. Will definitely come back!",
        author: "Sarah L.",
        rating: 5
    }
])

// Navigate carousel
const nextMasters = () => {
    if (props.masters && props.masters.length > 0) {
        currentMasterSlide.value = (currentMasterSlide.value + 1) % props.masters.length
    }
}

const prevMasters = () => {
    if (props.masters && props.masters.length > 0) {
        currentMasterSlide.value = (currentMasterSlide.value - 1 + props.masters.length) % props.masters.length
    }
}

const nextTestimonial = () => {
    currentTestimonialSlide.value = (currentTestimonialSlide.value + 1) % testimonials.value.length
}

const prevTestimonial = () => {
    currentTestimonialSlide.value = (currentTestimonialSlide.value - 1 + testimonials.value.length) % testimonials.value.length
}

// Intersection Observer for animations
onMounted(() => {
    const sections = document.querySelectorAll('.snap-section')

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('section-visible')
            }
        })
    }, { threshold: 0.3 })

    sections.forEach((section) => observer.observe(section))
})
</script>

<template>
    <Head :title="t('landing.title')" />
    
    <div class="snap-container">
        <!-- Navigation -->
        <nav class="fixed top-0 left-0 right-0 z-50 glass-nav">
            <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
                <div class="text-xl font-serif text-gold tracking-wider">{{ t('landing.brand') }}</div>
                <div class="hidden md:flex items-center gap-8 text-sm text-gray-700">
                    <a href="#services" class="hover:text-gold transition-colors">{{ t('landing.nav.services') }}</a>
                    <a href="#masters" class="hover:text-gold transition-colors">{{ t('landing.nav.masters') }}</a>
                    <a href="#about" class="hover:text-gold transition-colors">{{ t('landing.nav.about') }}</a>
                    <a href="#testimonials" class="hover:text-gold transition-colors">{{ t('landing.nav.testimonials') }}</a>
                </div>
                <a href="#book" class="btn-primary">{{ t('landing.nav.bookNow') }} →</a>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="snap-section hero-section">
            <div class="hero-bg"></div>
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <span class="hero-badge">
                    ✦ {{ t('landing.hero.badge') }}
                </span>
                <h1 class="hero-title" v-html="t('landing.hero.title')"></h1>
                <p class="hero-subtitle">{{ t('landing.hero.subtitle') }}</p>
                <div class="hero-buttons">
                    <a href="#book" class="btn-primary">{{ t('landing.hero.cta') }} →</a>
                    <a href="#services" class="btn-secondary">{{ t('landing.hero.viewServices') }}</a>
                </div>
                <div class="scroll-indicator">
                    <span class="scroll-dot"></span>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section id="services" class="snap-section services-section">
            <div class="section-content">
                <span class="section-badge">{{ t('landing.services.badge') }}</span>
                <h2 class="section-title">{{ t('landing.services.title') }}</h2>
                <p class="section-subtitle">{{ t('landing.services.subtitle') }}</p>

                <div class="services-grid">
                    <div
                        v-for="service in (serviceTypes && serviceTypes.length > 0 ? serviceTypes : [])"
                        :key="service.id"
                        class="service-card glass-card"
                    >
                        <div class="service-icon">
                            <img v-if="service.image_url" :src="service.image_url" :alt="service.name" class="service-image" />
                            <span v-else>{{ service.icon || '💆' }}</span>
                        </div>
                        <h3 class="service-name">{{ service.name }}</h3>
                        <p class="service-desc">{{ service.description || 'Premium massage service' }}</p>
                        <div v-if="service.price_range" class="service-price">{{ service.price_range }}</div>
                        <a href="#book" class="service-link">{{ t('landing.services.learnMore') }} →</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Masters Section -->
        <section id="masters" class="snap-section masters-section">
            <div class="section-content">
                <span class="section-badge">{{ t('landing.masters.badge') }}</span>
                <h2 class="section-title">{{ t('landing.masters.title') }}</h2>
                <p class="section-subtitle">{{ t('landing.masters.subtitle') }}</p>

                <div v-if="masters && masters.length > 0">
                    <!-- Responsive Masters Grid -->
                    <div class="masters-grid">
                        <div
                            v-for="master in masters"
                            :key="master.id"
                            class="master-card glass-card"
                        >
                            <div class="master-photo">
                                <img
                                    :src="master.photo ? '/storage/' + master.photo : '/images/master-placeholder.svg'"
                                    :alt="master.full_name"
                                    class="master-img"
                                />
                            </div>
                            <div class="master-info">
                                <h3 class="master-name">{{ master.full_name }}</h3>
                                <p class="master-bio">{{ master.bio ? master.bio[0] : t('landing.masters.defaultBio') }}</p>
                                <div class="master-rating">
                                    <span class="rating-stars">★★★★★</span>
                                    <span class="rating-value">{{ master.completed_orders || 0 }} ta buyurtma</span>
                                </div>
                                <a :href="`/masters/${master.id}`" class="master-link">{{ t('landing.masters.viewProfile') }} →</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="empty-state">
                    <p>{{ t('landing.masters.empty') }}</p>
                </div>
            </div>
        </section>

        <!-- Sanctuary Section -->
        <section id="about" class="snap-section sanctuary-section">
            <div class="sanctuary-content">
                <div class="sanctuary-image">
                    <img src="/images/sauna.jpg" :alt="t('landing.sanctuary.imageAlt')" class="sanctuary-img" />
                </div>
                <div class="sanctuary-text">
                    <span class="section-badge">{{ t('landing.sanctuary.badge') }}</span>
                    <h2 class="sanctuary-title" v-html="t('landing.sanctuary.title')"></h2>
                    <p class="sanctuary-desc">{{ t('landing.sanctuary.description') }}</p>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <span class="stat-number">{{ stats?.years || 12 }}+</span>
                            <span class="stat-label">{{ t('landing.stats.years') }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">{{ stats?.clients || '5K' }}+</span>
                            <span class="stat-label">{{ t('landing.stats.clients') }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">{{ stats?.rating || 4.9 }}</span>
                            <span class="stat-label">{{ t('landing.stats.rating') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="snap-section testimonials-section">
            <div class="section-content">
                <span class="section-badge">{{ t('landing.testimonials.badge') }}</span>
                <h2 class="section-title">{{ t('landing.testimonials.title') }}</h2>
                <p class="section-subtitle">{{ t('landing.testimonials.subtitle') }}</p>

                <div v-if="testimonials.length > 0" class="testimonials-container">
                    <div class="testimonial-card glass-card">
                        <div class="testimonial-stars">
                            <span v-for="i in testimonials[currentTestimonialSlide].rating" :key="i">★</span>
                        </div>
                        <p class="testimonial-text">{{ testimonials[currentTestimonialSlide].text }}</p>
                        <div class="testimonial-author">
                            <div class="author-avatar">
                                {{ testimonials[currentTestimonialSlide].author.charAt(0) }}
                            </div>
                            <div>
                                <div class="author-name">{{ testimonials[currentTestimonialSlide].author }}</div>
                                <div class="author-role">{{ t('landing.testimonials.verified') }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Dots -->
                    <div class="testimonial-nav">
                        <button
                            v-for="(_, index) in testimonials"
                            :key="index"
                            :class="['nav-dot', { active: index === currentTestimonialSlide }]"
                            @click="currentTestimonialSlide = index"
                            :aria-label="`Testimonial ${index + 1}`"
                        ></button>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section id="book" class="snap-section cta-section">
            <div class="cta-bg"></div>
            <div class="cta-content">
                <span class="section-badge-light">{{ t('landing.cta.badge') }}</span>
                <h2 class="cta-title">{{ t('landing.cta.title') }}</h2>
                <p class="cta-subtitle">{{ t('landing.cta.subtitle') }}</p>
                <div class="cta-buttons">
                    <a href="/book" class="btn-primary-large">{{ t('landing.cta.button') }} 📅</a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer-section">
            <div class="footer-content">
                <div class="footer-brand">
                    <div class="footer-logo">{{ t('landing.brand') }}</div>
                    <p class="footer-tagline">{{ t('landing.footer.tagline') }}</p>
                </div>
                <div class="footer-links">
                    <h4>{{ t('landing.footer.quickLinks') }}</h4>
                    <a href="#services">{{ t('landing.nav.services') }}</a>
                    <a href="#about">{{ t('landing.nav.about') }}</a>
                    <a href="#masters">{{ t('landing.nav.masters') }}</a>
                </div>
                <div class="footer-contact">
                    <h4>{{ t('landing.footer.contact') }}</h4>
                    <p>📍 Tashkent, Uzbekistan</p>
                    <p>📞 +998 90 123 45 67</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2024 Golden Touch. {{ t('landing.footer.rights') }}</p>
            </div>
        </footer>
    </div>
</template>
