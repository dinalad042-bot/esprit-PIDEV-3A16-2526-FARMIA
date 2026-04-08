# FarmAI - E2E Testing Documentation

## Overview
This directory contains End-to-End (E2E) tests for the FarmAI application using Playwright.

## Test Files

### 1. `farmai-complete-e2e.spec.ts`
Comprehensive E2E test suite with 10+ test cases covering:
- **Authentication**: Login with CAPTCHA bypass for testing
- **Dashboard**: Expert dashboard statistics and display
- **Analyse CRUD**: List, Show, Create with validation
- **Conseil CRUD**: List with priority filtering
- **AI Diagnostic**: Visual diagnosis display
- **PDF Export**: Generate and verify PDF reports
- **Workflow**: Complete end-to-end workflows
- **Error Handling**: 404 and error pages

### 2. `farmai_e2e_*.spec.ts` (Generated)
Auto-generated test file from the Playwright codegen session.

## Prerequisites

1. **Node.js** (v16 or higher)
2. **Playwright** installed globally or locally
3. **FarmAI application** running on `http://localhost:8000`

## Installation

```bash
# Install Playwright
npm init -y
npm install @playwright/test
npx playwright install

# Or install dependencies from package.json
npm install
```

## Running Tests

### Run all tests
```bash
npx playwright test
```

### Run tests in headed mode (with browser visible)
```bash
npx playwright test --headed
```

### Run specific test file
```bash
npx playwright test farmai-complete-e2e.spec.ts
```

### Run with specific browser
```bash
npx playwright test --project=chromium
npx playwright test --project=firefox
npx playwright test --project=webkit
```

### Run tests in debug mode
```bash
npx playwright test --debug
```

### Generate HTML report
```bash
npx playwright test --reporter=html
npx playwright show-report
```

## Test Configuration

### Test Credentials
- **Email**: `dinalad042@gmail.com`
- **Password**: `15141213`
- **Role**: Expert

### Base URL
All tests use: `http://localhost:8000`

## Test Coverage Summary

| Test ID | Feature | Description | Status |
|---------|---------|-------------|--------|
| T01 | Login | Authentication flow | ✅ Pass |
| T02 | Dashboard | Expert dashboard display | ✅ Pass |
| T03 | Analyse List | CRUD - List/Index | ✅ Pass |
| T04 | Analyse Show | CRUD - View details | ✅ Pass |
| T05 | Analyse Create | CRUD - Create with validation | ✅ Pass |
| T06 | Conseil List | CRUD - Priority filtering | ✅ Pass |
| T07 | AI Diagnostic | Visual diagnosis feature | ✅ Pass |
| T08 | PDF Export | Generate PDF reports | ✅ Pass |
| T09 | Workflow | Complete CRUD workflow | ✅ Pass |
| T10 | Navigation | Main menu access | ✅ Pass |
| E01 | Error Handling | 404 page handling | ✅ Pass |

## Key Features Tested

### 1. Authentication
- Login form validation
- CAPTCHA bypass for testing (see `CaptchaLoginListener.php`)
- Session management
- Role-based access (ROLE_EXPERT)

### 2. Dashboard
- Statistics display (Analyses, Conseils, Priority counts)
- Recent analyses list
- Quick action buttons
- Priority breakdown charts

### 3. Analyse Module
- **List**: Search, pagination, stats cards
- **Show**: View details, associated conseils
- **Create**: Form validation, dropdowns, date picker
- **Edit**: Update existing analyses
- **Delete**: Remove analyses (POST method)

### 4. Conseil Module
- **List**: Priority filtering (Haute, Moyenne, Basse)
- Priority counts display
- Association with Analyses

### 5. AI Diagnostic
- Visual diagnosis display
- Condition, Confidence, Urgency levels
- Symptoms, Treatment, Prevention sections

### 6. PDF Export
- DomPDF integration
- Professional report generation
- Download with correct headers

## Troubleshooting

### Test Timeouts
Increase timeout in `playwright.config.ts`:
```javascript
timeout: 60000, // 60 seconds
```

### Element Not Found
- Ensure the application is running: `http://localhost:8000`
- Check that the database has seed data
- Verify CAPTCHA is disabled in `CaptchaLoginListener.php`

### Screenshot Capturing
Screenshots are saved in the test output directory. To capture on failure:
```javascript
// In playwright.config.ts
use: {
  screenshot: 'only-on-failure',
}
```

## CI/CD Integration

### GitHub Actions Example
```yaml
name: E2E Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: actions/setup-node@v3
        with:
          node-version: 18
      - run: npm ci
      - run: npx playwright install --with-deps
      - run: npx playwright test
      - uses: actions/upload-artifact@v3
        if: always()
        with:
          name: playwright-report
          path: playwright-report/
```

## Notes

1. **CAPTCHA**: The CaptchaLoginListener has validation commented out for testing purposes. In production, uncomment the validation code.

2. **Test Data**: Tests assume the database has been seeded with demo data. Run `php setup_demo_data.php` if needed.

3. **Browser Support**: Tests run on Chromium, Firefox, and WebKit by default.

4. **Parallel Execution**: Tests are configured to run in parallel for faster execution.

## Maintenance

- Update selectors if UI changes
- Add new tests for new features
- Review and update test data regularly
- Monitor test execution time

## Contact

For questions or issues with E2E tests, refer to:
- `E2E_TEST_LOG.md` for test results history
- `REAL_TESTING.md` for manual testing documentation
