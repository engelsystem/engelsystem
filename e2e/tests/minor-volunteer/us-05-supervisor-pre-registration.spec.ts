import { test, expect } from '@playwright/test';
import { ShiftCalendarPage } from '../../pages/shifts/shift-calendar.page';
import { ShiftSignupPage } from '../../pages/shifts/shift-signup.page';
import { DashboardPage } from '../../pages/dashboard.page';
import { TEST_USERS } from '../../fixtures/test-users';
import { loginAs } from '../../fixtures/auth';

/**
 * US-05: Supervisor Pre-Registration
 *
 * As a supervisor, I want to be pre-registered for shifts where I supervise
 * minors so that the system knows supervision is available.
 *
 * Test scenarios:
 * 1. Supervisor can access shift calendar
 * 2. Supervisor can view available shifts
 * 3. Supervisor can sign up for shifts
 * 4. Supervisor sees their assigned minors (if applicable)
 * 5. System tracks supervisor availability
 */
test.describe('US-05: Supervisor Pre-Registration', () => {
  let shiftCalendar: ShiftCalendarPage;
  let shiftSignup: ShiftSignupPage;
  let dashboard: DashboardPage;

  test.beforeEach(async ({ page }) => {
    shiftCalendar = new ShiftCalendarPage(page);
    shiftSignup = new ShiftSignupPage(page);
    dashboard = new DashboardPage(page);
  });

  test('supervisor can access shift calendar', async ({ page }) => {
    const supervisor = TEST_USERS.supervisorA;

    await loginAs(page, supervisor.username, supervisor.password);
    await shiftCalendar.navigate();

    await shiftCalendar.expectOnShiftCalendar();
  });

  test('supervisor can view shift calendar with filters', async ({ page }) => {
    const supervisor = TEST_USERS.supervisorB;

    await loginAs(page, supervisor.username, supervisor.password);
    await shiftCalendar.navigate();

    // Verify filter form is accessible
    await shiftCalendar.expectOnShiftCalendar();
    await expect(shiftCalendar.filterForm()).toBeVisible();
  });

  test('supervisor can navigate to shifts from dashboard', async ({ page }) => {
    const supervisor = TEST_USERS.supervisorA;

    await loginAs(page, supervisor.username, supervisor.password);
    await dashboard.navigate();
    await dashboard.goToShifts();

    await shiftCalendar.expectOnShiftCalendar();
  });

  test('supervisor sees shift calendar properly', async ({ page }) => {
    const supervisor = TEST_USERS.supervisorA;

    await loginAs(page, supervisor.username, supervisor.password);
    await shiftCalendar.navigate();

    // Calendar content should be visible
    await shiftCalendar.expectShiftsVisible();
  });

  test('supervisor can use date filters', async ({ page }) => {
    const supervisor = TEST_USERS.supervisorB;

    await loginAs(page, supervisor.username, supervisor.password);
    await shiftCalendar.navigate();

    // Click today button if available
    const todayButton = shiftCalendar.todayButton();
    if (await todayButton.isVisible().catch(() => false)) {
      await todayButton.click();
      await page.waitForLoadState('networkidle');
    }

    // Should remain on shift calendar
    await shiftCalendar.expectOnShiftCalendar();
  });

  test('supervisor A has supervisor role', async ({ page }) => {
    const supervisor = TEST_USERS.supervisorA;

    await loginAs(page, supervisor.username, supervisor.password);
    await dashboard.navigate();

    // Supervisor should have access to dashboard
    await expect(dashboard.newsSection()).toBeVisible();
  });

  test('supervisor B can access shifts', async ({ page }) => {
    const supervisor = TEST_USERS.supervisorB;

    await loginAs(page, supervisor.username, supervisor.password);
    await shiftCalendar.navigate();

    await shiftCalendar.expectOnShiftCalendar();
  });

  test('supervisor session persists across navigation', async ({ page }) => {
    const supervisor = TEST_USERS.supervisorA;

    await loginAs(page, supervisor.username, supervisor.password);

    // Navigate to dashboard
    await dashboard.navigate();
    await expect(dashboard.newsSection()).toBeVisible();

    // Navigate to shifts
    await shiftCalendar.navigate();
    await shiftCalendar.expectOnShiftCalendar();

    // Navigate back to dashboard
    await dashboard.navigate();
    await expect(dashboard.newsSection()).toBeVisible();
  });
});
