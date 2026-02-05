<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Head } from '@inertiajs/vue3'

defineProps({
    serviceTypes: Array,
    masters: Array,
    stats: Object,
})

// Testimonials data
const testimonials = [
    {
        id: 1,
        text: "The most transformative spa experience I've ever had. My body and mind felt completely renewed after just one session. The ambiance, the technique — everything was flawless.",
        author: "Sophia R.",
        role: "Regular Guest",
        rating: 5,
    },
    {
        id: 2,
        text: "Every visit feels like a retreat. The aromatherapy massage is absolutely divine — I leave feeling like a completely new person every single time.",
        author: "James M.",
        role: "Wellness Enthusiast",
        rating: 5,
    },
]

// Services for ticker
const tickerServices = [
    'DEEP TISSUE',
    'HOT STONE',
    'AROMATHERAPY',
    'LUXURY FACIAL',
    'THAI MASSAGE',
    'BODY SCRUB',
]

// Current section for scroll snap
const currentSection = ref(0)
const isScrolling = ref(false)

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

// Scroll to section
const scrollToSection = (index) => {
    const sections = document.querySelectorAll('.snap-section')
    if (sections[index]) {
        sections[index].scrollIntoView({ behavior: 'smooth' })
    }
}
</script>

<template>
    <Head title="Golden Touch - Premium Wellness Experience" />
    
    <div class="snap-container">
        <!-- Navigation -->
        <nav class="fixed top-0 left-0 right-0 z-50 glass-nav">
            <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
                <div class="text-xl font-serif text-gold tracking-wider">GOLDEN TOUCH</div>
                <div class="hidden md:flex items-center gap-8 text-sm text-gray-700">
                    <a href="#services" class="hover:text-gold transition-colors">Services</a>
                    <a href="#about" class="hover:text-gold transition-colors">About</a>
                    <a href="#testimonials" class="hover:text-gold transition-colors">Testimonials</a>
                    <a href="#contact" class="hover:text-gold transition-colors">Contact</a>
                </div>
                <a href="#book" class="btn-primary">Book Now →</a>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="snap-section hero-section">
            <div class="hero-bg"></div>
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <span class="hero-badge">
                    ✦ Premium Wellness Experience
                </span>
                <h1 class="hero-title">
                    Restore Your<br>Inner Peace
                </h1>
                <p class="hero-subtitle">
                    Luxury massage & spa treatments designed to rejuvenate your<br>
                    body, calm your mind, and awaken your spirit.
                </p>
                <div class="hero-buttons">
                    <a href="#book" class="btn-primary">Book Your Session →</a>
                    <a href="#services" class="btn-secondary">View Services</a>
                </div>
                <div class="scroll-indicator">
                    <span class="scroll-dot"></span>
                </div>
            </div>
        </section>

        <!-- Services Ticker -->
        <section class="ticker-section">
            <div class="ticker-wrapper">
                <div class="ticker-content">
                    <template v-for="i in 3" :key="i">
                        <span v-for="service in tickerServices" :key="`${i}-${service}`" class="ticker-item">
                            {{ service }} <span class="ticker-plus">+</span>
                        </span>
                    </template>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section id="services" class="snap-section services-section">
            <div class="section-content">
                <span class="section-badge">SPA SERVICES</span>
                <h2 class="section-title">Tailored Treatments</h2>
                <p class="section-subtitle">
                    Each session is crafted to your unique needs, combining ancient<br>
                    techniques with modern luxury.
                </p>
                
                <div class="services-grid">
                    <div class="service-card glass-card">
                        <div class="service-icon">💆</div>
                        <h3 class="service-name">Deep Tissue Massage</h3>
                        <p class="service-desc">Targeted pressure therapy for chronic tension relief and muscle recovery.</p>
                        <a href="#book" class="service-link">Learn More →</a>
                    </div>
                    <div class="service-card glass-card">
                        <div class="service-icon">🪨</div>
                        <h3 class="service-name">Hot Stone Therapy</h3>
                        <p class="service-desc">Heated basalt stones melt away stress and restore natural balance.</p>
                        <a href="#book" class="service-link">Learn More →</a>
                    </div>
                    <div class="service-card glass-card">
                        <div class="service-icon">🌸</div>
                        <h3 class="service-name">Aromatherapy</h3>
                        <p class="service-desc">Essential oil-infused massage for deep relaxation and holistic healing.</p>
                        <a href="#book" class="service-link">Learn More →</a>
                    </div>
                    <div class="service-card glass-card">
                        <div class="service-icon">✨</div>
                        <h3 class="service-name">Luxury Facial</h3>
                        <p class="service-desc">Premium skincare ritual for radiant, youthful glow and lasting results.</p>
                        <a href="#book" class="service-link">Learn More →</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sanctuary Section -->
        <section id="about" class="snap-section sanctuary-section">
            <div class="sanctuary-content">
                <div class="sanctuary-image">
                    <img src="/images/sauna.jpg" alt="Sanctuary" class="sanctuary-img" />
                </div>
                <div class="sanctuary-text">
                    <span class="section-badge">WHY SERENITY</span>
                    <h2 class="sanctuary-title">
                        A Sanctuary of<br>Pure Relaxation
                    </h2>
                    <p class="sanctuary-desc">
                        Step into a world where every detail is designed for your comfort. Our master
                        therapists combine time-honored traditions with cutting-edge techniques to deliver
                        personalized treatments that truly transform.
                    </p>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <span class="stat-number">12+</span>
                            <span class="stat-label">Years Experience</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">5K+</span>
                            <span class="stat-label">Happy Clients</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">4.9</span>
                            <span class="stat-label">Avg. Rating</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section id="testimonials" class="snap-section testimonials-section">
            <div class="section-content">
                <span class="section-badge">TESTIMONIALS</span>
                <h2 class="section-title">What Our Guests Say</h2>
                
                <div class="testimonials-grid">
                    <div v-for="testimonial in testimonials" :key="testimonial.id" class="testimonial-card glass-card">
                        <div class="testimonial-stars">
                            <span v-for="i in testimonial.rating" :key="i">☆</span>
                        </div>
                        <p class="testimonial-text">"{{ testimonial.text }}"</p>
                        <div class="testimonial-author">
                            <div class="author-avatar">{{ testimonial.author[0] }}</div>
                            <div>
                                <div class="author-name">{{ testimonial.author }}</div>
                                <div class="author-role">{{ testimonial.role }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Special Offer Banner -->
        <section class="offer-section">
            <div class="offer-content glass-card-dark">
                <div class="offer-icon">🎁</div>
                <div class="offer-text">
                    <h3 class="offer-title">First Visit Special — 20% Off</h3>
                    <p class="offer-desc">Use code SERENITY20 at booking to claim your exclusive discount.</p>
                </div>
                <a href="#book" class="btn-primary-light">Claim Offer →</a>
            </div>
        </section>

        <!-- CTA Section -->
        <section id="book" class="snap-section cta-section">
            <div class="cta-bg"></div>
            <div class="cta-content">
                <span class="section-badge-light">READY TO UNWIND?</span>
                <h2 class="cta-title">Begin Your Journey to Bliss</h2>
                <p class="cta-subtitle">
                    Book your first session today and receive 20% off any signature<br>treatment.
                </p>
                <div class="cta-buttons">
                    <a href="/book" class="btn-primary-large">Reserve Your Spot 📅</a>
                    <a href="tel:+15552345678" class="btn-phone">📞 +1 (555) 234-5678</a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer-section">
            <div class="footer-content">
                <div class="footer-brand">
                    <div class="footer-logo">GOLDEN TOUCH</div>
                    <p class="footer-tagline">
                        Where luxury meets tranquility. We offer<br>
                        premium massage and spa treatments designed<br>
                        to restore your body and calm your mind.
                    </p>
                    <div class="footer-social">
                        <a href="#" class="social-link">📘</a>
                        <a href="#" class="social-link">📸</a>
                        <a href="#" class="social-link">🐦</a>
                    </div>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <a href="#">Services</a>
                    <a href="#">About Us</a>
                    <a href="#">Testimonials</a>
                    <a href="#">Gift Cards</a>
                </div>
                <div class="footer-links">
                    <h4>Services</h4>
                    <a href="#">Deep Tissue</a>
                    <a href="#">Hot Stone</a>
                    <a href="#">Aromatherapy</a>
                    <a href="#">Luxury Facial</a>
                </div>
                <div class="footer-contact">
                    <h4>Contact</h4>
                    <p>📍 123 Wellness Avenue,<br>Beverly Hills, CA</p>
                    <p>📞 +1 (555) 234-5678</p>
                    <p>🕐 Mon-Sun: 9 AM - 9 PM</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2024 Golden Touch Spa. All rights reserved.</p>
                <div class="footer-legal">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Custom Properties */
:root {
    --gold: #C9A55C;
    --gold-light: #D4B76A;
    --navy: #1a1a2e;
    --cream: #faf8f5;
    --cream-dark: #f5f0e8;
}

/* Scroll Snap Container */
.snap-container {
    height: 100vh;
    overflow-y: auto;
    scroll-snap-type: y mandatory;
    scroll-behavior: smooth;
}

.snap-section {
    min-height: 100vh;
    scroll-snap-align: start;
    scroll-snap-stop: always;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 0.8s ease, transform 0.8s ease;
}

.snap-section.section-visible {
    opacity: 1;
    transform: translateY(0);
}

/* Glass Effects */
.glass-nav {
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.3);
}

