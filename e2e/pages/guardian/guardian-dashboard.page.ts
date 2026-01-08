import { Page, Locator, expect } from '@playwright/test';
import { BasePage } from '../base.page';

/**
 * Page Object for the Guardian Dashboard
 * Shows list of linked minors and actions for guardians
 */
export class GuardianDashboardPage extends BasePage {
  readonly path = '/guardian';

  constructor(page: Page) {
    super(page);
  }

  // ==================== Locators ====================

  pageTitle(): Locator {
    return this.page.locator('h1');
  }

  registerMinorButton(): Locator {
    return this.page.getByRole('link', { name: /register|registrier/i }).first();
  }

  linkExistingButton(): Locator {
    return this.page.getByRole('link', { name: /link|verknüpf/i }).first();
  }

  minorCards(): Locator {
    return this.page.locator('.card');
  }

  minorCardByName(name: string): Locator {
    return this.page.locator('.card', { hasText: name });
  }

  noMinorsMessage(): Locator {
    return this.page.locator('.alert-info');
  }

  // Card elements
  minorName(card: Locator): Locator {
    return card.locator('.card-header strong');
  }

  primaryBadge(card: Locator): Locator {
    return card.locator('.badge.bg-primary');
  }

  categoryBadge(card: Locator): Locator {
    return card.locator('.badge.bg-info');
  }

  consentApproved(card: Locator): Locator {
    return card.locator('.text-success');
  }

  consentPending(card: Locator): Locator {
    return card.locator('.text-warning');
  }

  viewButton(card: Locator): Locator {
    return card.getByRole('link', { name: /view|ansehen/i });
  }

  editButton(card: Locator): Locator {
    return card.getByRole('link', { name: /edit|bearbeiten/i });
  }

  consentFormButton(card: Locator): Locator {
    return card.getByRole('link', { name: /consent|einwilligung/i });
  }

  // ==================== Actions ====================

  /**
   * Navigate to the guardian dashboard
   */
  async navigate(): Promise<void> {
    await this.goto(this.path);
    await this.waitForLoad();
  }

  /**
   * Click the "Register Minor" button
   */
  async clickRegisterMinor(): Promise<void> {
    await this.registerMinorButton().click();
    await this.page.waitForURL(/\/guardian\/register/);
  }

  /**
   * Click the "Link Existing Minor" button
   */
  async clickLinkExisting(): Promise<void> {
    await this.linkExistingButton().click();
    await this.page.waitForURL(/\/guardian\/link/);
  }

  /**
   * Click the View button for a specific minor
   */
  async viewMinor(minorName: string): Promise<void> {
    const card = this.minorCardByName(minorName);
    await this.viewButton(card).click();
    await this.page.waitForURL(/\/guardian\/minor\/\d+$/);
  }

  /**
   * Click the Edit button for a specific minor
   */
  async editMinor(minorName: string): Promise<void> {
    const card = this.minorCardByName(minorName);
    await this.editButton(card).click();
    await this.page.waitForURL(/\/guardian\/minor\/\d+\/edit/);
  }

  /**
   * Click the Consent Form button for a specific minor
   */
  async downloadConsentForm(minorName: string): Promise<void> {
    const card = this.minorCardByName(minorName);
    await this.consentFormButton(card).click();
  }

  /**
   * Get the count of linked minors
   */
  async getMinorCount(): Promise<number> {
    return await this.minorCards().count();
  }

  // ==================== Assertions ====================

  /**
   * Assert that we are on the guardian dashboard page
   */
  async expectOnGuardianDashboard(): Promise<void> {
    await expect(this.page).toHaveURL(/\/guardian$/);
    await expect(this.pageTitle()).toBeVisible();
  }

  /**
   * Assert that a specific minor is shown in the dashboard
   */
  async expectMinorVisible(minorName: string): Promise<void> {
    await expect(this.minorCardByName(minorName)).toBeVisible();
  }

  /**
   * Assert that no minors are linked yet
   */
  async expectNoMinors(): Promise<void> {
    await expect(this.noMinorsMessage()).toBeVisible();
  }

  /**
   * Assert that a minor has consent approved
   */
  async expectConsentApproved(minorName: string): Promise<void> {
    const card = this.minorCardByName(minorName);
    await expect(this.consentApproved(card)).toBeVisible();
  }

  /**
   * Assert that a minor has consent pending
   */
  async expectConsentPending(minorName: string): Promise<void> {
    const card = this.minorCardByName(minorName);
    await expect(this.consentPending(card)).toBeVisible();
  }

  /**
   * Assert that a minor is marked as primary
   */
  async expectIsPrimary(minorName: string): Promise<void> {
    const card = this.minorCardByName(minorName);
    await expect(this.primaryBadge(card)).toBeVisible();
  }

  /**
   * Assert that a minor has a specific category
   */
  async expectCategory(minorName: string, categoryName: string): Promise<void> {
    const card = this.minorCardByName(minorName);
    await expect(this.categoryBadge(card)).toContainText(categoryName);
  }
}
