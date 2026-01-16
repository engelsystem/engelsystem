import { Page, Locator, expect } from '@playwright/test';
import { BasePage } from '../base.page';

/**
 * Page Object for the Shift Signup/Entry page
 * Handles the process of signing up for a shift
 */
export class ShiftSignupPage extends BasePage {
  constructor(page: Page) {
    super(page);
  }

  // ==================== Locators ====================

  pageTitle(): Locator {
    return this.page.locator('h1, h2').first();
  }

  // Shift details
  shiftTitle(): Locator {
    return this.page.locator('.shift-title, .lead, h3').first();
  }

  shiftLocation(): Locator {
    return this.page.locator('text=/location|ort/i').locator('..').locator('p, .lead').first();
  }

  shiftTime(): Locator {
    return this.page.locator('text=/start|beginn/i').locator('..').locator('p, .lead').first();
  }

  // Angel type selection (if multiple available)
  angelTypeSelect(): Locator {
    return this.page.locator('select[name="angel_type_id"], select[name="type_id"]');
  }

  angelTypeOptions(): Locator {
    return this.angelTypeSelect().locator('option');
  }

  // Comment field
  commentInput(): Locator {
    return this.page.locator('textarea[name="comment"], input[name="comment"]');
  }

  // Submit button
  signupButton(): Locator {
    return this.page.getByRole('button', { name: /sign up|eintragen|anmelden/i });
  }

  cancelButton(): Locator {
    return this.page.getByRole('link', { name: /cancel|abbrechen|back|zurück/i });
  }

  // Error/Warning messages
  errorMessage(): Locator {
    return this.page.locator('.alert-danger, .error, .text-danger');
  }

  warningMessage(): Locator {
    return this.page.locator('.alert-warning');
  }

  successMessage(): Locator {
    return this.page.locator('.alert-success');
  }

  // Minor-specific warnings
  minorRestrictionWarning(): Locator {
    return this.page.locator('.alert-warning:has-text("minor"), .alert-warning:has-text("minderjährig")');
  }

  supervisorRequiredWarning(): Locator {
    return this.page.locator('text=/supervisor|aufsicht/i');
  }

  hoursLimitWarning(): Locator {
    return this.page.locator('text=/hour.*limit|stunden.*limit|maximum.*hours/i');
  }

  // Shift entry list (users already signed up)
  existingEntries(): Locator {
    return this.page.locator('.shift-entry, .entry-list li, tr.shift-entry');
  }

  // ==================== Actions ====================

  /**
   * Navigate directly to a shift signup page
   */
  async navigate(shiftId: number, angelTypeId?: number): Promise<void> {
    const params = angelTypeId
      ? `?shift_id=${shiftId}&type_id=${angelTypeId}`
      : `?shift_id=${shiftId}`;
    await this.goto(`/shift-entry-add${params}`);
    await this.waitForLoad();
  }

  /**
   * Select an angel type (if available)
   */
  async selectAngelType(typeId: number | string): Promise<void> {
    await this.angelTypeSelect().selectOption(String(typeId));
  }

  /**
   * Fill in the comment field
   */
  async fillComment(comment: string): Promise<void> {
    await this.commentInput().fill(comment);
  }

  /**
   * Click the signup button
   */
  async clickSignup(): Promise<void> {
    await this.signupButton().click();
  }

  /**
   * Complete signup process
   */
  async signup(options?: { angelTypeId?: number; comment?: string }): Promise<void> {
    if (options?.angelTypeId) {
      await this.selectAngelType(options.angelTypeId);
    }
    if (options?.comment) {
      await this.fillComment(options.comment);
    }
    await this.clickSignup();
  }

  /**
   * Cancel and go back
   */
  async cancel(): Promise<void> {
    await this.cancelButton().click();
  }

  // ==================== Assertions ====================

  /**
   * Assert that signup was successful
   */
  async expectSignupSuccess(): Promise<void> {
    // After successful signup, typically redirected to shift view or success message shown
    await expect(this.page).not.toHaveURL(/shift-entry-add/);
  }

  /**
   * Assert that signup failed with an error
   */
  async expectSignupError(message?: string): Promise<void> {
    await expect(this.errorMessage()).toBeVisible();
    if (message) {
      await expect(this.errorMessage()).toContainText(message);
    }
  }

  /**
   * Assert that there's a warning about minor restrictions
   */
  async expectMinorRestrictionWarning(): Promise<void> {
    await expect(this.minorRestrictionWarning()).toBeVisible();
  }

  /**
   * Assert that supervisor is required
   */
  async expectSupervisorRequiredWarning(): Promise<void> {
    await expect(this.supervisorRequiredWarning()).toBeVisible();
  }

  /**
   * Assert that hours limit would be exceeded
   */
  async expectHoursLimitWarning(): Promise<void> {
    await expect(this.hoursLimitWarning()).toBeVisible();
  }

  /**
   * Assert that an angel type is available for signup
   */
  async expectAngelTypeAvailable(typeName: string): Promise<void> {
    const options = await this.angelTypeOptions().allTextContents();
    const found = options.some(opt => opt.toLowerCase().includes(typeName.toLowerCase()));
    expect(found).toBe(true);
  }

  /**
   * Assert signup button is disabled (user can't sign up)
   */
  async expectSignupDisabled(): Promise<void> {
    await expect(this.signupButton()).toBeDisabled();
  }

  /**
   * Assert signup button is enabled
   */
  async expectSignupEnabled(): Promise<void> {
    await expect(this.signupButton()).toBeEnabled();
  }
}