.glass-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.5);
    border-radius: 24px;
    padding: 2rem;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.glass-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.glass-card-dark {
    background: rgba(26, 26, 46, 0.9);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 24px;
    color: white;
}

/* Buttons */
.btn-primary {
    background: linear-gradient(135deg, #C9A55C 0%, #D4B76A 100%);
    color: white;
    padding: 0.875rem 1.75rem;
    border-radius: 50px;
    font-weight: 500;
    font-size: 0.875rem;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
}

.btn-primary:hover {
    transform: scale(1.05);
    box-shadow: 0 10px 30px rgba(201, 165, 92, 0.4);
}

.btn-primary-large {
    background: linear-gradient(135deg, #C9A55C 0%, #D4B76A 100%);
    color: white;
    padding: 1.125rem 2.5rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1rem;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
}

.btn-primary-light {
    background: linear-gradient(135deg, #C9A55C 0%, #D4B76A 100%);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 50px;
    font-weight: 500;
    font-size: 0.875rem;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-secondary {
    background: transparent;
    color: #1a1a2e;
    padding: 0.875rem 1.75rem;
    border-radius: 50px;
    font-weight: 500;
    font-size: 0.875rem;
    border: 1px solid rgba(26, 26, 46, 0.2);
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
}

.btn-secondary:hover {
    background: rgba(26, 26, 46, 0.05);
}

.btn-phone {
    color: white;
    font-weight: 500;
    text-decoration: none;
    transition: opacity 0.3s ease;
}

.btn-phone:hover {
    opacity: 0.8;
}

/* Hero Section */
.hero-section {
    background: linear-gradient(180deg, #e8f4f8 0%, #d4e8ed 100%);
    flex-direction: column;
    padding-top: 80px;
}

.hero-bg {
    position: absolute;
    inset: 0;
    background-image: url('/images/hero-mountains.jpg');
    background-size: cover;
    background-position: center bottom;
    opacity: 0.9;
}

.hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(255,255,255,0.3) 0%, rgba(255,255,255,0.1) 50%, transparent 100%);
}

.hero-content {
    position: relative;
    z-index: 10;
    text-align: center;
    padding: 2rem;
}

.hero-badge {
    display: inline-block;
    background: rgba(201, 165, 92, 0.15);
    color: #C9A55C;
    padding: 0.5rem 1.25rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    margin-bottom: 1.5rem;
}

.hero-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.5rem, 8vw, 4.5rem);
    font-weight: 400;
    color: #1a1a2e;
    line-height: 1.1;
    margin-bottom: 1.5rem;
}

.hero-subtitle {
    color: #4a4a5a;
    font-size: 1.125rem;
    line-height: 1.7;
    margin-bottom: 2rem;
}

.hero-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.scroll-indicator {
    position: absolute;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
}

.scroll-dot {
    display: block;
    width: 8px;
    height: 8px;
    background: #C9A55C;
    border-radius: 50%;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(10px); }
}

