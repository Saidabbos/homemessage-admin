<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    show: Boolean,
    user: Object,
});

const emit = defineEmits(['close']);

const menuItems = [
    { href: '/app', icon: 'home', label: 'Bosh sahifa' },
    { href: '/app/book', icon: 'calendar', label: 'Bron qilish' },
    { href: '/app/orders', icon: 'orders', label: 'Buyurtmalarim' },
    { href: '/app/ratings', icon: 'star', label: 'Reytinglar' },
    { href: '/app/addresses', icon: 'location', label: 'Manzillarim' },
    { href: '/app/profile', icon: 'profile', label: 'Profil' },
];

const close = () => emit('close');
</script>

<template>
    <Teleport to="body">
        <!-- Overlay -->
        <Transition name="fade">
            <div v-if="show" class="sidebar-overlay" @click="close"></div>
        </Transition>

        <!-- Sidebar -->
        <Transition name="slide">
            <div v-if="show" class="sidebar">
                <!-- Header -->
                <div class="sidebar-header">
                    <div class="sidebar-logo">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m3 4.5a4.5 4.5 0 1 1-4.5-4.5M12 16.5V15m4.5-3a4.5 4.5 0 1 0-4.5-4.5M16.5 12H15"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <span>HOMEMASSAGE</span>
                    </div>
                    <button class="sidebar-close" @click="close">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"/>
                            <line x1="6" y1="6" x2="18" y2="18"/>
                        </svg>
                    </button>
                </div>

                <!-- User Info -->
                <div v-if="user" class="sidebar-user">
                    <div class="sidebar-avatar">
                        {{ user.name?.charAt(0) || user.phone?.slice(-2) }}
                    </div>
                    <div class="sidebar-user-info">
                        <span class="sidebar-user-name">{{ user.name || 'Foydalanuvchi' }}</span>
                        <span class="sidebar-user-phone">{{ user.phone }}</span>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="sidebar-nav">
                    <Link 
                        v-for="item in menuItems" 
                        :key="item.href"
                        :href="item.href"
                        class="sidebar-link"
                        @click="close"
                    >
                        <!-- Home -->
                        <svg v-if="item.icon === 'home'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                        <!-- Calendar -->
                        <svg v-else-if="item.icon === 'calendar'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                            <line x1="12" y1="14" x2="12" y2="18"/>
                            <line x1="10" y1="16" x2="14" y2="16"/>
                        </svg>
                        <!-- Orders -->
                        <svg v-else-if="item.icon === 'orders'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                        <!-- Star -->
                        <svg v-else-if="item.icon === 'star'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                        <!-- Location -->
                        <svg v-else-if="item.icon === 'location'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <!-- Profile -->
                        <svg v-else-if="item.icon === 'profile'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        <span>{{ item.label }}</span>
                    </Link>
                </nav>

                <!-- Footer -->
                <div class="sidebar-footer">
                    <Link href="/app" class="sidebar-logout" @click="$inertia.post('/app/logout')">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        <span>Chiqish</span>
                    </Link>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.sidebar-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 100;
}

.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: 280px;
    max-width: 85vw;
    background: white;
    z-index: 101;
    display: flex;
    flex-direction: column;
    box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
    transition: transform 0.25s ease;
}
.slide-enter-from,
.slide-leave-to {
    transform: translateX(-100%);
}

/* Header */
.sidebar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #f0f0f0;
}

.sidebar-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #C8A951;
}

.sidebar-logo span {
    font-family: 'Playfair Display', serif;
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 1px;
    color: #1B2B5A;
}

.sidebar-close {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f5f5f5;
    border: none;
    border-radius: 10px;
    color: #666;
    cursor: pointer;
}

/* User */
.sidebar-user {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px;
    background: linear-gradient(135deg, #1B2B5A 0%, #253672 100%);
}

.sidebar-avatar {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #C8A951 0%, #D4B76A 100%);
    border-radius: 50%;
    font-size: 18px;
    font-weight: 600;
    color: white;
}

.sidebar-user-info {
    display: flex;
    flex-direction: column;
}

.sidebar-user-name {
    font-size: 16px;
    font-weight: 600;
    color: white;
}

.sidebar-user-phone {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.7);
}

/* Navigation */
.sidebar-nav {
    flex: 1;
    padding: 16px 12px;
    overflow-y: auto;
}

.sidebar-link {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 16px;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 500;
    color: #333;
    text-decoration: none;
    transition: all 0.2s;
}

.sidebar-link:hover {
    background: #f8f5f0;
    color: #C8A951;
}

.sidebar-link svg {
    flex-shrink: 0;
    color: #888;
}

.sidebar-link:hover svg {
    color: #C8A951;
}

/* Footer */
.sidebar-footer {
    padding: 16px 20px;
    border-top: 1px solid #f0f0f0;
}

.sidebar-logout {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    width: 100%;
    background: #FEF2F2;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    color: #EF4444;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
}

.sidebar-logout:hover {
    background: #FEE2E2;
}
</style>
