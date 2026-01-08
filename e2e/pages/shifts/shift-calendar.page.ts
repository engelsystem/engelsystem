import { Page, Locator, expect } from '@playwright/test';
import { BasePage } from '../base.page';

/**
 * Page Object for the Shift Calendar (user-shifts page)
 * Main view showing available shifts to sign up for
 */
export class ShiftCalendarPage extends BasePage {
  readonly path = '/user-shifts';

  constructor(page: Page) {
    super(page);
  }

  // ==================== Locators ====================

  pageTitle(): Locator {
    return this.page.locator('h1').first();
  }

  // Time filter controls - Choices.js wraps selects in .choices container
  startDateSelect(): Locator {
    // Look for the visible Choices.js container, or fall back to form element
    return this.page.locator('.choices:has(select[name="start_day"]), select[name="start_day"]').first();
  }

  startTimeInput(): Locator {
    return this.page.locator('input[name="start_time"]');
  }

  endDateSelect(): Locator {
    // Look for the visible Choices.js container, or fall back to form element
    return this.page.locator('.choices:has(select[name="end_day"]), select[name="end_day"]').first();
  }

  endTimeInput(): Locator {
    return this.page.locator('input[name="end_time"]');
  }

  // Filter form itself
  filterForm(): Locator {
    return this.page.locator('form#filter-form, form.form-inline, form:has(select[name="start_day"])');
  }

  // Filter buttons
  filterButton(): Locator {
    return this.page.getByRole('button', { name: /filter|filtern/i });
  }

  yesterdayButton(): Locator {
    return this.page.locator('button.set-date[data-days="-1"]');
  }

  todayButton(): Locator {
    return this.page.locator('button.set-date[data-days="0"]');
  }

  tomorrowButton(): Locator {
    return this.page.locator('button.set-date[data-days="1"]');
  }

  // Location filter
  locationCheckboxes(): Locator {
    return this.page.locator('input[name="locations[]"]');
  }

  locationCheckbox(locationId: number): Locator {
    return this.page.locator(`input[name="locations[]"][value="${locationId}"]`);
  }

  // Angel type filter
  angelTypeCheckboxes(): Locator {
    return this.page.locator('input[name="types[]"]');
  }

  angelTypeCheckbox(typeId: number): Locator {
    return this.page.locator(`input[name="types[]"][value="${typeId}"]`);
  }

  // Shift calendar/table
  shiftCalendar(): Locator {
    return this.page.locator('.shift-calendar, .shift-table, table.table');
  }

  shiftCells(): Locator {
    return this.page.locator('.shift, .shift-entry, td[class*="shift"]');
  }

  shiftByTitle(title: string): Locator {
    return this.page.locator('.shift, .shift-entry', { hasText: title });
  }

  // Shift signup links/buttons within shift cells
  signupLink(shiftCell: Locator): Locator {
    return shiftCell.getByRole('link', { name: /sign|anmelden/i });
  }

  // Free slots indicator
  freeSlotsIndicator(): Locator {
    return this.page.locator('.free-slots, .badge:has-text("free")');
  }

  // ==================== Actions ====================

  /**
   * Navigate to the shift calendar page
   */
  async navigate(): Promise<void> {
    await this.goto(this.path);
    await this.waitForLoad();
  }

  /**
   * Set the start date for filtering
   */
  async setStartDate(date: string): Promise<void> {
    await this.startDateSelect().selectOption(date);
  }

  /**
   * Set the end date for filtering
   */
  async setEndDate(date: string): Promise<void> {
    await this.endDateSelect().selectOption(date);
  }

  /**
   * Set start time
   */
  async setStartTime(time: string): Promise<void> {
    await this.startTimeInput().fill(time);
  }

  /**
   * Set end time
   */
  async setEndTime(time: string): Promise<void> {
    await this.endTimeInput().fill(time);
  }

  /**
   * Click filter button to apply filters
   */
  async applyFilter(): Promise<void> {
    await this.filterButton().click();
    await this.page.waitForLoadState('networkidle');
  }

  /**
   * Quick navigation to today's shifts
   */
  async goToToday(): Promise<void> {
    await this.todayButton().click();
    await this.page.waitForLoadState('networkidle');
  }

  /**
   * Click on a specific shift to view details
   */
  async viewShift(shiftTitle: string): Promise<void> {
    await this.shiftByTitle(shiftTitle).click();
    await this.page.waitForLoadState('networkidle');
  }

  /**
   * Click signup link for a specific shift
   */
  async clickSignupForShift(shiftTitle: string): Promise<void> {
    const shiftCell = this.shiftByTitle(shiftTitle);
    await this.signupLink(shiftCell).click();
    await this.page.waitForURL(/shift_entry_add|shifts/);
  }

  /**
   * Toggle location filter
   */
  async toggleLocation(locationId: number): Promise<void> {
    await this.locationCheckbox(locationId).click();
  }

  /**
   * Toggle angel type filter
   */
  async toggleAngelType(typeId: number): Promise<void> {
    await this.angelTypeCheckbox(typeId).click();
  }

  // ==================== Assertions ====================

  /**
   * Assert that we are on the shift calendar page
   */
  async expectOnShiftCalendar(): Promise<void> {
    await expect(this.page).toHaveURL(/user-shifts/);
    await expect(this.pageTitle()).toBeVisible();
  }

  /**
   * Assert that shifts are displayed
   */
  async expectShiftsVisible(): Promise<void> {
    await expect(this.shiftCalendar()).toBeVisible();
  }

  /**
   * Assert that a specific shift is visible
   */
  async expectShiftVisible(shiftTitle: string): Promise<void> {
    await expect(this.shiftByTitle(shiftTitle)).toBeVisible();
  }

  /**
   * Assert that a shift is NOT visible (filtered out or doesn't exist)
   */
  async expectShiftNotVisible(shiftTitle: string): Promise<void> {
    await expect(this.shiftByTitle(shiftTitle)).not.toBeVisible();
  }

  /**
   * Assert no shifts available message
   */
  async expectNoShiftsMessage(): Promise<void> {
    await expect(this.page.locator('text=/no shifts|keine schichten/i')).toBeVisible();
  }
}
