<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { ref, computed, onMounted, onUnmounted } from 'vue'

const { t, locale } = useI18n()
const mobileMenuOpen = ref(false)
const userDropdownOpen = ref(false)
const dropdownRef = ref(null)

const page = usePage()
const user = computed(() => page.props.auth?.user)

const switchLocale = (newLocale) => {
    locale.value = newLocale
    localStorage.setItem('locale', newLocale)
}

const toggleUserDropdown = () => {
    userDropdownOpen.value = !userDropdownOpen.value
}

const closeDropdown = (e) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        userDropdownOpen.value = false
    }
}

const logout = () => {
    router.post('/customer/logout')
}

onMounted(() => {
    document.addEventListener('click', closeDropdown)
})

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown)
})
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

                <!-- User Menu (Desktop) -->
                <div v-if="user" class="user-menu desktop-only" ref="dropdownRef">
                    <button class="user-menu-btn" @click.stop="toggleUserDropdown">
                        <span class="user-avatar">{{ user.name?.charAt(0) || user.phone?.slice(-2) }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="{ rotated: userDropdownOpen }">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>
                    <div v-if="userDropdownOpen" class="user-dropdown">
                        <div class="dropdown-header">
                            <span class="dropdown-name">{{ user.name || user.phone }}</span>
                            <span class="dropdown-phone" v-if="user.name">{{ user.phone }}</span>
                        </div>
                        <div class="dropdown-divider"></div>
                        <Link href="/customer/dashboard" class="dropdown-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                            {{ t('public.nav.dashboard') }}
                        </Link>
                        <Link href="/customer/orders" class="dropdown-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                            {{ t('public.nav.orders') }}
                        </Link>
                        <Link href="/customer/profile" class="dropdown-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            {{ t('public.nav.profile') }}
                        </Link>
                        <div class="dropdown-divider"></div>
                        <button @click="logout" class="dropdown-item dropdown-item-danger">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                            {{ t('public.nav.logout') }}
                        </button>
                    </div>
                </div>

                <!-- Login Button (Desktop) -->
                <Link v-else href="/customer/login" class="login-btn desktop-only">
                    {{ t('public.nav.login') }}
                </Link>

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
                <!-- Mobile User Section -->
                <div v-if="user" class="mobile-user">
                    <div class="mobile-user-info">
                        <span class="mobile-user-avatar">{{ user.name?.charAt(0) || user.phone?.slice(-2) }}</span>
                        <span class="mobile-user-name">{{ user.name || user.phone }}</span>
                    </div>
                    <Link href="/customer/dashboard" class="mobile-link" @click="mobileMenuOpen = false">{{ t('public.nav.dashboard') }}</Link>
                    <Link href="/customer/orders" class="mobile-link" @click="mobileMenuOpen = false">{{ t('public.nav.orders') }}</Link>
                    <button @click="logout" class="mobile-link mobile-logout">{{ t('public.nav.logout') }}</button>
                </div>
                <Link v-else href="/customer/login" class="mobile-link mobile-link-login" @click="mobileMenuOpen = false">{{ t('public.nav.login') }}</Link>
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

/* User Menu */
.user-menu {
    position: relative;
}

.user-menu-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.375rem 0.75rem;
    background: rgba(0, 0, 0, 0.05);
    border: none;
    border-radius: 50px;
    cursor: pointer;
    transition: background 0.2s ease;
}

.user-menu-btn:hover {
    background: rgba(0, 0, 0, 0.1);
}

.user-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #C9A55C 0%, #D4B76A 100%);
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
    border-radius: 50%;
}

.user-menu-btn svg {
    color: #6a6a7a;
    transition: transform 0.2s ease;
}

.user-menu-btn svg.rotated {
    transform: rotate(180deg);
}

.user-dropdown {
    position: absolute;
    top: calc(100% + 0.5rem);
    right: 0;
    min-width: 220px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    z-index: 200;
}

.dropdown-header {
    padding: 1rem;
    background: rgba(0, 0, 0, 0.02);
}

.dropdown-name {
    display: block;
    font-weight: 600;
    color: #1a1a2e;
}

.dropdown-phone {
    display: block;
    font-size: 0.8125rem;
    color: #6a6a7a;
    margin-top: 0.25rem;
}

.dropdown-divider {
    height: 1px;
    background: rgba(0, 0, 0, 0.08);
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    width: 100%;
    padding: 0.75rem 1rem;
    font-size: 0.9375rem;
    color: #1a1a2e;
    text-decoration: none;
    background: none;
    border: none;
    cursor: pointer;
    transition: background 0.2s ease;
}

.dropdown-item:hover {
    background: rgba(0, 0, 0, 0.05);
}

.dropdown-item svg {
    color: #6a6a7a;
}

.dropdown-item-danger {
    color: #dc3545;
}

.dropdown-item-danger svg {
    color: #dc3545;
}

/* Login Button */
.login-btn {
    padding: 0.5rem 1rem;
    font-size: 0.9375rem;
    font-weight: 500;
    color: #1a1a2e;
    text-decoration: none;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 8px;
    transition: background 0.2s ease;
}

.login-btn:hover {
    background: rgba(0, 0, 0, 0.1);
}

/* Mobile User Section */
.mobile-user {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
}

.mobile-user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    margin-bottom: 0.5rem;
}

.mobile-user-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #C9A55C 0%, #D4B76A 100%);
    color: white;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 50%;
}

.mobile-user-name {
    font-weight: 600;
    color: #1a1a2e;
}

.mobile-logout {
    color: #dc3545;
    text-align: left;
    width: 100%;
    background: none;
    border: none;
    cursor: pointer;
}

.mobile-link-login {
    background: rgba(0, 0, 0, 0.05);
    text-align: center;
    margin-top: 1rem;
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