/* Ticker Section */
.ticker-section {
    background: linear-gradient(135deg, #C9A55C 0%, #D4B76A 100%);
    padding: 1rem 0;
    overflow: hidden;
}

.ticker-wrapper {
    width: 100%;
    overflow: hidden;
}

.ticker-content {
    display: flex;
    animation: ticker 30s linear infinite;
    white-space: nowrap;
}

.ticker-item {
    color: white;
    font-size: 0.875rem;
    font-weight: 500;
    letter-spacing: 0.15em;
    padding: 0 2rem;
}

.ticker-plus {
    opacity: 0.5;
    margin-left: 2rem;
}

@keyframes ticker {
    0% { transform: translateX(0); }
    100% { transform: translateX(-33.33%); }
}

/* Services Section */
.services-section {
    background: var(--cream);
    padding: 6rem 2rem;
}

.section-content {
    max-width: 1200px;
    width: 100%;
    text-align: center;
}

.section-badge {
    display: inline-block;
    color: #C9A55C;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.2em;
    margin-bottom: 1rem;
}

.section-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2rem, 5vw, 3rem);
    font-weight: 400;
    color: #1a1a2e;
    margin-bottom: 1rem;
}

.section-subtitle {
    color: #6a6a7a;
    font-size: 1rem;
    line-height: 1.7;
    margin-bottom: 3rem;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1.5rem;
    text-align: left;
}

