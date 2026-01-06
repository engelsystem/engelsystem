import { Page, Locator, expect } from '@playwright/test';
import { BasePage } from '../base.page';

/**
 * Page Object for the Register Minor page
 * Allows guardians to create new accounts for minors
 */
export class RegisterMinorPage extends BasePage {
  readonly path = '/guardian/register';

  constructor(page: Page) {
    super(page);
  }

  // ==================== Locators ====================

  pageTitle(): Locator {
    return this.page.locator('h1');
  }

  // Form fields
  usernameInput(): Locator {
    return this.page.locator('input[name="name"]');
  }

  emailInput(): Locator {
    return this.page.locator('input[name="email"]');
  }

  passwordInput(): Locator {
    return this.page.locator('input[name="password"]');
  }

  // Choices.js container or raw select
  categorySelect(): Locator {
    return this.page.locator('.choices:has(select[name="minor_category_id"]), select[name="minor_category_id"]').first();
  }

  // Raw select for programmatic interaction
  categorySelectRaw(): Locator {
    return this.page.locator('select[name="minor_category_id"]');
  }

  firstNameInput(): Locator {
    return this.page.locator('input[name="first_name"]');
  }

  lastNameInput(): Locator {
    return this.page.locator('input[name="last_name"]');
  }

  pronounInput(): Locator {
    return this.page.locator('input[name="pronoun"]');
  }

  submitButton(): Locator {
    return this.page.getByRole('button', { name: /register|registrier/i });
  }

  cancelButton(): Locator {
    return this.page.getByRole('link', { name: /cancel|abbrechen/i });
  }

  errorMessage(): Locator {
    return this.page.locator('.alert-danger');
  }

  successMessage(): Locator {
    return this.page.locator('.alert-success');
  }

  // ==================== Actions ====================

  /**
   * Navigate to the register minor page
   */
  async navigate(): Promise<void> {
    await this.goto(this.path);
    await this.waitForLoad();
  }

  /**
   * Fill in the registration form
   */
  async fillForm(data: {
    username: string;
    password: string;
    category: string;
    email?: string;
    firstName?: string;
    lastName?: string;
    pronoun?: string;
  }): Promise<void> {
    await this.usernameInput().fill(data.username);
    await this.passwordInput().fill(data.password);
    // Select category by partial text match - use raw select for programmatic interaction
    // Force option since Choices.js hides the native select
    const options = await this.categorySelectRaw().locator('option').allTextContents();
    const matchingOption = options.find(opt => opt.toLowerCase().includes(data.category.toLowerCase()));
    if (matchingOption) {
      await this.categorySelectRaw().selectOption({ label: matchingOption }, { force: true });
    } else {
      await this.categorySelectRaw().selectOption(data.category, { force: true });
    }

    if (data.email) {
      await this.emailInput().fill(data.email);
    }
    if (data.firstName) {
      await this.firstNameInput().fill(data.firstName);
    }
    if (data.lastName) {
      await this.lastNameInput().fill(data.lastName);
    }
    if (data.pronoun) {
      await this.pronounInput().fill(data.pronoun);
    }
  }

  /**
   * Submit the registration form
   */
  async submit(): Promise<void> {
    await this.submitButton().click();
  }

  /**
   * Register a new minor (fill and submit)
   */
  async registerMinor(data: {
    username: string;
    password: string;
    category: string;
    email?: string;
    firstName?: string;
    lastName?: string;
    pronoun?: string;
  }): Promise<void> {
    await this.fillForm(data);
    await this.submit();
  }

  /**
   * Cancel registration and return to dashboard
   */
  async cancel(): Promise<void> {
    await this.cancelButton().click();
    await this.page.waitForURL(/\/guardian$/);
  }

  /**
   * Get available category options
   */
  async getCategoryOptions(): Promise<string[]> {
    const options = await this.categorySelectRaw().locator('option').allTextContents();
    // Filter out the placeholder option
    return options.filter(opt => opt.trim() !== '' && !opt.includes('Select'));
  }

  // ==================== Assertions ====================

  /**
   * Assert that we are on the register minor page
   */
  async expectOnRegisterPage(): Promise<void> {
    await expect(this.page).toHaveURL(/\/guardian\/register/);
    await expect(this.pageTitle()).toBeVisible();
  }

  /**
   * Assert that registration was successful (redirected to dashboard or minor page)
   */
  async expectRegistrationSuccess(): Promise<void> {
    await expect(this.page).not.toHaveURL(/\/guardian\/register/);
    // Either redirected to dashboard or success message shown
    const onDashboard = await this.page.url().includes('/guardian');
    expect(onDashboard).toBe(true);
  }

  /**
   * Assert that registration failed with an error
   */
  async expectRegistrationError(message?: string): Promise<void> {
    await expect(this.errorMessage()).toBeVisible();
    if (message) {
      await expect(this.errorMessage()).toContainText(message);
    }
  }

  /**
   * Assert that a specific category is available
   */
  async expectCategoryAvailable(categoryName: string): Promise<void> {
    const options = await this.getCategoryOptions();
    const found = options.some(opt => opt.toLowerCase().includes(categoryName.toLowerCase()));
    expect(found).toBe(true);
  }
}
