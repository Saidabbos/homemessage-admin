<script setup>
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { ref } from 'vue'

const { t, locale } = useI18n()
const mobileMenuOpen = ref(false)

const switchLocale = (newLocale) => {
    locale.value = newLocale
    localStorage.setItem('locale', newLocale)
}
</script>

<template>
    <div class="public-layout">
        <!-- Navigation -->
        <nav class="public-nav">
            <div class="nav-container">
                <Link href="/" class="nav-logo">
                    <span class="logo-text">Home Massage</span>
                </Link>

                <!-- Desktop Menu -->
                <div class="nav-links desktop-only">
                    <Link href="/services" class="nav-link">{{ t('public.nav.services') }}</Link>
                    <Link href="/masters" class="nav-link">{{ t('public.nav.masters') }}</Link>
                    <Link href="/booking" class="nav-link nav-link-cta">{{ t('common.book_now') }}</Link>
                </div>

                <!-- Language Switcher -->
                <div class="lang-switcher desktop-only">
                    <button 
                        @click="switchLocale('uz')" 
                        class="lang-btn"
                        :class="{ active: locale === 'uz' }"
                    >UZ</button>
                    <button 
                        @click="switchLocale('ru')" 
                        class="lang-btn"
                        :class="{ active: locale === 'ru' }"
                    >RU</button>
                </div>

                <!-- Mobile Menu Toggle -->
                <button class="mobile-menu-toggle mobile-only" @click="mobileMenuOpen = !mobileMenuOpen">
                    <svg v-if="!mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div v-if="mobileMenuOpen" class="mobile-menu">
                <Link href="/services" class="mobile-link" @click="mobileMenuOpen = false">{{ t('public.nav.services') }}</Link>
                <Link href="/masters" class="mobile-link" @click="mobileMenuOpen = false">{{ t('public.nav.masters') }}</Link>
                <Link href="/booking" class="mobile-link mobile-link-cta" @click="mobileMenuOpen = false">{{ t('common.book_now') }}</Link>
                <div class="mobile-lang">
                    <button @click="switchLocale('uz')" class="lang-btn" :class="{ active: locale === 'uz' }">O'zbekcha</button>
                    <button @click="switchLocale('ru')" class="lang-btn" :class="{ active: locale === 'ru' }">Русский</button>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="public-main">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="public-footer">
            <div class="footer-container">
                <div class="footer-brand">
                    <span class="footer-logo">Home Massage</span>
                    <p class="footer-tagline">{{ t('public.footer.tagline') }}</p>
                </div>
                
                <div class="footer-links">
                    <div class="footer-column">
                        <h4>{{ t('public.footer.navigation') }}</h4>
                        <Link href="/services">{{ t('public.nav.services') }}</Link>
                        <Link href="/masters">{{ t('public.nav.masters') }}</Link>
                        <Link href="/booking">{{ t('common.book_now') }}</Link>
                    </div>
                    <div class="footer-column">
                        <h4>{{ t('public.footer.contact') }}</h4>
                        <a href="tel:+998901234567">+998 90 123 45 67</a>
                        <a href="https://t.me/h_m_UZ_bot" target="_blank">Telegram</a>
                    </div>
                </div>

                <div class="footer-bottom">
                    <p>&copy; 2026 Home Massage. {{ t('public.footer.rights') }}</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.public-layout {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* Navigation */
.public-nav {
    position: sticky;
    top: 0;
    z-index: 100;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.nav-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    max-width: 1200px;
    margin: 0 auto;
    padding: 1rem 1.5rem;
}

.nav-logo {
    text-decoration: none;
}

.logo-text {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    font-weight: 600;
    color: #1a1a2e;
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.nav-link {
    font-size: 0.9375rem;
    font-weight: 500;
    color: #6a6a7a;
    text-decoration: none;
    transition: color 0.2s ease;
}

.nav-link:hover {
    color: #1a1a2e;
}

.nav-link-cta {
    padding: 0.625rem 1.25rem;
    background: linear-gradient(135deg, #C9A55C 0%, #D4B76A 100%);
    color: white !important;
    border-radius: 50px;
}

.nav-link-cta:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(201, 165, 92, 0.3);
}

.lang-switcher {
    display: flex;
    gap: 0.25rem;
    padding: 0.25rem;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 8px;
}

.lang-btn {
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: #6a6a7a;
    background: transparent;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.lang-btn.active {
    background: white;
    color: #1a1a2e;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Mobile Menu */
.mobile-menu-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: rgba(0, 0, 0, 0.05);
    border: none;
    border-radius: 10px;
    color: #1a1a2e;
    cursor: pointer;
}

.mobile-menu {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding: 1rem 1.5rem 1.5rem;
    border-top: 1px solid rgba(0, 0, 0, 0.05);
}

.mobile-link {
    display: block;
    padding: 0.75rem 1rem;
    font-size: 1rem;
    font-weight: 500;
    color: #1a1a2e;
    text-decoration: none;
    border-radius: 10px;
    transition: background 0.2s ease;
}

.mobile-link:hover {
    background: rgba(0, 0, 0, 0.05);
}

.mobile-link-cta {
    background: linear-gradient(135deg, #C9A55C 0%, #D4B76A 100%);
    color: white;
    text-align: center;
}

.mobile-lang {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.mobile-lang .lang-btn {
    flex: 1;
    padding: 0.75rem;
    font-size: 0.875rem;
}

/* Show/Hide based on viewport */
.desktop-only {
    display: none;
}

.mobile-only {
    display: flex;
}

@media (min-width: 768px) {
    .desktop-only {
        display: flex;
    }
    .mobile-only {
        display: none;
    }
}

/* Main Content */
.public-main {
    flex: 1;
}

/* Footer */
.public-footer {
    background: #1a1a2e;
    color: white;
    padding: 3rem 1.5rem 1.5rem;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
}

.footer-brand {
    margin-bottom: 2rem;
}

.footer-logo {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    font-weight: 600;
}

.footer-tagline {
    margin-top: 0.5rem;
    font-size: 0.9375rem;
    opacity: 0.7;
}

.footer-links {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 2rem;
    margin-bottom: 2rem;
}

.footer-column h4 {
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.footer-column a {
    display: block;
    color: white;
    text-decoration: none;
    font-size: 0.9375rem;
    margin-bottom: 0.5rem;
    opacity: 0.8;
    transition: opacity 0.2s ease;
}

.footer-column a:hover {
    opacity: 1;
}

.footer-bottom {
    padding-top: 1.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    text-align: center;
}

.footer-bottom p {
    font-size: 0.875rem;
    opacity: 0.5;
}

@media (min-width: 768px) {
    .footer-links {
        grid-template-columns: repeat(4, 1fr);
    }
}
</style>
