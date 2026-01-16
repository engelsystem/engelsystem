import { Page, Locator, expect } from '@playwright/test';
import { BasePage } from '../base.page';

/**
 * Page Object for the Link Minor page
 * Allows guardians to link to existing minor accounts
 */
export class LinkMinorPage extends BasePage {
  readonly path = '/guardian/link';

  constructor(page: Page) {
    super(page);
  }

  // ==================== Locators ====================

  pageTitle(): Locator {
    return this.page.locator('h1');
  }

  // Form fields
  minorIdentifierInput(): Locator {
    return this.page.locator('input[name="minor_identifier"]');
  }

  relationshipTypeSelect(): Locator {
    return this.page.locator('select[name="relationship_type"]');
  }

  submitButton(): Locator {
    return this.page.getByRole('button', { name: /link|verknüpf/i });
  }

  cancelButton(): Locator {
    return this.page.getByRole('link', { name: /cancel|abbrechen/i });
  }

  registerNewButton(): Locator {
    return this.page.getByRole('link', { name: /register|registrier/i });
  }

  errorMessage(): Locator {
    return this.page.locator('.alert-danger');
  }

  successMessage(): Locator {
    return this.page.locator('.alert-success');
  }

  // ==================== Actions ====================

  /**
   * Navigate to the link minor page
   */
  async navigate(): Promise<void> {
    await this.goto(this.path);
    await this.waitForLoad();
  }

  /**
   * Fill in the link form
   */
  async fillForm(minorIdentifier: string, relationshipType: 'parent' | 'legal_guardian' | 'delegated' = 'parent'): Promise<void> {
    await this.minorIdentifierInput().fill(minorIdentifier);
    await this.relationshipTypeSelect().selectOption(relationshipType);
  }

  /**
   * Submit the link form
   */
  async submit(): Promise<void> {
    await this.submitButton().click();
  }

  /**
   * Link to an existing minor (fill and submit)
   */
  async linkMinor(minorIdentifier: string, relationshipType: 'parent' | 'legal_guardian' | 'delegated' = 'parent'): Promise<void> {
    await this.fillForm(minorIdentifier, relationshipType);
    await this.submit();
  }

  /**
   * Cancel and return to dashboard
   */
  async cancel(): Promise<void> {
    await this.cancelButton().click();
    await this.page.waitForURL(/\/guardian$/);
  }

  /**
   * Go to register new minor page
   */
  async goToRegisterNew(): Promise<void> {
    await this.registerNewButton().click();
    await this.page.waitForURL(/\/guardian\/register/);
  }

  // ==================== Assertions ====================

  /**
   * Assert that we are on the link minor page
   */
  async expectOnLinkPage(): Promise<void> {
    await expect(this.page).toHaveURL(/\/guardian\/link/);
    await expect(this.pageTitle()).toBeVisible();
  }

  /**
   * Assert that link was successful (redirected to dashboard)
   */
  async expectLinkSuccess(): Promise<void> {
    // Should redirect to dashboard or show success message
    await expect(this.page).toHaveURL(/\/guardian/);
  }

  /**
   * Assert that link failed with an error
   */
  async expectLinkError(message?: string): Promise<void> {
    await expect(this.errorMessage()).toBeVisible();
    if (message) {
      await expect(this.errorMessage()).toContainText(message);
    }
  }
}