.service-card {
    padding: 2rem;
}

.service-icon {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.service-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.25rem;
    font-weight: 500;
    color: #1a1a2e;
    margin-bottom: 0.75rem;
}

.service-desc {
    color: #6a6a7a;
    font-size: 0.9rem;
    line-height: 1.6;
    margin-bottom: 1rem;
}

.service-link {
    color: #C9A55C;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    transition: opacity 0.3s ease;
}

.service-link:hover {
    opacity: 0.7;
}

/* Sanctuary Section */
.sanctuary-section {
    background: var(--cream-dark);
    padding: 4rem 2rem;
}

.sanctuary-content {
    max-width: 1200px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
}

.sanctuary-image {
    position: relative;
    border-radius: 24px;
    overflow: hidden;
}

.sanctuary-img {
    width: 100%;
    height: 500px;
    object-fit: cover;
}

.sanctuary-text {
    padding: 2rem 0;
}

.sanctuary-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2rem, 4vw, 2.75rem);
    font-weight: 400;
    color: #1a1a2e;
    line-height: 1.2;
    margin-bottom: 1.5rem;
}

.sanctuary-desc {
    color: #6a6a7a;
    font-size: 1rem;
    line-height: 1.8;
    margin-bottom: 2.5rem;
}

.stats-grid {
    display: flex;
    gap: 3rem;
}

.stat-item {
    text-align: center;
}

.stat-number {
    display: block;
    font-family: 'Playfair Display', serif;
    font-size: 2.5rem;
    font-weight: 400;
    color: #C9A55C;
}

.stat-label {
    color: #6a6a7a;
    font-size: 0.875rem;
}

/* Testimonials Section */
.testimonials-section {
    background: var(--cream);
    padding: 6rem 2rem;
}

.testimonials-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    max-width: 900px;
    margin: 0 auto;
    text-align: left;
}

