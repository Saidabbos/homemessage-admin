<script setup>
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t, locale } = useI18n()

const props = defineProps({
    serviceTypes: Array,
    masters: Array,
    stats: Object,
})

// State
const currentTestimonial = ref(0)
const scrollY = ref(0)

// Testimonials
const testimonials = [
    {
        id: 1,
        text: "Ajoyib xizmat! Master juda professional. Hozir dam olgan xis qilmoqda.",
        author: "Fatima A.",
        rating: 5
    },
    {
        id: 2,
        text: "Stressdan tola qutqildi. Tavsiya qilamiz!",
        author: "Ahmed M.",
        rating: 5
    },
    {
        id: 3,
        text: "Professional va rasmiy. Yana kelardik!",
        author: "Sarah L.",
        rating: 5
    }
]

// Methods
const nextTestimonial = () => {
    currentTestimonial.value = (currentTestimonial.value + 1) % testimonials.length
}

const prevTestimonial = () => {
    currentTestimonial.value = (currentTestimonial.value - 1 + testimonials.length) % testimonials.length
}

onMounted(() => {
    window.addEventListener('scroll', () => {
        scrollY.value = window.scrollY
    })
})
</script>

<template>
    <Head :title="t('landing.title')" />

    <div class="min-h-screen bg-gradient-to-b from-[#F0EBE2] via-[#E2DDD4] to-[#D8D3CA]">
        <!-- Navigation Header -->
        <header class="fixed top-0 left-0 right-0 z-50 backdrop-blur-xl bg-white/80 border-b border-white/20">
            <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
                <div class="text-2xl font-playfair font-bold text-[#1B2B5A] tracking-widest">
                    {{ t('landing.brand') }}
                </div>
                <nav class="hidden md:flex items-center gap-8">
                    <a href="#services" class="text-sm text-[#1B2B5A]/70 hover:text-[#C9A55C] transition font-manrope">
                        {{ t('landing.nav.services') }}
                    </a>
                    <a href="#masters" class="text-sm text-[#1B2B5A]/70 hover:text-[#C9A55C] transition font-manrope">
                        {{ t('landing.nav.masters') }}
                    </a>
                    <a href="#testimonials" class="text-sm text-[#1B2B5A]/70 hover:text-[#C9A55C] transition font-manrope">
                        {{ t('landing.nav.testimonials') }}
                    </a>
                </nav>
                <a href="/booking" class="px-6 py-2 bg-gradient-to-r from-[#C9A55C] to-[#D4B76A] text-white rounded-full text-sm font-medium hover:shadow-lg transition font-manrope">
                    {{ t('landing.nav.bookNow') }}
                </a>
            </div>
        </header>

        <!-- HERO SECTION -->
        <section class="relative pt-32 pb-20 px-6 overflow-hidden min-h-screen flex items-center justify-center">
            <div class="absolute inset-0 opacity-20" style="background-image: url('https://images.unsplash.com/photo-1709689769810-225b90f51653?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&w=1080'); background-size: cover;"></div>
            <div class="absolute inset-0 bg-gradient-to-b from-[#EDE8DF]/85 via-transparent to-[#E0DCD3]/85"></div>

            <div class="relative z-10 text-center max-w-3xl mx-auto">
                <div class="mb-6 inline-block px-4 py-2 rounded-full bg-[#C9A55C]/15 text-[#C9A55C] text-sm font-semibold tracking-wider font-manrope">
                    ✦ {{ t('landing.hero.badge') }}
                </div>
                <h1 class="text-6xl md:text-7xl font-playfair font-light text-[#1B2B5A] mb-6 leading-tight">
                    {{ t('landing.hero.title') }}
                </h1>
                <p class="text-lg text-[#1B2B5A]/70 mb-8 leading-relaxed font-manrope">
                    {{ t('landing.hero.subtitle') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="/booking" class="px-8 py-3 bg-gradient-to-r from-[#C9A55C] to-[#D4B76A] text-white rounded-full font-medium hover:shadow-xl transition">
                        {{ t('landing.hero.cta') }} →
                    </a>
                    <a href="#services" class="px-8 py-3 border border-[#1B2B5A]/20 text-[#1B2B5A] rounded-full font-medium hover:bg-white/50 transition">
                        {{ t('landing.hero.viewServices') }}
                    </a>
                </div>
            </div>
        </section>

        <!-- MASSAGE TYPES SECTION -->
        <section id="services" class="py-24 px-6 bg-gradient-to-b from-[#EDE8DF] to-[#E7E2D9]">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-16">
                    <div class="text-[#C9A55C] text-sm font-semibold tracking-widest mb-4 font-manrope">
                        {{ t('landing.services.badge') }}
                    </div>
                    <h2 class="text-5xl font-playfair text-[#1B2B5A] mb-4">
                        {{ t('landing.services.title') }}
                    </h2>
                    <p class="text-[#1B2B5A]/60 text-lg font-manrope">
                        {{ t('landing.services.subtitle') }}
                    </p>
                </div>

                <div class="grid md:grid-cols-4 gap-6">
                    <div v-for="service in (serviceTypes || [])" :key="service.id"
                        class="group backdrop-blur-2xl bg-white/40 rounded-3xl p-8 border border-white/60 hover:bg-white/60 transition duration-300 hover:shadow-xl">
                        <div v-if="service.image_url" class="w-full h-40 rounded-2xl overflow-hidden mb-4 bg-gradient-to-br from-[#C9A55C]/20 to-[#D4B76A]/20">
                            <img :src="service.image_url" :alt="service.name" class="w-full h-full object-cover group-hover:scale-110 transition duration-300" />
                        </div>
                        <h3 class="text-xl font-playfair text-[#1B2B5A] mb-3">
                            {{ service.name }}
                        </h3>
                        <p class="text-[#1B2B5A]/60 text-sm mb-4 line-clamp-2 font-manrope">
                            {{ service.description || 'Premium massage service' }}
                        </p>
                        <div v-if="service.price_range" class="text-[#C9A55C] font-semibold mb-4 font-manrope">
                            {{ service.price_range }}
                        </div>
                        <a href="/booking" class="text-[#C9A55C] text-sm font-medium hover:text-[#D4B76A] transition font-manrope">
                            {{ t('landing.services.learnMore') }} →
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- MASTERS SECTION -->
        <section id="masters" class="py-24 px-6 bg-gradient-to-b from-[#E5E0D7] via-[#DDD8CF] to-[#D5D0C7]">
            <div class="max-w-6xl mx-auto">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-16 gap-8">
                    <div>
                        <div class="text-[#C9A55C] text-sm font-semibold tracking-widest mb-4 font-manrope">
                            {{ t('landing.masters.badge') }}
                        </div>
                        <h2 class="text-5xl font-playfair text-[#1B2B5A] mb-4">
                            {{ t('landing.masters.title') }}
                        </h2>
                        <p class="text-[#1B2B5A]/60 text-lg font-manrope">
                            {{ t('landing.masters.subtitle') }}
                        </p>
                    </div>
                </div>

                <div class="grid md:grid-cols-4 gap-6">
                    <a v-for="master in (masters || [])" :key="master.id"
                        :href="`/masters/${master.id}`"
                        class="group backdrop-blur-2xl bg-white/35 rounded-3xl overflow-hidden border border-white/50 hover:bg-white/50 transition duration-300 hover:shadow-xl">
                        <div class="aspect-square overflow-hidden bg-gradient-to-br from-[#C9A55C]/30 to-[#D4B76A]/30">
                            <img v-if="master.photo" :src="'/storage/' + master.photo" :alt="master.full_name"
                                class="w-full h-full object-cover group-hover:scale-105 transition duration-300" />
                            <div v-else class="w-full h-full flex items-center justify-center text-4xl font-serif text-[#C9A55C]/30">
                                {{ master.full_name.charAt(0) }}
                            </div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-lg font-playfair text-[#1B2B5A] mb-2">
                                {{ master.full_name }}
                            </h3>
                            <p class="text-[#1B2B5A]/60 text-sm mb-4 line-clamp-2 font-manrope">
                                {{ master.bio && master.bio[locale] || t('landing.masters.defaultBio') }}
                            </p>
                            <div class="flex items-center justify-between">
                                <div class="text-[#C9A55C]">★★★★★</div>
                                <span class="text-[#C9A55C] font-semibold text-sm font-manrope">
                                    {{ t('landing.masters.viewProfile') }} →
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </section>

        <!-- TESTIMONIALS SECTION -->
        <section id="testimonials" class="py-24 px-6 bg-gradient-to-b from-[#EFE9E0] to-[#E8E3DA]">
            <div class="max-w-5xl mx-auto">
                <div class="text-center mb-16">
                    <div class="text-[#C9A55C] text-sm font-semibold tracking-widest mb-4 font-manrope">
                        {{ t('landing.testimonials.badge') }}
                    </div>
                    <h2 class="text-5xl font-playfair text-[#1B2B5A] mb-4">
                        {{ t('landing.testimonials.title') }}
                    </h2>
                    <p class="text-[#1B2B5A]/60 text-lg font-manrope">
                        {{ t('landing.testimonials.subtitle') }}
                    </p>
                </div>

                <div class="backdrop-blur-2xl bg-white/40 rounded-3xl p-12 border border-white/60 mb-8">
                    <div class="flex items-center gap-1 mb-6">
                        <span v-for="i in testimonials[currentTestimonial].rating" :key="i" class="text-2xl text-[#C9A55C]">★</span>
                    </div>
                    <p class="text-2xl font-playfair text-[#1B2B5A] mb-8 leading-relaxed">
                        "{{ testimonials[currentTestimonial].text }}"
                    </p>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#C9A55C] to-[#D4B76A] flex items-center justify-center text-white font-bold text-lg">
                            {{ testimonials[currentTestimonial].author.charAt(0) }}
                        </div>
                        <div>
                            <div class="font-semibold text-[#1B2B5A] font-manrope">
                                {{ testimonials[currentTestimonial].author }}
                            </div>
                            <div class="text-[#1B2B5A]/60 text-sm font-manrope">
                                {{ t('landing.testimonials.verified') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-center gap-2">
                    <button v-for="(_, index) in testimonials" :key="index"
                        @click="currentTestimonial = index"
                        :class="['w-3 h-3 rounded-full transition', index === currentTestimonial ? 'bg-[#C9A55C]' : 'bg-[#C9A55C]/30']"
                        :aria-label="`Testimonial ${index + 1}`" />
                </div>
            </div>
        </section>

        <!-- CONTACT SECTION -->
        <section class="py-24 px-6 bg-gradient-to-b from-[#E3DED5] via-[#DBD6CD] to-[#D3CEC5]">
            <div class="max-w-5xl mx-auto">
                <div class="text-center mb-16">
                    <div class="text-[#C9A55C] text-sm font-semibold tracking-widest mb-4 font-manrope">
                        ALOQA
                    </div>
                    <h2 class="text-5xl font-playfair text-[#1B2B5A] mb-4">
                        Biz Bilan Bog'laning
                    </h2>
                    <p class="text-[#1B2B5A]/60 text-lg font-manrope">
                        Savollaringiz bormi? Biz bilan bog'laning yoki to'g'ridan-to'g'ri bron qiling
                    </p>
                </div>

                <div class="grid md:grid-cols-2 gap-8">
                    <!-- Contact Info -->
                    <div class="space-y-6">
                        <div class="backdrop-blur-2xl bg-white/40 rounded-3xl p-8 border border-white/60">
                            <div class="text-2xl mb-2">📍</div>
                            <h3 class="font-semibold text-[#1B2B5A] mb-2 font-manrope">Manzil</h3>
                            <p class="text-[#1B2B5A]/70 font-manrope">Tashkent, O'zbekiston</p>
                        </div>
                        <div class="backdrop-blur-2xl bg-white/40 rounded-3xl p-8 border border-white/60">
                            <div class="text-2xl mb-2">📞</div>
                            <h3 class="font-semibold text-[#1B2B5A] mb-2 font-manrope">Telefon</h3>
                            <a href="tel:+998901234567" class="text-[#C9A55C] hover:text-[#D4B76A] font-manrope">+998 90 123 45 67</a>
                        </div>
                        <div class="backdrop-blur-2xl bg-white/40 rounded-3xl p-8 border border-white/60">
                            <div class="text-2xl mb-2">✉️</div>
                            <h3 class="font-semibold text-[#1B2B5A] mb-2 font-manrope">Email</h3>
                            <a href="mailto:info@goldentouch.uz" class="text-[#C9A55C] hover:text-[#D4B76A] font-manrope">info@goldentouch.uz</a>
                        </div>
                    </div>

                    <!-- CTA Card -->
                    <div class="backdrop-blur-2xl bg-white/40 rounded-3xl p-12 border border-white/60 flex flex-col justify-center">
                        <h3 class="text-2xl font-playfair text-[#1B2B5A] mb-4">
                            Barchasini Boshlab Bering
                        </h3>
                        <p class="text-[#1B2B5A]/60 mb-8 font-manrope">
                            Bugun birinchi seansni band qiling va premium dam olishni hiss qiling
                        </p>
                        <a href="/booking" class="w-full px-8 py-3 bg-gradient-to-r from-[#C9A55C] to-[#D4B76A] text-white rounded-full font-medium hover:shadow-xl transition text-center font-manrope">
                            Seansni Band Qiling 📅
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- FOOTER SECTION -->
        <footer class="bg-gradient-to-b from-[#1B2B5A] to-[#152348] text-white py-20 px-6">
            <div class="max-w-7xl mx-auto">
                <!-- Footer Content -->
                <div class="grid md:grid-cols-4 gap-12 mb-12 pb-12 border-b border-white/10">
                    <div>
                        <div class="text-2xl font-playfair font-bold text-[#C9A55C] mb-4 tracking-widest">
                            GOLDEN TOUCH
                        </div>
                        <p class="text-white/60 text-sm leading-relaxed font-manrope">
                            Hashamat va tinchlik uyg'unligi. Premium massaj xizmatlari.
                        </p>
                    </div>

                    <div>
                        <h4 class="font-semibold mb-4 font-manrope">Navigatsiya</h4>
                        <ul class="space-y-2 text-sm text-white/70">
                            <li><a href="#services" class="hover:text-[#C9A55C] transition font-manrope">{{ t('landing.nav.services') }}</a></li>
                            <li><a href="#masters" class="hover:text-[#C9A55C] transition font-manrope">{{ t('landing.nav.masters') }}</a></li>
                            <li><a href="#testimonials" class="hover:text-[#C9A55C] transition font-manrope">{{ t('landing.nav.testimonials') }}</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-semibold mb-4 font-manrope">Hukuqiy</h4>
                        <ul class="space-y-2 text-sm text-white/70">
                            <li><a href="#" class="hover:text-[#C9A55C] transition font-manrope">Shartlar</a></li>
                            <li><a href="#" class="hover:text-[#C9A55C] transition font-manrope">Maxfiylik</a></li>
                            <li><a href="#" class="hover:text-[#C9A55C] transition font-manrope">Cookies</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-semibold mb-4 font-manrope">Ijtimoiy</h4>
                        <ul class="space-y-2 text-sm text-white/70">
                            <li><a href="#" class="hover:text-[#C9A55C] transition font-manrope">Instagram</a></li>
                            <li><a href="#" class="hover:text-[#C9A55C] transition font-manrope">Facebook</a></li>
                            <li><a href="#" class="hover:text-[#C9A55C] transition font-manrope">Telegram</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Copyright -->
                <div class="text-center text-sm text-white/50 font-manrope">
                    <p>© 2026 Golden Touch. {{ t('landing.footer.rights') }}</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap');

:root {
    --font-playfair: 'Playfair Display', serif;
    --font-manrope: 'Manrope', sans-serif;
}

.font-playfair {
    font-family: var(--font-playfair);
}

.font-manrope {
    font-family: var(--font-manrope);
}
</style>
