const { chromium } = require('playwright');
const path = require('path');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: { width: 1920, height: 1080 } });
  const page = await context.newPage();
  await page.goto('http://localhost:8000/admin/login', { waitUntil: 'networkidle' });
  await page.fill('input[type="email"]', 'admin@vestra.com');
  await page.fill('input[type="password"]', 'Admin@12345');
  await page.click('button[type="submit"]');
  await page.waitForURL('http://localhost:8000/admin', { timeout: 15000 });
  await page.goto('http://localhost:8000/admin/categories', { waitUntil: 'domcontentloaded' });
  await page.waitForTimeout(1000);
  await page.getByPlaceholder('Search').fill('Audit Test Category');
  await page.waitForTimeout(1500);
  const del = await page.locator('button[aria-label="Delete"]').first();
  if (await del.isVisible().catch(() => false)) {
    await del.click();
    await page.waitForTimeout(500);
    await page.getByRole('button', { name: /Delete/i }).click();
    await page.waitForTimeout(1500);
    console.log('Deleted test category');
  } else {
    console.log('No test category found');
  }
  await browser.close();
})();
