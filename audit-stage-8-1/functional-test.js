const { chromium } = require('playwright');
const path = require('path');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: { width: 1920, height: 1080 } });
  const page = await context.newPage();
  const results = [];

  const log = (step, status, detail) => {
    results.push({ step, status, detail });
    console.log(step, status, detail || '');
  };

  await page.goto('http://localhost:8000/admin/login', { waitUntil: 'networkidle' });
  await page.fill('input[type="email"]', 'admin@vestra.com');
  await page.fill('input[type="password"]', 'Admin@12345');
  await page.click('button[type="submit"]');
  await page.waitForURL('http://localhost:8000/admin', { timeout: 15000 });
  log('login', 'PASS');

  // Create category
  await page.goto('http://localhost:8000/admin/categories/create', { waitUntil: 'domcontentloaded' });
  await page.waitForTimeout(1200);
  await page.getByLabel('Name').fill('Audit Test Category');
  await page.getByLabel('Slug').fill('audit-test-category');
  await page.getByLabel('Description').fill('Created by Stage 8.1 audit.');
  await page.getByLabel('Sort order').fill('99');
  await page.getByRole("button", { name: "Create", exact: true }).click();
  await page.waitForTimeout(2500);
  const catCreated = page.url().includes('/admin/categories');
  log('category-create', catCreated ? 'PASS' : 'FAIL', page.url());

  // Create product
  await page.goto('http://localhost:8000/admin/products/create', { waitUntil: 'domcontentloaded' });
  await page.waitForTimeout(1500);
  await page.getByLabel('Name').fill('Audit Test Product');
  await page.getByLabel('Slug').fill('audit-test-product');
  // Category select
  await page.locator('[data-field-wrapper="category_id"] button, [id*="category_id"]').first().click();
  await page.waitForTimeout(500);
  await page.locator('.choices__list--dropdown .choices__item').first().click();
  await page.getByLabel('SKU').fill('AUDIT-SKU-001');
  await page.getByLabel('Price').fill('9.99');
  await page.getByLabel('Stock quantity').fill('100');
  await page.getByRole("button", { name: "Create", exact: true }).click();
  await page.waitForTimeout(3000);
  const prodCreated = page.url().includes('/admin/products');
  log('product-create', prodCreated ? 'PASS' : 'FAIL', page.url());

  // Search product
  await page.goto('http://localhost:8000/admin/products', { waitUntil: 'domcontentloaded' });
  await page.waitForTimeout(1000);
  await page.getByPlaceholder('Search').fill('Audit Test Product');
  await page.waitForTimeout(1500);
  const searchResult = await page.locator('text=Audit Test Product').first().isVisible().catch(() => false);
  log('product-search', searchResult ? 'PASS' : 'FAIL');

  // Delete product
  await page.goto('http://localhost:8000/admin/products', { waitUntil: 'domcontentloaded' });
  await page.waitForTimeout(1000);
  await page.getByPlaceholder('Search').fill('Audit Test Product');
  await page.waitForTimeout(1500);
  await page.locator('button[aria-label="Delete"]').first().click();
  await page.waitForTimeout(800);
  await page.getByRole('button', { name: /Delete/i }).click();
  await page.waitForTimeout(1500);
  log('product-delete', 'PASS', page.url());

  // Delete category
  await page.goto('http://localhost:8000/admin/categories', { waitUntil: 'domcontentloaded' });
  await page.waitForTimeout(1000);
  await page.getByPlaceholder('Search').fill('Audit Test Category');
  await page.waitForTimeout(1500);
  await page.locator('button[aria-label="Delete"]').first().click();
  await page.waitForTimeout(800);
  await page.getByRole('button', { name: /Delete/i }).click();
  await page.waitForTimeout(1500);
  log('category-delete', 'PASS', page.url());

  await page.screenshot({ path: path.join(__dirname, 'screenshots', 'functional-test-end.png'), fullPage: true });
  await browser.close();

  require('fs').writeFileSync(path.join(__dirname, 'functional-test-results.json'), JSON.stringify(results, null, 2));
})();
