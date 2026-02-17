<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarGroupContent,
  SidebarGroupLabel,
  SidebarHeader,
  SidebarInset,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarProvider,
  SidebarRail,
  SidebarTrigger,
} from '@/components/ui/sidebar';
import { Separator } from '@/components/ui/separator';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';

const { t } = useI18n();
const page = usePage();
const auth = computed(() => page.props.auth);
const flash = computed(() => page.props.flash);

const isActive = (path) => {
  const url = page.url;
  if (path === '/admin/dashboard') return url === '/admin/dashboard';
  return url.startsWith(path);
};

// Menu items grouped by section
const menuSections = computed(() => [
  {
    label: t('sidebar.main'),
    items: [
      { title: t('sidebar.dashboard'), url: '/admin/dashboard', icon: 'home' },
    ],
  },
  {
    label: t('sidebar.services'),
    items: [
      { title: t('sidebar.massageTypes'), url: '/admin/service-types', icon: 'grid' },
      { title: t('sidebar.oilTypes'), url: '/admin/oils', icon: 'droplet' },
      { title: t('sidebar.standardItems'), url: '/admin/standard-items', icon: 'clipboard' },
      { title: t('sidebar.allOrders'), url: '/admin/orders', icon: 'file-text' },
      { title: t('sidebar.newOrders'), url: '/admin/orders/new', icon: 'plus-circle' },
      { title: t('sidebar.testimonials'), url: '/admin/testimonials', icon: 'message-square' },
    ],
  },
  {
    label: t('sidebar.users'),
    items: [
      { title: t('sidebar.masters'), url: '/admin/masters', icon: 'users' },
      { title: t('sidebar.dispatchers'), url: '/admin/dispatchers', icon: 'headphones' },
      { title: t('sidebar.clients'), url: '/admin/customers', icon: 'user' },
      { title: t('sidebar.ratings'), url: '/admin/ratings', icon: 'star' },
    ],
  },
  {
    label: t('sidebar.reports'),
    items: [
      { title: t('sidebar.ordersReport'), url: '/admin/reports', icon: 'bar-chart-2' },
      { title: t('sidebar.mastersReport'), url: '/admin/reports/masters', icon: 'pie-chart' },
    ],
  },
  {
    label: t('sidebar.system'),
    items: [
      { title: t('sidebar.auditLogs'), url: '/admin/audit-logs', icon: 'shield' },
      { title: t('sidebar.settings'), url: '/admin/settings', icon: 'settings' },
    ],
  },
]);

const getIcon = (name) => {
  const icons = {
    home: 'M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z M9 22V12h6v10',
    grid: 'M3 3h7v7H3z M14 3h7v7h-7z M14 14h7v7h-7z M3 14h7v7H3z',
    droplet: 'M12 2.69l5.66 5.66a8 8 0 11-11.31 0z',
    clipboard: 'M16 4h2a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2 M8.5 2h7a1.5 1.5 0 010 3h-7a1.5 1.5 0 010-3z',
    'file-text': 'M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z M14 2v6h6 M16 13H8 M16 17H8 M10 9H8',
    'plus-circle': 'M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z M12 8v8 M8 12h8',
    'message-square': 'M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z',
    users: 'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2 M9 7a4 4 0 100 8 4 4 0 000-8z M23 21v-2a4 4 0 00-3-3.87 M16 3.13a4 4 0 010 7.75',
    headphones: 'M3 18v-6a9 9 0 0118 0v6 M21 19a2 2 0 01-2 2h-1a2 2 0 01-2-2v-3a2 2 0 012-2h3z M3 19a2 2 0 002 2h1a2 2 0 002-2v-3a2 2 0 00-2-2H3z',
    user: 'M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2 M12 3a4 4 0 100 8 4 4 0 000-8z',
    star: 'M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z',
    'bar-chart-2': 'M18 20V10 M12 20V4 M6 20v-6',
    'pie-chart': 'M21.21 15.89A10 10 0 118 2.83 M22 12A10 10 0 0012 2v10z',
    shield: 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z',
    settings: 'M12 15a3 3 0 100-6 3 3 0 000 6z M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z',
  };
  return icons[name] || icons.home;
};

const logout = () => {
  // Using Inertia to post to logout
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '/admin/logout';
  const csrf = document.createElement('input');
  csrf.type = 'hidden';
  csrf.name = '_token';
  csrf.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
  form.appendChild(csrf);
  document.body.appendChild(form);
  form.submit();
};
</script>

