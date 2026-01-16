import { test, expect } from '@playwright/test';
import { LoginPage } from '../../pages/login.page';
import { TEST_USERS, TEST_PASSWORD } from '../../fixtures/test-users';
import { loginAs, logout, isLoggedIn, expandMobileMenu } from '../../fixtures/auth';

/**
 * Smoke tests for login functionality.
 * These tests verify the basic authentication flow works correctly.
 */
test.describe('Login Smoke Tests', () => {
  let loginPage: LoginPage;

  test.beforeEach(async ({ page }) => {
    loginPage = new LoginPage(page);
  });

  test('should display login page elements', async ({ page }) => {
    await loginPage.navigate();

    // Verify all login form elements are visible
    await loginPage.expectOnLoginPage();

    // Check for login form
    await expect(loginPage.usernameInput()).toBeVisible();
    await expect(loginPage.passwordInput()).toBeVisible();
    await expect(loginPage.loginButton()).toBeVisible();
  });

  test('should login successfully with valid credentials', async ({ page }, testInfo) => {
    const user = TEST_USERS.angelA;

    await loginPage.navigate();
    await loginPage.login(user.username, user.password);

    // Should redirect away from login page
    await loginPage.expectLoginSuccess();

    // User menu should be visible on desktop viewport (1440px) - only check on desktop projects
    const isDesktop = testInfo.project.name.includes('desktop');
    if (isDesktop) {
      await loginPage.expectUserMenuVisible();
    }
  });

  test('should show error with invalid credentials', async ({ page }) => {
    await loginPage.navigate();
    await loginPage.login('invalid_user', 'wrong_password');

    // Should stay on login page with error
    await loginPage.expectLoginError();
    await expect(loginPage.usernameInput()).toBeVisible();
  });

  test('should show error with empty credentials', async ({ page }) => {
    await loginPage.navigate();

    // Try to submit without entering credentials
    await loginPage.loginButton().click();

    // Should show validation error or stay on page
    await expect(loginPage.usernameInput()).toBeVisible();
  });

  test('should logout successfully', async ({ page }) => {
    const user = TEST_USERS.angelA;

    // First login
    await loginAs(page, user.username, user.password);

    // Verify logged in
    expect(await isLoggedIn(page)).toBe(true);

    // Logout
    await logout(page);

    // Should be redirected to login page
    await expect(page).toHaveURL(/\/login/);
  });

  test('should show error when accessing protected page while logged out', async ({ context, page }) => {
    // Clear all cookies to ensure fresh session
    await context.clearCookies();

    // Try to access a protected page without being logged in
    await page.goto('/user-shifts');

    // Expand mobile menu if collapsed
    await expandMobileMenu(page);

    // Engelsystem shows a 404 page with login prompt instead of redirecting
    // Check that we're not logged in (login link visible in navbar)
    await expect(page.locator('nav').getByRole('link', { name: /login/i })).toBeVisible();

    // Check that the 404 error is shown
    await expect(page.locator('.error-big')).toBeVisible();
  });

  test.describe('Different User Types', () => {
    test('should login as admin (ShiCo)', async ({ page }) => {
      const user = TEST_USERS.shico;

      await loginAs(page, user.username, user.password);
      await loginPage.expectLoginSuccess();
    });

    test('should login as guardian', async ({ page }) => {
      const user = TEST_USERS.guardianA;

      await loginAs(page, user.username, user.password);
      await loginPage.expectLoginSuccess();
    });

    test('should login as minor (Junior Angel)', async ({ page }) => {
      const user = TEST_USERS.minorJunior;

      await loginAs(page, user.username, user.password);
      await loginPage.expectLoginSuccess();
    });

    test('should login as minor (Teen Angel)', async ({ page }) => {
      const user = TEST_USERS.minorTeen;

      await loginAs(page, user.username, user.password);
      await loginPage.expectLoginSuccess();
    });

    test('should login as supervisor', async ({ page }) => {
      const user = TEST_USERS.supervisorA;

      await loginAs(page, user.username, user.password);
      await loginPage.expectLoginSuccess();
    });
  });

  test.describe('Mobile Viewport', () => {
    test.use({ viewport: { width: 375, height: 667 } });

    test('should display login form correctly on mobile', async ({ page }) => {
      await loginPage.navigate();
      await loginPage.expectOnLoginPage();

      // All elements should be visible and accessible
      await expect(loginPage.usernameInput()).toBeVisible();
      await expect(loginPage.passwordInput()).toBeVisible();
      await expect(loginPage.loginButton()).toBeVisible();
    });

    test('should login successfully on mobile', async ({ page }) => {
      const user = TEST_USERS.angelA;

      await loginPage.navigate();
      await loginPage.login(user.username, user.password);
      await loginPage.expectLoginSuccess();
    });
  });
});
