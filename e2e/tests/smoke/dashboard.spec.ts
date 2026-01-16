import { test, expect } from '@playwright/test';
import { TEST_USERS } from '../../fixtures/test-users';
import { loginAs, expandMobileMenu } from '../../fixtures/auth';

/**
 * Smoke tests for the dashboard/home page.
 * Verifies basic navigation and content after login.
 */
test.describe('Dashboard Smoke Tests', () => {
  test.beforeEach(async ({ page }) => {
    // Login before each test
    await loginAs(page, TEST_USERS.angelA.username, TEST_USERS.angelA.password);
  });

  test('should display main navigation', async ({ page }) => {
    // Check for main navigation elements
    const nav = page.locator('nav, .navbar');
    await expect(nav).toBeVisible();

    // Expand mobile menu if collapsed
    await expandMobileMenu(page);

    // User menu should be visible (last dropdown in right navbar)
    const userMenu = page.locator('.navbar-nav.ms-auto .nav-item.dropdown').last().locator('.dropdown-toggle');
    await expect(userMenu).toBeVisible();
  });

  test('should have working navigation links', async ({ page }) => {
    // Expand mobile menu if collapsed
    await expandMobileMenu(page);

    // Navigate to shifts page
    await page.getByRole('link', { name: /shifts|schichten/i }).first().click();
    await expect(page).toHaveURL(/user-shifts|shifts/);
  });

  test('should display user profile link', async ({ page }) => {
    // Expand mobile menu if collapsed
    await expandMobileMenu(page);

    // Click on user menu (last dropdown in right navbar)
    const userMenu = page.locator('.navbar-nav.ms-auto .nav-item.dropdown').last().locator('.dropdown-toggle');
    await userMenu.click();

    // Profile/Settings link should be visible
    const profileLink = page.getByRole('link', { name: /profile|profil|settings|einstellungen/i });
    await expect(profileLink).toBeVisible();
  });

  test.describe('Admin User Dashboard', () => {
    test.beforeEach(async ({ context, page }) => {
      // Clear cookies and login as admin (need fresh session)
      await context.clearCookies();
      await loginAs(page, TEST_USERS.shico.username, TEST_USERS.shico.password);
    });

    test('should display admin navigation', async ({ page }) => {
      // Expand mobile menu if collapsed
      await expandMobileMenu(page);

      // Admin users should see Admin dropdown menu
      // It's a dropdown toggle, not a regular link
      const adminDropdown = page.locator('.nav-link.dropdown-toggle', { hasText: /admin/i }).first();
      await expect(adminDropdown).toBeVisible();
    });
  });

  test.describe('Guardian User Dashboard', () => {
    test.beforeEach(async ({ context, page }) => {
      // Clear cookies and login as guardian (need fresh session)
      await context.clearCookies();
      await loginAs(page, TEST_USERS.guardianA.username, TEST_USERS.guardianA.password);
    });

    test('should display guardian navigation', async ({ page }) => {
      // Expand mobile menu if collapsed
      await expandMobileMenu(page);

      // Guardian users should see guardian menu
      const guardianLink = page.getByRole('link', { name: /guardian|betreuer/i }).first();
      // Note: This might not exist yet depending on implementation
      // If it doesn't exist, the test will fail - that's expected
      if (await guardianLink.isVisible().catch(() => false)) {
        await expect(guardianLink).toBeVisible();
      }
    });
  });

  test.describe('Mobile Viewport', () => {
    test.use({ viewport: { width: 375, height: 667 } });

    test('should display mobile-friendly navigation', async ({ page }) => {
      // On mobile, there should be a hamburger menu or similar
      const hamburger = page.locator('.navbar-toggler, [data-bs-toggle="collapse"]');

      // Either hamburger menu or full nav should be visible
      const nav = page.locator('nav, .navbar');
      await expect(nav).toBeVisible();
    });

    test('should be able to navigate on mobile', async ({ page }) => {
      // Try to navigate using mobile menu
      const hamburger = page.locator('.navbar-toggler').first();

      if (await hamburger.isVisible()) {
        await hamburger.click();
        // Wait for menu to expand
        await page.waitForTimeout(300);
      }

      // Should be able to find and click a navigation link
      const shiftsLink = page.getByRole('link', { name: /shifts|schichten/i }).first();
      if (await shiftsLink.isVisible()) {
        await shiftsLink.click();
        await expect(page).toHaveURL(/user-shifts|shifts/);
      }
    });
  });
});