.testimonial-card {
    padding: 2rem;
}

.testimonial-stars {
    color: #C9A55C;
    font-size: 1.25rem;
    margin-bottom: 1rem;
}

.testimonial-text {
    color: #4a4a5a;
    font-size: 1rem;
    line-height: 1.7;
    font-style: italic;
    margin-bottom: 1.5rem;
}

.testimonial-author {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.author-avatar {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #C9A55C 0%, #D4B76A 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
}

.author-name {
    font-weight: 600;
    color: #1a1a2e;
}

.author-role {
    color: #6a6a7a;
    font-size: 0.875rem;
}

/* Offer Section */
.offer-section {
    background: linear-gradient(135deg, #f5f0e8 0%, #ebe5db 100%);
    padding: 3rem 2rem;
}

.offer-content {
    max-width: 1000px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 2rem;
    padding: 2rem 3rem;
    flex-wrap: wrap;
}

.offer-icon {
    font-size: 2.5rem;
}

.offer-text {
    flex: 1;
}

.offer-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.offer-desc {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.9rem;
}

/* CTA Section */
.cta-section {
    background: linear-gradient(180deg, #2a3a4a 0%, #1a2a3a 100%);
    flex-direction: column;
    text-align: center;
    color: white;
}

.cta-bg {
    position: absolute;
    inset: 0;
    background-image: url('/images/cta-bg.jpg');
    background-size: cover;
    background-position: center;
    opacity: 0.3;
}

.cta-content {
    position: relative;
    z-index: 10;
    padding: 2rem;
}

.section-badge-light {
    display: inline-block;
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.2em;
    margin-bottom: 1rem;
}

.cta-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2rem, 6vw, 3.5rem);
    font-weight: 400;
    margin-bottom: 1rem;
}

.cta-subtitle {
    color: rgba(255, 255, 255, 0.7);
    font-size: 1.125rem;
    line-height: 1.7;
    margin-bottom: 2rem;
}

.cta-buttons {
    display: flex;
    gap: 2rem;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
}

/* Footer */
.footer-section {
    background: #0a0a0f;
    color: white;
    padding: 4rem 2rem 2rem;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    gap: 3rem;
}

.footer-logo {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    letter-spacing: 0.1em;
    color: #C9A55C;
    margin-bottom: 1rem;
}

.footer-tagline {
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.9rem;
    line-height: 1.7;
    margin-bottom: 1.5rem;
}

.footer-social {
    display: flex;
    gap: 1rem;
}

.social-link {
    font-size: 1.25rem;
    transition: transform 0.3s ease;
}

.social-link:hover {
    transform: scale(1.2);
}

.footer-links h4,
.footer-contact h4 {
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
}

.footer-links a {
    display: block;
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.9rem;
    margin-bottom: 0.75rem;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-links a:hover {
    color: #C9A55C;
}

.footer-contact p {
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.9rem;
    margin-bottom: 0.75rem;
}

.footer-bottom {
    max-width: 1200px;
    margin: 3rem auto 0;
    padding-top: 2rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.footer-bottom p {
    color: rgba(255, 255, 255, 0.4);
    font-size: 0.875rem;
}

.footer-legal {
    display: flex;
    gap: 2rem;
}

.footer-legal a {
    color: rgba(255, 255, 255, 0.4);
    font-size: 0.875rem;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-legal a:hover {
    color: rgba(255, 255, 255, 0.7);
}

/* Responsive */
@media (max-width: 1024px) {
    .sanctuary-content {
        grid-template-columns: 1fr;
    }
    
    .footer-content {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 768px) {
    .stats-grid {
        gap: 1.5rem;
    }
    
    .footer-content {
        grid-template-columns: 1fr;
    }
    
    .footer-bottom {
        flex-direction: column;
        text-align: center;
    }
}

/* Animation Classes */
.fade-in {
    animation: fadeIn 0.8s ease forwards;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
