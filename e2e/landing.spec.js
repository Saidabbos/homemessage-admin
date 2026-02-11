import { test, expect } from '@playwright/test';

test.describe('Landing Page', () => {
  test('should load landing page', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveTitle(/Welcome|HomeMessage/i);
  });

  test('should show hero section', async ({ page }) => {
    await page.goto('/');
    await page.waitForSelector('[data-page]');
    
    // Check for hero content
    await expect(page.locator('text=Home Massage').first()).toBeVisible({ timeout: 10000 });
    await expect(page.locator('text=/booking|massage|wellness/i').first()).toBeVisible({ timeout: 10000 });
  });

  test('should show stats section', async ({ page }) => {
    await page.goto('/');
    await page.waitForSelector('[data-page]');
    
    // Check for stats (Years, Clients, Rating)
    await expect(page.locator('text=/years|clients|rating/i').first()).toBeVisible({ timeout: 10000 });
  });

  test('should navigate to masters page', async ({ page }) => {
    await page.goto('/masters');
    await page.waitForLoadState('networkidle');
    await expect(page.locator('h1, h2').first()).toBeVisible({ timeout: 10000 });
  });

  test('should have booking button', async ({ page }) => {
    await page.goto('/');
    await page.waitForSelector('[data-page]');
    
    // Check for booking link
    const bookingLink = page.locator('a[href="/booking"]');
    await expect(bookingLink).toBeVisible({ timeout: 10000 });
  });
});

test.describe('Admin Login', () => {
  test('should show admin login page', async ({ page }) => {
    await page.goto('/admin/login');
    await expect(page.locator('input[type="email"], input[name="email"]')).toBeVisible();
    await expect(page.locator('input[type="password"]')).toBeVisible();
  });

  test('should login with valid credentials', async ({ page }) => {
    await page.goto('/admin/login');
    await page.fill('input[type="email"], input[name="email"]', 'admin@example.com');
    await page.fill('input[type="password"]', 'password');
    await page.click('button[type="submit"]');
    
    await page.waitForURL(/dashboard/, { timeout: 10000 });
    await expect(page.locator('text=Dashboard').first()).toBeVisible();
  });
});

test.describe('Admin Orders', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/admin/login');
    await page.fill('input[type="email"], input[name="email"]', 'admin@example.com');
    await page.fill('input[type="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL(/dashboard/, { timeout: 10000 });
  });

  test('should show orders list', async ({ page }) => {
    await page.goto('/admin/orders');
    await expect(page.locator('table').first()).toBeVisible({ timeout: 10000 });
  });

  test('should have status filter cards', async ({ page }) => {
    await page.goto('/admin/orders');
    // Should show status count cards (Yangi, Tasdiqlangan, etc.)
    await expect(page.locator('text=Yangi').first()).toBeVisible({ timeout: 10000 });
  });
});

test.describe('Mini App', () => {
  test('should redirect to login for unauthenticated users', async ({ page }) => {
    await page.goto('/app');
    await page.waitForLoadState('networkidle');
    // Should be on login page or show login form
    await expect(page).toHaveURL(/login/);
  });
});

test.describe('API Endpoints', () => {
  test('slots API should return data', async ({ request }) => {
    const response = await request.get('/api/slots/multi-master?date=2026-02-15&duration=60&master_ids=1');
    expect(response.ok()).toBeTruthy();
    const data = await response.json();
    expect(data.success).toBe(true);
    expect(Array.isArray(data.data?.slots)).toBe(true);
  });
});
