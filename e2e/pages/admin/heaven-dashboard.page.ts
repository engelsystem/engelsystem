import { Page, Locator, expect } from '@playwright/test';
import { BasePage } from '../base.page';

/**
 * Page Object for the Heaven (Admin) Minor Management Dashboard
 * Shows all minors, consent status, supervision gaps, and category statistics
 */
export class HeavenDashboardPage extends BasePage {
  readonly path = '/admin/minors';

  constructor(page: Page) {
    super(page);
  }

  // ==================== Locators ====================

  pageTitle(): Locator {
    return this.page.locator('h1');
  }

  // Statistics cards
  totalMinorsCard(): Locator {
    return this.page.locator('.card.text-bg-primary');
  }

  totalMinorsCount(): Locator {
    return this.totalMinorsCard().locator('.display-6');
  }

  consentApprovedCard(): Locator {
    return this.page.locator('.card.text-bg-success');
  }

  consentApprovedCount(): Locator {
    return this.consentApprovedCard().locator('.display-6');
  }

  consentPendingCard(): Locator {
    return this.page.locator('.card.text-bg-warning');
  }

  consentPendingCount(): Locator {
    return this.consentPendingCard().locator('.display-6');
  }

  supervisionGapsCard(): Locator {
    return this.page.locator('.card.text-bg-danger');
  }

  supervisionGapsCount(): Locator {
    return this.supervisionGapsCard().locator('.display-6');
  }

  // Category statistics section
  categoryStatsSection(): Locator {
    return this.page.locator('.card:has-text("category"), .card:has-text("Kategorie")');
  }

  // Supervision gaps alert
  supervisionGapsAlert(): Locator {
    return this.page.locator('.alert-danger:has(.bi-exclamation-triangle)');
  }

  supervisionGapsList(): Locator {
    return this.supervisionGapsAlert().locator('ul li');
  }

  // Search and filter form
  searchInput(): Locator {
    return this.page.locator('input[name="search"]');
  }

  // Choices.js containers for select elements
  categoryFilter(): Locator {
    return this.page.locator('.choices:has(select[name="category"]), select[name="category"]').first();
  }

  consentFilter(): Locator {
    return this.page.locator('.choices:has(select[name="consent"]), select[name="consent"]').first();
  }

  // For filtering actions - need the raw selects
  categoryFilterSelect(): Locator {
    return this.page.locator('select[name="category"]');
  }

  consentFilterSelect(): Locator {
    return this.page.locator('select[name="consent"]');
  }

  searchButton(): Locator {
    return this.page.getByRole('button', { name: /search|suchen/i });
  }

  // Filter form itself
  filterForm(): Locator {
    return this.page.locator('form:has(input[name="search"])');
  }

  // Minors table
  minorsTable(): Locator {
    return this.page.locator('table.table');
  }

  minorRows(): Locator {
    return this.minorsTable().locator('tbody tr');
  }

  minorRowByName(name: string): Locator {
    return this.minorsTable().locator('tbody tr', { hasText: name });
  }

  // Row elements
  minorName(row: Locator): Locator {
    return row.locator('td:nth-child(1)');
  }

  minorCategory(row: Locator): Locator {
    return row.locator('td:nth-child(2) .badge');
  }

  minorGuardians(row: Locator): Locator {
    return row.locator('td:nth-child(3)');
  }

  minorConsentStatus(row: Locator): Locator {
    return row.locator('td:nth-child(4) .badge');
  }

  minorHoursProgress(row: Locator): Locator {
    return row.locator('td:nth-child(5) .progress');
  }

  // Action buttons
  viewButton(row: Locator): Locator {
    return row.locator('.btn-group').getByRole('link');
  }

  approveConsentButton(row: Locator): Locator {
    return row.locator('.btn-group').locator('button.btn-success');
  }

  revokeConsentButton(row: Locator): Locator {
    return row.locator('.btn-group').locator('button.btn-outline-danger');
  }

  // Empty state
  noMinorsMessage(): Locator {
    return this.page.locator('tr td[colspan]');
  }

  // ==================== Actions ====================

  /**
   * Navigate to the heaven minor management dashboard
   */
  async navigate(): Promise<void> {
    await this.goto(this.path);
    await this.waitForLoad();
  }

  /**
   * Search for a minor by name
   */
  async searchMinor(searchTerm: string): Promise<void> {
    await this.searchInput().fill(searchTerm);
    await this.searchButton().click();
    await this.page.waitForLoadState('networkidle');
  }

  /**
   * Filter by category
   */
  async filterByCategory(categoryId: string): Promise<void> {
    // Force option since Choices.js hides the native select
    await this.categoryFilterSelect().selectOption(categoryId, { force: true });
    await this.searchButton().click();
    await this.page.waitForLoadState('networkidle');
  }