<template>
  <SidebarProvider>
    <Sidebar collapsible="icon" class="border-r">
      <!-- Header -->
      <SidebarHeader>
        <SidebarMenu>
          <SidebarMenuItem>
            <SidebarMenuButton size="lg" as-child>
              <Link href="/admin/dashboard" class="flex items-center gap-2">
                <div class="flex aspect-square size-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                  <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                  </svg>
                </div>
                <div class="grid flex-1 text-left text-sm leading-tight">
                  <span class="truncate font-semibold">HomeMessage</span>
                  <span class="truncate text-xs text-muted-foreground">Admin Panel</span>
                </div>
              </Link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarHeader>

      <!-- Content -->
      <SidebarContent>
        <SidebarGroup v-for="section in menuSections" :key="section.label">
          <SidebarGroupLabel>{{ section.label }}</SidebarGroupLabel>
          <SidebarGroupContent>
            <SidebarMenu>
              <SidebarMenuItem v-for="item in section.items" :key="item.url">
                <SidebarMenuButton
                  as-child
                  :is-active="isActive(item.url)"
                >
                  <Link :href="item.url" class="flex items-center gap-2">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path :d="getIcon(item.icon)" />
                    </svg>
                    <span>{{ item.title }}</span>
                  </Link>
                </SidebarMenuButton>
              </SidebarMenuItem>
            </SidebarMenu>
          </SidebarGroupContent>
        </SidebarGroup>
      </SidebarContent>

      <!-- Footer -->
      <SidebarFooter>
        <SidebarMenu>
          <SidebarMenuItem>
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <SidebarMenuButton size="lg" class="data-[state=open]:bg-sidebar-accent">
                  <Avatar class="h-8 w-8 rounded-lg">
                    <AvatarFallback class="rounded-lg bg-primary text-primary-foreground">
                      {{ auth?.user?.name?.charAt(0)?.toUpperCase() || 'A' }}
                    </AvatarFallback>
                  </Avatar>
                  <div class="grid flex-1 text-left text-sm leading-tight">
                    <span class="truncate font-semibold">{{ auth?.user?.name || 'Admin' }}</span>
                    <span class="truncate text-xs text-muted-foreground">{{ auth?.user?.email || 'admin@example.com' }}</span>
                  </div>
                  <svg class="ml-auto size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 18l6-6-6-6"/>
                  </svg>
                </SidebarMenuButton>
              </DropdownMenuTrigger>
              <DropdownMenuContent side="right" align="end" class="w-56">
                <DropdownMenuItem as-child>
                  <Link href="/admin/profile" class="flex items-center gap-2">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                      <circle cx="12" cy="7" r="4"/>
                    </svg>
                    Profil
                  </Link>
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem @click="logout" class="text-destructive focus:text-destructive">
                  <svg class="size-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                    <polyline points="16,17 21,12 16,7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                  </svg>
                  Chiqish
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarFooter>

      <SidebarRail />
    </Sidebar>

    <!-- Main Content -->
    <SidebarInset>
      <!-- Header -->
      <header class="flex h-16 shrink-0 items-center gap-2 border-b px-4">
        <SidebarTrigger class="-ml-1" />
        <Separator orientation="vertical" class="mr-2 h-4" />
        <Breadcrumb>
          <BreadcrumbList>
            <BreadcrumbItem>
              <BreadcrumbLink href="/admin/dashboard">Admin</BreadcrumbLink>
            </BreadcrumbItem>
          </BreadcrumbList>
        </Breadcrumb>
      </header>

      <!-- Flash Messages -->
      <div v-if="flash?.success || flash?.error" class="px-4 pt-4">
        <div
          v-if="flash.success"
          class="rounded-lg border border-green-200 bg-green-50 p-4 text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200"
        >
          {{ flash.success }}
        </div>
        <div
          v-if="flash.error"
          class="rounded-lg border border-red-200 bg-red-50 p-4 text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200"
        >
          {{ flash.error }}
        </div>
      </div>

      <!-- Page Content -->
      <main class="flex-1 p-4">
        <slot />
      </main>

      <!-- Footer -->
      <footer class="border-t px-4 py-4 text-center text-sm text-muted-foreground">
        <strong>Copyright © 2024 <a href="/" class="text-primary hover:underline">HomeMessage</a>.</strong>
        Barcha huquqlar himoyalangan.
      </footer>
    </SidebarInset>
  </SidebarProvider>
</template>
