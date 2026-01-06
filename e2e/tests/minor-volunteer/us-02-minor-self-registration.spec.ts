import { test, expect } from '@playwright/test';
import { LoginPage } from '../../pages/login.page';
import { DashboardPage } from '../../pages/dashboard.page';
import { TEST_USERS } from '../../fixtures/test-users';
import { loginAs } from '../../fixtures/auth';

/**
 * US-02: Minor Self-Registration
 *
 * As a minor volunteer (with guardian consent), I want to be able to register
 * myself for the event so I can participate in age-appropriate activities.
 *
 * Test scenarios:
 * 1. Minor can log in with valid credentials
 * 2. Minor can access their dashboard
 * 3. Minor sees appropriate content for their category
 * 4. Minor cannot access admin areas
 */
test.describe('US-02: Minor Self-Registration', () => {
  let loginPage: LoginPage;
  let dashboard: DashboardPage;

  test.beforeEach(async ({ page }) => {
    loginPage = new LoginPage(page);
    dashboard = new DashboardPage(page);
  });

  test('junior angel can log in', async ({ page }) => {
    const junior = TEST_USERS.minorJunior;

    await loginAs(page, junior.username, junior.password);
    await dashboard.navigate();

    await expect(dashboard.newsSection()).toBeVisible();
  });

  test('teen angel can log in', async ({ page }) => {
    const teen = TEST_USERS.minorTeen;

    await loginAs(page, teen.username, teen.password);
    await dashboard.navigate();

    await expect(dashboard.newsSection()).toBeVisible();
  });

  test('accompanying child can log in', async ({ page }) => {
    const child = TEST_USERS.minorChild;

    await loginAs(page, child.username, child.password);
    await dashboard.navigate();

    await expect(dashboard.newsSection()).toBeVisible();
  });

  test('minor cannot access admin pages', async ({ page }) => {
    const junior = TEST_USERS.minorJunior;

    await loginAs(page, junior.username, junior.password);

    // Try to access admin page
    await page.goto('/admin/minors');

    // Should be denied access - check that we don't see the admin dashboard
    // Either redirected, shown error, or access denied
    const hasError = await page.locator('.alert-danger, .error').isVisible().catch(() => false);
    const urlChanged = !page.url().includes('/admin/minors');
    const hasStatsCards = await page.locator('.card.text-bg-primary').isVisible().catch(() => false);

    // Either URL changed (redirect), has error message, or doesn't have the dashboard content
    expect(hasError || urlChanged || !hasStatsCards).toBe(true);
  });

  test('minor can view their profile', async ({ page }) => {
    const teen = TEST_USERS.minorTeen;

    await loginAs(page, teen.username, teen.password);

    // Navigate to settings
    await page.goto('/settings/profile');

    // Should be able to access profile page
    await expect(page).toHaveURL(/\/settings\/profile/);
  });

  test('minor session remains active', async ({ page }) => {
    const junior = TEST_USERS.minorJunior;

    await loginAs(page, junior.username, junior.password);

    // Navigate to dashboard
    await dashboard.navigate();
    await expect(dashboard.newsSection()).toBeVisible();

    // Navigate away and back
    await page.goto('/settings/profile');
    await dashboard.navigate();

    // Still logged in
    await expect(dashboard.newsSection()).toBeVisible();
  });

  test('invalid login is rejected', async ({ page }) => {
    await loginPage.navigate();
    await loginPage.login('test_minor_junior', 'wrongpassword');

    // Should show error or stay on login page
    const hasError = await loginPage.errorMessage().isVisible().catch(() => false);
    const stillOnLogin = await page.url().includes('/login');

    expect(hasError || stillOnLogin).toBe(true);
  });
});
