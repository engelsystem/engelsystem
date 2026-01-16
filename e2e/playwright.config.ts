import { defineConfig, devices } from '@playwright/test';

/**
 * Engelsystem E2E Test Configuration
 *
 * Supports:
 * - Desktop browsers: Chromium, Firefox, WebKit
 * - Mobile browsers: Pixel 5 (Chrome), iPhone 13 (Safari)
 *
 * @see https://playwright.dev/docs/test-configuration
 */
export default defineConfig({
  // Test directory
  testDir: './tests',

  // Test file pattern
  testMatch: '**/*.spec.ts',

  // Maximum time one test can run
  timeout: 60 * 1000, // 60 seconds

  // Expect timeout
  expect: {
    timeout: 10 * 1000,
  },

  // Test execution settings
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 4 : undefined,

  // Reporter configuration
  reporter: [
    ['html', { outputFolder: 'e2e-report', open: 'never' }],
    ['junit', { outputFile: 'e2e-results.xml' }],
    ['list'],
    ...(process.env.CI ? [['github', {}] as const] : []),
  ],

  // Global test settings
  use: {
    // Base URL - uses local dev server by default
    baseURL: process.env.APP_URL || 'http://localhost:5080',

    // Tracing and artifacts
    trace: process.env.CI ? 'retain-on-failure' : 'on-first-retry',
    screenshot: 'only-on-failure',
    video: process.env.CI ? 'retain-on-failure' : 'off',

    // Browser options - always headless unless HEADED=true
    headless: process.env.HEADED !== 'true',
    viewport: { width: 1280, height: 720 },

    // Network options
    actionTimeout: 10 * 1000,
    navigationTimeout: 30 * 1000,

    // Locale settings for German event management
    locale: 'de-DE',
    timezoneId: 'Europe/Berlin',
  },

  // Output directory for test artifacts
  outputDir: 'test-results/',

  // Projects for different browsers and viewports
  projects: [
    // Desktop browsers (1440px+ to ensure navbar expansion for logged-in users)
    // Engelsystem uses Bootstrap navbar-expand-xxl (1400px) for authenticated users
    {
      name: 'chromium-desktop',
      use: { ...devices['Desktop Chrome'], viewport: { width: 1440, height: 900 } },
    },
    {
      name: 'firefox-desktop',
      use: { ...devices['Desktop Firefox'], viewport: { width: 1440, height: 900 } },
    },
    {
      name: 'webkit-desktop',
      use: { ...devices['Desktop Safari'], viewport: { width: 1440, height: 900 } },
    },

    // Mobile browsers
    {
      name: 'mobile-chrome',
      use: { ...devices['Pixel 5'] },
    },
    {
      name: 'mobile-safari',
      use: { ...devices['iPhone 13'] },
    },

    // Tablet
    {
      name: 'tablet',
      use: { ...devices['iPad Pro'] },
    },
  ],

  // Web server configuration (for local development)
  // Uncomment to auto-start dev server before tests
  // webServer: {
  //   command: 'nix run .#serve',
  //   url: 'http://localhost:5080',
  //   timeout: 120 * 1000,
  //   reuseExistingServer: !process.env.CI,
  // },
});
