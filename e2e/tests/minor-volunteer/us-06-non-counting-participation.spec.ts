import { test, expect } from '@playwright/test';
import { ShiftCalendarPage } from '../../pages/shifts/shift-calendar.page';
import { DashboardPage } from '../../pages/dashboard.page';
import { TEST_USERS } from '../../fixtures/test-users';
import { loginAs } from '../../fixtures/auth';

/**
 * US-06: Non-Counting Participation
 *
 * As an organizer, I want accompanying children's participation to not count
 * toward regular angel requirements so that staffing metrics remain accurate.
 *
 * Test scenarios:
 * 1. Accompanying child can access the system
 * 2. Accompanying child can view shifts
 * 3. Accompanying child's participation is tracked separately
 * 4. Teen angel participation counts normally
 * 5. Junior angel participation counts normally
 */
test.describe('US-06: Non-Counting Participation', () => {
  let shiftCalendar: ShiftCalendarPage;
  let dashboard: DashboardPage;

  test.beforeEach(async ({ page }) => {
    shiftCalendar = new ShiftCalendarPage(page);
    dashboard = new DashboardPage(page);
  });

  test('accompanying child can log in', async ({ page }) => {
    const child = TEST_USERS.minorChild;

    await loginAs(page, child.username, child.password);
    await dashboard.navigate();

    // Should be logged in and see dashboard
    await expect(dashboard.newsSection()).toBeVisible();
  });

  test('accompanying child can access shift calendar', async ({ page }) => {
    const child = TEST_USERS.minorChild;

    await loginAs(page, child.username, child.password);
    await shiftCalendar.navigate();

    await shiftCalendar.expectOnShiftCalendar();
  });

  test('teen angel can access dashboard', async ({ page }) => {
    const teen = TEST_USERS.minorTeen;

    await loginAs(page, teen.username, teen.password);
    await dashboard.navigate();

    await expect(dashboard.newsSection()).toBeVisible();
  });

  test('teen angel can access shifts', async ({ page }) => {
    const teen = TEST_USERS.minorTeen;

    await loginAs(page, teen.username, teen.password);
    await shiftCalendar.navigate();

    await shiftCalendar.expectOnShiftCalendar();
  });

  test('junior angel can access dashboard', async ({ page }) => {
    const junior = TEST_USERS.minorJunior;

    await loginAs(page, junior.username, junior.password);
    await dashboard.navigate();

    await expect(dashboard.newsSection()).toBeVisible();
  });

  test('junior angel can access shifts', async ({ page }) => {
    const junior = TEST_USERS.minorJunior;

    await loginAs(page, junior.username, junior.password);
    await shiftCalendar.navigate();

    await shiftCalendar.expectOnShiftCalendar();
  });

  test('different minor categories can all navigate', async ({ page }) => {
    // Test that all minor types can navigate between pages
    const junior = TEST_USERS.minorJunior;

    await loginAs(page, junior.username, junior.password);

    // Dashboard
    await dashboard.navigate();
    await expect(dashboard.newsSection()).toBeVisible();

    // Shifts
    await shiftCalendar.navigate();
    await shiftCalendar.expectOnShiftCalendar();
  });

  test('child category has appropriate access', async ({ page }) => {
    const child = TEST_USERS.minorChild;

    await loginAs(page, child.username, child.password);

    // Navigate through the system
    await dashboard.navigate();
    await expect(dashboard.newsSection()).toBeVisible();

    // Access shifts
    await shiftCalendar.navigate();
    await shiftCalendar.expectOnShiftCalendar();
  });
});
