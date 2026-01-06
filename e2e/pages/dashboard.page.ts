import { Page, Locator, expect } from '@playwright/test';
import { BasePage } from './base.page';

/**
 * Page Object for the Dashboard/News page (main landing page after login)
 */
export class DashboardPage extends BasePage {
  readonly path = '/';

  constructor(page: Page) {
    super(page);
  }

  // ==================== Locators ====================

  pageTitle(): Locator {
    return this.page.locator('h1, h2').first();
  }

  newsItems(): Locator {
    return this.page.locator('.news-item, .card.news, article.news');
  }

  /**
   * The news section on the dashboard - either contains news items or is the main content area
   */
  newsSection(): Locator {
    // The main content area after login - can be cards, news, or just the main container
    return this.page.locator('.container main, main .container, #content, .content, .card').first();
  }

  welcomeMessage(): Locator {
    return this.page.locator('.welcome-message, .alert-info').first();
  }

  shiftsLink(): Locator {
    return this.page.getByRole('link', { name: /shifts|schichten/i }).first();
  }

  // ==================== Actions ====================

  /**
   * Navigate to the dashboard/home page
   */
  async navigate(): Promise<void> {
    await this.goto(this.path);
    await this.waitForLoad();
  }

  /**
   * Navigate to shifts page from dashboard
   */
  async goToShifts(): Promise<void> {
    await this.expandMobileMenuIfNeeded();
    await this.shiftsLink().click();
    await this.page.waitForURL(/user-shifts|shifts/);
  }

  // ==================== Assertions ====================

  /**
   * Assert that we are on the dashboard page
   */
  async expectOnDashboard(): Promise<void> {
    await expect(this.page.locator('nav.navbar')).toBeVisible();
    // Dashboard typically shows news or welcome content
    await expect(this.page).not.toHaveURL(/\/login/);
  }

  /**
   * Assert that the user sees news items
   */
  async expectNewsVisible(): Promise<void> {
    await expect(this.newsItems().first()).toBeVisible();
  }
}
