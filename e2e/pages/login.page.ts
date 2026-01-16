import { Page, Locator, expect } from '@playwright/test';
import { BasePage } from './base.page';

/**
 * Page Object for the Login page
 */
export class LoginPage extends BasePage {
  readonly path = '/login';

  constructor(page: Page) {
    super(page);
  }

  // ==================== Locators ====================

  usernameInput(): Locator {
    return this.page.locator('input[name="login"]');
  }

  passwordInput(): Locator {
    return this.page.locator('input[name="password"]');
  }

  loginButton(): Locator {
    return this.page.getByRole('button', { name: /login|anmelden/i });
  }

  errorMessage(): Locator {
    return this.page.locator('.alert-danger');
  }

  // ==================== Actions ====================

  /**
   * Navigate to the login page
   */
  async navigate(): Promise<void> {
    await this.goto(this.path);
    await this.waitForLoad();
  }

  /**
   * Perform login with given credentials
   */
  async login(username: string, password: string): Promise<void> {
    await this.usernameInput().fill(username);
    await this.passwordInput().fill(password);
    await this.loginButton().click();
  }

  /**
   * Full login flow: navigate and login
   */
  async loginAs(username: string, password: string): Promise<void> {
    await this.navigate();
    await this.login(username, password);
    // Wait for redirect after successful login
    await this.page.waitForURL(/\/(news|dashboard|user-shifts)/, { timeout: 10000 });
  }

  // ==================== Assertions ====================

  /**
   * Assert that login failed with an error message
   */
  async expectLoginError(message?: string): Promise<void> {
    await expect(this.errorMessage()).toBeVisible();
    if (message) {
      await expect(this.errorMessage()).toContainText(message);
    }
  }

  /**
   * Assert that login was successful (redirected away from login page)
   * Note: Only checks URL redirect. Use expectUserMenuVisible() for desktop viewport assertions.
   */
  async expectLoginSuccess(): Promise<void> {
    await expect(this.page).not.toHaveURL(/\/login/);
    // Check navbar is present (works on both mobile and desktop)
    await expect(this.page.locator('nav.navbar')).toBeVisible();
  }

  /**
   * Assert that user menu is visible (desktop viewports only - navbar is collapsed on mobile)
   */
  async expectUserMenuVisible(): Promise<void> {
    await expect(this.userMenu()).toBeVisible();
  }

  /**
   * Assert that we are on the login page
   */
  async expectOnLoginPage(): Promise<void> {
    await expect(this.usernameInput()).toBeVisible();
    await expect(this.passwordInput()).toBeVisible();
    await expect(this.loginButton()).toBeVisible();
  }
}
