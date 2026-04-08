import { test, expect } from '@playwright/test';

/**
 * FarmAI - Complete E2E Test Suite
 * ================================
 * Tests all major functionalities of the FarmAI application:
 * - Authentication (Login with CAPTCHA bypass)
 * - Expert Dashboard
 * - Analyse CRUD operations
 * - Conseil CRUD operations
 * - AI Diagnostic feature
 * - PDF Export functionality
 * 
 * Base URL: http://localhost:8000
 * Test User: dinalad042@gmail.com / 15141213
 */

test.describe('FarmAI E2E Tests', () => {
  
  // Base URL for all tests
  const BASE_URL = 'http://localhost:8000';
  
  // Test credentials
  const TEST_USER = {
    email: 'dinalad042@gmail.com',
    password: '15141213'
  };

  /**
   * Test 1: Login Flow
   * ------------------
   * Verifies that the user can successfully log in to the application.
   * Note: CAPTCHA validation is disabled in the CaptchaLoginListener for testing.
   */
  test('T01: Login with valid credentials', async ({ page }) => {
    // Navigate to expert dashboard (will redirect to login)
    await page.goto(`${BASE_URL}/expert/dashboard`);
    
    // Verify login form is displayed
    await expect(page.locator('form[method="post"]')).toBeVisible();
    await expect(page.locator('input[name="_username"]')).toBeVisible();
    await expect(page.locator('input[name="_password"]')).toBeVisible();
    
    // Fill in login credentials
    await page.fill('input[name="_username"]', TEST_USER.email);
    await page.fill('input[name="_password"]', TEST_USER.password);
    
    // Submit login form
    await page.click('button[type="submit"].btn-login');
    
    // Verify successful login by checking for dashboard content
    await expect(page.locator('text=Tableau de Bord Expert')).toBeVisible({ timeout: 10000 });
    await expect(page.locator('text=Bienvenue')).toBeVisible();
  });

  /**
   * Test 2: Expert Dashboard
   * ------------------------
   * Verifies the expert dashboard displays correct statistics and data.
   */
  test('T02: Expert Dashboard displays correctly', async ({ page }) => {
    // Login first
    await page.goto(`${BASE_URL}/expert/dashboard`);
    await page.fill('input[name="_username"]', TEST_USER.email);
    await page.fill('input[name="_password"]', TEST_USER.password);
    await page.click('button[type="submit"].btn-login');
    
    // Verify dashboard components
    await expect(page.locator('text=🌿 Tableau de Bord Expert')).toBeVisible();
    
    // Check stats cards
    await expect(page.locator('text=Analyses')).toBeVisible();
    await expect(page.locator('text=Conseils')).toBeVisible();
    await expect(page.locator('text=🔴 Haute priorité')).toBeVisible();
    await expect(page.locator('text=🟡 Moyenne priorité')).toBeVisible();
    
    // Verify quick actions
    await expect(page.locator('text=Nouvelle Analyse')).toBeVisible();
    await expect(page.locator('text=Nouveau Conseil')).toBeVisible();
    
    // Check recent analyses section
    await expect(page.locator('text=Analyses Récentes')).toBeVisible();
  });

  /**
   * Test 3: Analyse List Page
   * -------------------------
   * Verifies the Analyse CRUD - List (Index) functionality.
   */
  test('T03: Analyse List - Display and search', async ({ page }) => {
    // Login
    await page.goto(`${BASE_URL}/login`);
    await page.fill('input[name="_username"]', TEST_USER.email);
    await page.fill('input[name="_password"]', TEST_USER.password);
    await page.click('button[type="submit"].btn-login');
    
    // Navigate to Analyse list
    await page.goto(`${BASE_URL}/analyse`);
    
    // Verify page title
    await expect(page.locator('text=Analyses Agricoles')).toBeVisible();
    await expect(page.locator('text=Consultez et gérez vos analyses de terrain')).toBeVisible();
    
    // Check stats
    await expect(page.locator('text=Total analyses')).toBeVisible();
    await expect(page.locator('text=Avec conseils')).toBeVisible();
    await expect(page.locator('text=Sans conseils')).toBeVisible();
    
    // Verify "Nouvelle Analyse" button
    await expect(page.locator('text=Nouvelle Analyse')).toBeVisible();
    
    // Check that analyses are listed
    await expect(page.locator('text=Ferme de Test PIDEV')).toBeVisible();
  });

  /**
   * Test 4: Analyse Show Page
   * -------------------------
   * Verifies viewing a single Analyse with associated Conseils.
   */
  test('T04: Analyse Show - View details and conseils', async ({ page }) => {
    // Login
    await page.goto(`${BASE_URL}/login`);
    await page.fill('input[name="_username"]', TEST_USER.email);
    await page.fill('input[name="_password"]', TEST_USER.password);
    await page.click('button[type="submit"].btn-login');
    
    // Navigate to Analyse #1
    await page.goto(`${BASE_URL}/analyse/1`);
    
    // Verify analyse details
    await expect(page.locator('text=Analyse')).toBeVisible();
    await expect(page.locator('text=#1')).toBeVisible();
    await expect(page.locator('text=Ferme de Test PIDEV')).toBeVisible();
    
    // Verify associated conseils
    await expect(page.locator('text=Conseil(s) associé(s)')).toBeVisible();
    await expect(page.locator('text=🔴 Haute')).toBeVisible();
    await expect(page.locator('text=🟡 Moyenne')).toBeVisible();
    
    // Check action buttons
    await expect(page.locator('text=Modifier')).toBeVisible();
    await expect(page.locator('text=Ajouter Conseil')).toBeVisible();
  });

  /**
   * Test 5: Analyse Create Form
   * ---------------------------
   * Verifies the new Analyse form displays with validation.
   */
  test('T05: Analyse Create - Form and validation', async ({ page }) => {
    // Login
    await page.goto(`${BASE_URL}/login`);
    await page.fill('input[name="_username"]', TEST_USER.email);
    await page.fill('input[name="_password"]', TEST_USER.password);
    await page.click('button[type="submit"].btn-login');
    
    // Navigate to new Analyse form
    await page.goto(`${BASE_URL}/analyse/new`);
    
    // Verify form elements
    await expect(page.locator('text=Nouvelle Analyse')).toBeVisible();
    await expect(page.locator('label:has-text("Date de l\'analyse")')).toBeVisible();
    await expect(page.locator('label:has-text("Expert / Technicien")')).toBeVisible();
    await expect(page.locator('label:has-text("Ferme")')).toBeVisible();
    await expect(page.locator('label:has-text("Résultat technique")')).toBeVisible();
    
    // Submit empty form to test validation
    await page.click('button[type="submit"]');
    
    // Verify validation errors
    await expect(page.locator('text=Veuillez sélectionner un technicien')).toBeVisible();
    await expect(page.locator('text=Veuillez sélectionner une ferme')).toBeVisible();
  });

  /**
   * Test 6: Conseil List Page
   * -------------------------
   * Verifies the Conseil CRUD - List with priority filtering.
   */
  test('T06: Conseil List - Priority filtering', async ({ page }) => {
    // Login
    await page.goto(`${BASE_URL}/login`);
    await page.fill('input[name="_username"]', TEST_USER.email);
    await page.fill('input[name="_password"]', TEST_USER.password);
    await page.click('button[type="submit"].btn-login');
    
    // Navigate to Conseil list
    await page.goto(`${BASE_URL}/conseil`);
    
    // Verify page title
    await expect(page.locator('text=Conseils Agricoles')).toBeVisible();
    await expect(page.locator('text=Recommandations et conseils par priorité')).toBeVisible();
    
    // Check priority filters
    await expect(page.locator('text=Toutes priorités')).toBeVisible();
    await expect(page.locator('text=🔴 Haute')).toBeVisible();
    await expect(page.locator('text=🟡 Moyenne')).toBeVisible();
    await expect(page.locator('text=🟢 Basse')).toBeVisible();
    
    // Verify priority counts
    await expect(page.locator('text=🔴 Haute priorité')).toBeVisible();
    await expect(page.locator('text=🟡 Moyenne priorité')).toBeVisible();
    await expect(page.locator('text=🟢 Basse priorité')).toBeVisible();
    
    // Check "Nouveau Conseil" button
    await expect(page.locator('text=Nouveau Conseil')).toBeVisible();
  });

  /**
   * Test 7: AI Diagnostic Feature
   * -----------------------------
   * Verifies AI-powered visual diagnosis is displayed for analyses with images.
   */
  test('T07: AI Diagnostic - Visual diagnosis display', async ({ page }) => {
    // Login
    await page.goto(`${BASE_URL}/login`);
    await page.fill('input[name="_username"]', TEST_USER.email);
    await page.fill('input[name="_password"]', TEST_USER.password);
    await page.click('button[type="submit"].btn-login');
    
    // Navigate to Analyse #11 (has AI diagnostic)
    await page.goto(`${BASE_URL}/analyse/11`);
    
    // Verify AI Diagnostic section
    await expect(page.locator('text=DIAGNOSTIC VISUEL IA')).toBeVisible();
    await expect(page.locator('text=Condition:')).toBeVisible();
    await expect(page.locator('text=Confiance:')).toBeVisible();
    await expect(page.locator('text=Urgence:')).toBeVisible();
    
    // Verify diagnostic details
    await expect(page.locator('text=Symptomes:')).toBeVisible();
    await expect(page.locator('text=Traitement:')).toBeVisible();
    await expect(page.locator('text=Prevention:')).toBeVisible();
  });

  /**
   * Test 8: PDF Export
   * ------------------
   * Verifies PDF export functionality for Analyses.
   */
  test('T08: PDF Export - Generate analyse report', async ({ page }) => {
    // Login
    await page.goto(`${BASE_URL}/login`);
    await page.fill('input[name="_username"]', TEST_USER.email);
    await page.fill('input[name="_password"]', TEST_USER.password);
    await page.click('button[type="submit"].btn-login');
    
    // Navigate to PDF export URL
    const response = await page.goto(`${BASE_URL}/admin/report/analyse/1/pdf`);
    
    // Verify PDF response
    expect(response?.status()).toBe(200);
    expect(response?.headers()['content-type']).toBe('application/pdf');
    
    // Verify Content-Disposition header indicates inline PDF
    const contentDisposition = response?.headers()['content-disposition'];
    expect(contentDisposition).toContain('inline');
    expect(contentDisposition).toContain('analyse-1.pdf');
  });

  /**
   * Test 9: Full CRUD Workflow
   * --------------------------
   * Tests a complete workflow: Create Analyse → View → Add Conseil → View PDF
   */
  test('T09: Complete workflow - Create Analyse with Conseil', async ({ page }) => {
    // Login
    await page.goto(`${BASE_URL}/login`);
    await page.fill('input[name="_username"]', TEST_USER.email);
    await page.fill('input[name="_password"]', TEST_USER.password);
    await page.click('button[type="submit"].btn-login');
    
    // Step 1: Navigate to new Analyse form
    await page.goto(`${BASE_URL}/analyse/new`);
    await expect(page.locator('text=Nouvelle Analyse')).toBeVisible();
    
    // Step 2: Fill in form
    await page.selectOption('select[name="analyse[technicien]"]', { index: 1 });
    await page.selectOption('select[name="analyse[ferme]"]', { index: 1 });
    await page.fill('textarea[name="analyse[resultatTechnique]"]', 
      'Test analyse created during E2E testing. Résultat technique détaillé.');
    
    // Step 3: Submit form
    await page.click('button[type="submit"]');
    
    // Step 4: Verify redirect to index with success
    await expect(page.locator('text=Analyses Agricoles')).toBeVisible();
    
    // Step 5: Verify new analyse appears in list
    await expect(page.locator('text=Test analyse created during E2E')).toBeVisible();
  });

  /**
   * Test 10: Navigation and UI
   * --------------------------
   * Verifies main navigation and UI elements are accessible.
   */
  test('T10: Navigation - Main menu access', async ({ page }) => {
    // Login
    await page.goto(`${BASE_URL}/login`);
    await page.fill('input[name="_username"]', TEST_USER.email);
    await page.fill('input[name="_password"]', TEST_USER.password);
    await page.click('button[type="submit"].btn-login');
    
    // Test navigation to each main section
    const sections = [
      { name: 'Tableau de bord', url: '/expert/dashboard' },
      { name: 'Analyses', url: '/analyse' },
      { name: 'Conseils', url: '/conseil' },
    ];
    
    for (const section of sections) {
      await page.goto(`${BASE_URL}${section.url}`);
      await expect(page.locator(`h1:has-text("${section.name}"), text=${section.name}`).first()).toBeVisible();
    }
  });
});

/**
 * Additional Test Suite: Error Handling
 * =====================================
 */
test.describe('Error Handling', () => {
  const BASE_URL = 'http://localhost:8000';
  
  test('E01: 404 page for non-existent Analyse', async ({ page }) => {
    // Login first
    await page.goto(`${BASE_URL}/login`);
    await page.fill('input[name="_username"]`, 'dinalad042@gmail.com');
    await page.fill('input[name="_password"]`, '15141213');
    await page.click('button[type="submit"].btn-login');
    
    // Try to access non-existent analyse
    const response = await page.goto(`${BASE_URL}/analyse/99999`);
    expect(response?.status()).toBe(404);
  });
});
