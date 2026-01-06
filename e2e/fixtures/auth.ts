import { test as base, Page, expect } from '@playwright/test';
import { TEST_USERS, TEST_PASSWORD, TestUserKey } from './test-users';
import { LoginPage } from '../pages/login.page';

/**
 * Authentication fixture and helpers for E2E tests
 */

/**
 * Expand mobile hamburger menu if collapsed
 * Returns true if menu was expanded, false if already expanded or no hamburger
 */
export async function expandMobileMenu(page: Page): Promise<boolean> {
  const hamburger = page.locator('.navbar-toggler').first();
  const navbarCollapse = page.locator('.navbar-collapse');

  // Check if hamburger is visible (mobile viewport)
  if (await hamburger.isVisible().catch(() => false)) {
    // Check if navbar is collapsed
    const isCollapsed = await navbarCollapse.evaluate((el) => !el.classList.contains('show'));
    if (isCollapsed) {
      await hamburger.click();
      // Wait for menu to expand
      await page.waitForTimeout(300);
      return true;
    }
  }
  return false;
}

/**
 * Login as a specific user
 */
export async function loginAs(page: Page, username: string, password: string = TEST_PASSWORD): Promise<void> {
  const loginPage = new LoginPage(page);
  await loginPage.loginAs(username, password);
}

/**
 * Login as a test user by key
 */
export async function loginAsTestUser(page: Page, userKey: TestUserKey): Promise<void> {
  const user = TEST_USERS[userKey];
  await loginAs(page, user.username, user.password);
}

/**
 * Logout current user
 * Works on both desktop and mobile viewports
 */
export async function logout(page: Page): Promise<void> {
  // Expand hamburger menu if on mobile viewport
  await expandMobileMenu(page);

  // Click on user menu (the last dropdown in the right navbar)
  const userMenu = page.locator('.navbar-nav.ms-auto .nav-item.dropdown').last().locator('.dropdown-toggle');
  await userMenu.click();

  // Click logout link
  await page.getByRole('link', { name: /logout|abmelden/i }).click();

  // Wait for redirect to login page
  await page.waitForURL(/\/login/);
}

/**
 * Check if user is currently logged in
 * Works on both desktop and mobile viewports by checking:
 * 1. URL is not /login
 * 2. Navbar is present
 * 3. No visible login link in nav (indicates logged out state)
 */
export async function isLoggedIn(page: Page): Promise<boolean> {
  try {
    // If on login page, not logged in
    if (page.url().includes('/login')) {
      return false;
    }

    // Check if navbar exists
    const navbar = page.locator('nav.navbar');
    await navbar.waitFor({ state: 'visible', timeout: 2000 });

    // On mobile, expand menu to check for login link
    await expandMobileMenu(page);

    // If there's a visible login link in nav, user is NOT logged in
    const loginLink = navbar.getByRole('link', { name: /^login$|^anmelden$/i });
    const hasLoginLink = await loginLink.isVisible().catch(() => false);

    return !hasLoginLink;
  } catch {
    return false;
  }
}

/**
 * Ensure user is logged out
 */
export async function ensureLoggedOut(page: Page): Promise<void> {
  if (await isLoggedIn(page)) {
    await logout(page);
  }
}

// ==================== Extended Test Fixture ====================

/**
 * Extended test fixture with authentication helpers
 */
export const test = base.extend<{
  loginAs: (username: string, password?: string) => Promise<void>;
  loginAsTestUser: (userKey: TestUserKey) => Promise<void>;
  logout: () => Promise<void>;
  authenticatedPage: Page;
}>({
  /**
   * Helper to login as specific user
   */
  loginAs: async ({ page }, use) => {
    await use(async (username: string, password?: string) => {
      await loginAs(page, username, password);
    });
  },

  /**
   * Helper to login as test user by key
   */
  loginAsTestUser: async ({ page }, use) => {
    await use(async (userKey: TestUserKey) => {
      await loginAsTestUser(page, userKey);
    });
  },

  /**
   * Helper to logout
   */
  logout: async ({ page }, use) => {
    await use(async () => {
      await logout(page);
    });
  },

  /**
   * Page that is pre-authenticated as test_angel_a
   */
  authenticatedPage: async ({ page }, use) => {
    await loginAsTestUser(page, 'angelA');
    await use(page);
  },
});

export { expect } from '@playwright/test';
