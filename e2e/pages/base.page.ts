import { Page, Locator, expect } from '@playwright/test';

/**
 * Base Page Object class that all page objects extend.
 * Provides common navigation, actions, and assertions.
 */
export abstract class BasePage {
  protected readonly page: Page;

  constructor(page: Page) {
    this.page = page;
  }

  // ==================== Navigation ====================

  /**
   * Navigate to a path relative to baseURL
   */
  async goto(path: string = ''): Promise<void> {
    await this.page.goto(path);
  }

  /**
   * Wait for the page to fully load
   */
  async waitForLoad(): Promise<void> {
    await this.page.waitForLoadState('networkidle');
  }

  /**
   * Wait for navigation to complete
   */
  async waitForNavigation(): Promise<void> {
    await this.page.waitForLoadState('domcontentloaded');
  }

  // ==================== Common Locators ====================

  /**
   * Get the page title element
   */
  pageTitle(): Locator {
    return this.page.locator('h1').first();
  }

  /**
   * Get the user menu button (dropdown with username in the right navbar)
   * The user dropdown is in the ms-auto section and shows the username
   */
  userMenu(): Locator {
    // The user menu is a dropdown in the right-side navbar (.ms-auto)
    // that contains user-specific links like logout
    return this.page.locator('.navbar-nav.ms-auto .nav-item.dropdown').last().locator('.dropdown-toggle');
  }

  /**
   * Get alert/notification elements
   */
  alerts(type?: 'success' | 'danger' | 'warning' | 'info'): Locator {
    if (type) {
      return this.page.locator(`.alert-${type}`);
    }
    return this.page.locator('.alert');
  }

  // ==================== Common Actions ====================

  /**
   * Click a button by its text content
   */
  async clickButton(text: string): Promise<void> {
    await this.page.getByRole('button', { name: text }).click();
  }

  /**
   * Click a link by its text content
   */
  async clickLink(text: string): Promise<void> {
    await this.page.getByRole('link', { name: text }).click();
  }

  /**
   * Fill an input field by its label
   */
  async fillByLabel(label: string, value: string): Promise<void> {
    await this.page.getByLabel(label).fill(value);
  }

  /**
   * Fill an input field by its name attribute
   */
  async fillByName(name: string, value: string): Promise<void> {
    await this.page.locator(`input[name="${name}"]`).fill(value);
  }

  /**
   * Select an option from a dropdown by label
   */
  async selectByLabel(label: string, option: string): Promise<void> {
    await this.page.getByLabel(label).selectOption(option);
  }

  /**
   * Check or uncheck a checkbox by label
   */
  async setCheckbox(label: string, checked: boolean): Promise<void> {
    const checkbox = this.page.getByLabel(label);
    if (checked) {
      await checkbox.check();
    } else {
      await checkbox.uncheck();
    }
  }

  // ==================== Common Assertions ====================

  /**
   * Assert that the page title contains expected text
   */
  async expectPageTitle(expected: string): Promise<void> {
    await expect(this.pageTitle()).toContainText(expected);
  }

  /**
   * Assert that a success alert is visible
   */
  async expectSuccess(message?: string): Promise<void> {
    const alert = this.alerts('success');
    await expect(alert).toBeVisible();
    if (message) {
      await expect(alert).toContainText(message);
    }
  }

  /**
   * Assert that an error alert is visible
   */
  async expectError(message?: string): Promise<void> {
    const alert = this.alerts('danger');
    await expect(alert).toBeVisible();
    if (message) {
      await expect(alert).toContainText(message);
    }
  }

  /**
   * Assert that a warning alert is visible
   */
  async expectWarning(message?: string): Promise<void> {
    const alert = this.alerts('warning');
    await expect(alert).toBeVisible();
    if (message) {
      await expect(alert).toContainText(message);
    }
  }

  /**
   * Assert that an info alert is visible
   */
  async expectInfo(message?: string): Promise<void> {
    const alert = this.alerts('info');
    await expect(alert).toBeVisible();
    if (message) {
      await expect(alert).toContainText(message);
    }
  }

  /**
   * Assert that the current URL matches expected pattern
   */
  async expectUrl(pattern: string | RegExp): Promise<void> {
    if (typeof pattern === 'string') {
      await expect(this.page).toHaveURL(new RegExp(pattern));
    } else {
      await expect(this.page).toHaveURL(pattern);
    }
  }

  // ==================== Utility Methods ====================

  /**
   * Expand mobile hamburger menu if collapsed (mobile viewport)
   * Returns true if menu was expanded, false if already expanded or not mobile
   */
  async expandMobileMenuIfNeeded(): Promise<boolean> {
    const hamburger = this.page.locator('.navbar-toggler').first();
    const navbarCollapse = this.page.locator('.navbar-collapse');

    // Check if hamburger is visible (mobile viewport)
    if (await hamburger.isVisible().catch(() => false)) {
      // Check if navbar is collapsed
      const isCollapsed = await navbarCollapse.evaluate((el) => !el.classList.contains('show'));
      if (isCollapsed) {
        await hamburger.click();
        // Wait for menu to expand
        await this.page.waitForTimeout(300);
        return true;
      }
    }
    return false;
  }

  /**
   * Take a screenshot with a descriptive name
   */
  async screenshot(name: string): Promise<void> {
    await this.page.screenshot({ path: `test-results/screenshots/${name}.png` });
  }

  /**
   * Wait for a specific amount of time (use sparingly)
   */
  async wait(ms: number): Promise<void> {
    await this.page.waitForTimeout(ms);
  }

  /**
   * Check if user is logged in by looking for user menu
   */
  async isLoggedIn(): Promise<boolean> {
    try {
      await this.userMenu().waitFor({ state: 'visible', timeout: 2000 });
      return true;
    } catch {
      return false;
    }
  }
}
