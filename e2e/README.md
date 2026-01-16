# Engelsystem E2E Tests

End-to-end tests for Engelsystem using [Playwright](https://playwright.dev/).

## Overview

This test suite covers:
- **Smoke tests**: Basic login, navigation, and dashboard functionality
- **Minor Volunteer tests**: All 7 user stories (US-01 through US-07)
- **Multi-browser support**: Chromium, Firefox, WebKit
- **Mobile support**: iOS Safari, Android Chrome

## Prerequisites

- Node.js 18+
- Running Engelsystem instance (local or remote)
- Test data seeded (see below)

## Setup

### Install Dependencies

```bash
cd e2e
npm install

# Install Playwright browsers
npx playwright install --with-deps
```

### Seed Test Data

Before running tests, seed the test data:

```bash
# From project root
nix run .#seed-test-data

# Or if in dev shell
nix develop
es-seed-test-data
```

This creates:
- 12 test users (password: `testpass123`)
- 12 test shifts with various time slots and categories
- Guardian-minor relationships
- Supervisor configurations

## Running Tests

### All Tests

```bash
npm test
# or
npx playwright test
```

### Specific Test Suites

```bash
# Smoke tests only
npm run test:smoke

# Minor volunteer user stories
npm run test:minor-volunteer

# Single browser
npm run test:chromium
npm run test:firefox
npm run test:webkit

# Mobile browsers
npm run test:mobile
```

### Interactive Mode

```bash
# Playwright UI mode (recommended for development)
npm run test:ui

# Debug mode (step through tests)
npm run test:debug

# Headed mode (watch browser)
npm run test:headed
```

### Generate Tests

```bash
# Record actions in browser
npm run codegen
```

## Test Users

All test users use password: `testpass123`

| Username | Role | Description |
|----------|------|-------------|
| test_shico | ShiCo | Shift Coordinator (admin) |
| test_bureaucrat | Bureaucrat | User management |
| test_angel_a | Angel | Standard adult |
| test_guardian_a | Guardian | Guardian of test_minor_junior |
| test_guardian_b | Guardian | Guardian of test_minor_teen, test_minor_child |
| test_supervisor_a | Supervisor | Willing to supervise minors |
| test_minor_junior | Junior Angel | 13-14, 4h/day, 08:00-18:00, Cat A |
| test_minor_teen | Teen Angel | 15-17, 8h/day, 06:00-22:00, Cat A+B |
| test_minor_child | Accompanying Child | Under 13, no work |

## Project Structure

```
e2e/
├── playwright.config.ts   # Test configuration
├── tsconfig.json          # TypeScript config
├── package.json           # Dependencies
├── tests/
│   ├── smoke/             # Basic functionality tests
│   │   ├── login.spec.ts
│   │   └── dashboard.spec.ts
│   └── minor-volunteer/   # User story tests
│       ├── us-01-guardian-registration.spec.ts
│       ├── us-02-minor-self-registration.spec.ts
│       ├── us-03-shift-type-classification.spec.ts
│       ├── us-04-shift-signup-validation.spec.ts
│       ├── us-05-supervisor-preregistration.spec.ts
│       ├── us-06-non-counting-participation.spec.ts
│       └── us-07-heaven-minor-overview.spec.ts
├── pages/                 # Page Object Models
│   ├── base.page.ts
│   ├── login.page.ts
│   ├── dashboard.page.ts
│   ├── guardian/
│   ├── shifts/
│   └── admin/
├── fixtures/              # Test fixtures
│   ├── auth.ts            # Authentication helpers
│   ├── database.ts        # Database utilities
│   └── test-users.ts      # User constants
└── utils/                 # Utility functions
    ├── database.utils.ts
    └── assertions.utils.ts
```

## Configuration

### Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_URL` | `http://localhost:5080` | Application base URL |
| `CI` | - | Set in CI environment |

### Browser Projects

The test suite includes these browser configurations:

- `chromium-desktop` - Desktop Chrome
- `firefox-desktop` - Desktop Firefox
- `webkit-desktop` - Desktop Safari
- `mobile-chrome` - Pixel 5 (Android)
- `mobile-safari` - iPhone 13 (iOS)
- `tablet` - iPad Pro

Run specific project:

```bash
npx playwright test --project=mobile-chrome
```

## Writing Tests

### Page Object Model

All page interactions should go through Page Objects:

```typescript
import { LoginPage } from '../../pages/login.page';

test('example', async ({ page }) => {
  const loginPage = new LoginPage(page);
  await loginPage.navigate();
  await loginPage.login('username', 'password');
  await loginPage.expectLoginSuccess();
});
```

### Using Test Fixtures

```typescript
import { TEST_USERS } from '../../fixtures/test-users';
import { loginAs } from '../../fixtures/auth';

test('example', async ({ page }) => {
  await loginAs(page, TEST_USERS.shico.username);
  // Test admin functionality...
});
```

### Best Practices

1. **Use Page Objects** - Never use selectors directly in tests
2. **Use test data constants** - Import from `test-users.ts`
3. **Wait for conditions** - Use `expect().toBeVisible()` instead of `waitForTimeout()`
4. **Clean test structure** - Arrange, Act, Assert pattern
5. **Descriptive names** - Test names should read like specifications

## Viewing Reports

After running tests:

```bash
# Open HTML report
npm run report
```

Reports are generated in:
- `e2e-report/` - HTML report
- `e2e-results.xml` - JUnit XML (for CI)
- `test-results/` - Screenshots, traces, videos

## CI Integration

Tests run automatically in GitLab CI. See `.gitlab-ci.yml` for configuration.

### Artifacts

CI uploads:
- HTML test report
- JUnit XML results
- Screenshots/videos on failure
- Trace files for debugging

## Troubleshooting

### Tests fail with "browser not found"

```bash
npx playwright install --with-deps
```

### Tests fail with connection errors

Ensure the dev server is running:

```bash
nix run .#serve
```

### Flaky tests

1. Check for race conditions
2. Use explicit waits instead of timeouts
3. Ensure test data is properly seeded
4. Run with `--trace on` to debug:

```bash
npx playwright test --trace on
npx playwright show-trace test-results/trace.zip
```

## Contributing

1. Create tests following existing patterns
2. Use Page Objects for all page interactions
3. Add new pages to `pages/` directory
4. Update test users in `fixtures/test-users.ts` if needed
5. Run full test suite before submitting
