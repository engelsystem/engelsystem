import { test, expect } from '@playwright/test';
import { ShiftCalendarPage } from '../../pages/shifts/shift-calendar.page';
import { DashboardPage } from '../../pages/dashboard.page';
import { TEST_USERS } from '../../fixtures/test-users';
import { loginAs } from '../../fixtures/auth';

/**
 * US-03: Shift Type Classification
 *
 * As an organizer, I want shift types to be classified by minor eligibility
 * so that the system can automatically enforce restrictions.
 *
 * Test scenarios:
 * 1. Shift calendar displays shift types
 * 2. Different user categories see the calendar
 * 3. Angel types have categorized shifts
 * 4. Admin can view all shift types
 */
test.describe('US-03: Shift Type Classification', () => {
  let shiftCalendar: ShiftCalendarPage;
  let dashboard: DashboardPage;

  test.beforeEach(async ({ page }) => {
    shiftCalendar = new ShiftCalendarPage(page);
    dashboard = new DashboardPage(page);
  });

  test('shift calendar displays for adult angel', async ({ page }) => {
    const angel = TEST_USERS.angelA;

    await loginAs(page, angel.username, angel.password);
    await shiftCalendar.navigate();

    await shiftCalendar.expectOnShiftCalendar();
    await shiftCalendar.expectShiftsVisible();
  });

  test('shift calendar displays for junior angel', async ({ page }) => {
    const junior = TEST_USERS.minorJunior;

    await loginAs(page, junior.username, junior.password);
    await shiftCalendar.navigate();

    await shiftCalendar.expectOnShiftCalendar();
  });

  test('shift calendar displays for teen angel', async ({ page }) => {
    const teen = TEST_USERS.minorTeen;

    await loginAs(page, teen.username, teen.password);
    await shiftCalendar.navigate();

    await shiftCalendar.expectOnShiftCalendar();
  });

  test('shift calendar has filter controls', async ({ page }) => {
    const angel = TEST_USERS.angelB;

    await loginAs(page, angel.username, angel.password);
    await shiftCalendar.navigate();

    // Filter form should be visible
    await shiftCalendar.expectOnShiftCalendar();
    await expect(shiftCalendar.filterForm()).toBeVisible();
  });

  test('supervisor can view shift types', async ({ page }) => {
    const supervisor = TEST_USERS.supervisorA;

    await loginAs(page, supervisor.username, supervisor.password);
    await shiftCalendar.navigate();

    await shiftCalendar.expectOnShiftCalendar();
    await shiftCalendar.expectShiftsVisible();
  });

  test('shift calendar accessible from dashboard', async ({ page }) => {
    const angel = TEST_USERS.angelA;

    await loginAs(page, angel.username, angel.password);
    await dashboard.navigate();
    await dashboard.goToShifts();

    await shiftCalendar.expectOnShiftCalendar();
  });
});