  /**
   * Filter by consent status
   */
  async filterByConsent(status: 'approved' | 'pending'): Promise<void> {
    // Force option since Choices.js hides the native select
    await this.consentFilterSelect().selectOption(status, { force: true });
    await this.searchButton().click();
    await this.page.waitForLoadState('networkidle');
  }

  /**
   * Clear all filters
   */
  async clearFilters(): Promise<void> {
    await this.searchInput().clear();
    await this.categoryFilterSelect().selectOption('', { force: true });
    await this.consentFilterSelect().selectOption('', { force: true });
    await this.searchButton().click();
    await this.page.waitForLoadState('networkidle');
  }

  /**
   * View a minor's profile
   */
  async viewMinor(minorName: string): Promise<void> {
    const row = this.minorRowByName(minorName);
    await this.viewButton(row).click();
    await this.page.waitForURL(/users.*user_id/);
  }

  /**
   * Approve consent for a minor
   */
  async approveConsent(minorName: string): Promise<void> {
    const row = this.minorRowByName(minorName);
    await this.approveConsentButton(row).click();
    await this.page.waitForLoadState('networkidle');
  }

  /**
   * Revoke consent for a minor (with confirmation)
   */
  async revokeConsent(minorName: string): Promise<void> {
    const row = this.minorRowByName(minorName);
    // Handle confirmation dialog
    this.page.once('dialog', async (dialog) => {
      await dialog.accept();
    });
    await this.revokeConsentButton(row).click();
    await this.page.waitForLoadState('networkidle');
  }

  /**
   * Get the total number of minors shown
   */
  async getMinorCount(): Promise<number> {
    return await this.minorRows().count();
  }

  /**
   * Get statistics from the dashboard
   */
  async getStatistics(): Promise<{
    total: number;
    approved: number;
    pending: number;
    gaps: number;
  }> {
    const total = parseInt(await this.totalMinorsCount().textContent() || '0');
    const approved = parseInt(await this.consentApprovedCount().textContent() || '0');
    const pending = parseInt(await this.consentPendingCount().textContent() || '0');
    const gaps = parseInt(await this.supervisionGapsCount().textContent() || '0');
    return { total, approved, pending, gaps };
  }

  // ==================== Assertions ====================

  /**
   * Assert that we are on the heaven dashboard page
   */
  async expectOnHeavenDashboard(): Promise<void> {
    await expect(this.page).toHaveURL(/\/admin\/minors/);
    await expect(this.pageTitle()).toBeVisible();
  }

  /**
   * Assert that a minor is visible in the table
   */
  async expectMinorVisible(minorName: string): Promise<void> {
    await expect(this.minorRowByName(minorName)).toBeVisible();
  }

  /**
   * Assert that a minor is NOT visible
   */
  async expectMinorNotVisible(minorName: string): Promise<void> {
    await expect(this.minorRowByName(minorName)).not.toBeVisible();
  }

  /**
   * Assert that a minor has a specific consent status
   */
  async expectConsentStatus(minorName: string, status: 'approved' | 'pending'): Promise<void> {
    const row = this.minorRowByName(minorName);
    const badge = this.minorConsentStatus(row);
    if (status === 'approved') {
      await expect(badge).toHaveClass(/bg-success/);
    } else {
      await expect(badge).toHaveClass(/bg-warning/);
    }
  }

  /**
   * Assert that a minor is in a specific category
   */
  async expectMinorCategory(minorName: string, categoryName: string): Promise<void> {
    const row = this.minorRowByName(minorName);
    await expect(this.minorCategory(row)).toContainText(categoryName);
  }

  /**
   * Assert that there are supervision gaps
   */
  async expectSupervisionGaps(): Promise<void> {
    await expect(this.supervisionGapsAlert()).toBeVisible();
  }

  /**
   * Assert that there are no supervision gaps
   */
  async expectNoSupervisionGaps(): Promise<void> {
    await expect(this.supervisionGapsAlert()).not.toBeVisible();
  }

  /**
   * Assert no minors found (empty state)
   */
  async expectNoMinors(): Promise<void> {
    await expect(this.noMinorsMessage()).toBeVisible();
  }

  /**
   * Assert the approve button is visible for a minor
   */
  async expectCanApproveConsent(minorName: string): Promise<void> {
    const row = this.minorRowByName(minorName);
    await expect(this.approveConsentButton(row)).toBeVisible();
  }

  /**
   * Assert the revoke button is visible for a minor
   */
  async expectCanRevokeConsent(minorName: string): Promise<void> {
    const row = this.minorRowByName(minorName);
    await expect(this.revokeConsentButton(row)).toBeVisible();
  }
}
