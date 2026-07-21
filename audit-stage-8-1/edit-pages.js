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

  const pages = [
    { name: 'products-edit', url: '/admin/products/149/edit' },
    { name: 'categories-edit', url: '/admin/categories/97/edit' },
    { name: 'roles-edit', url: '/admin/roles/118/edit' },
    { name: 'permissions-edit', url: '/admin/permissions/262/edit' },
  ];

  for (const p of pages) {
    try {
      await page.goto(`http://localhost:8000${p.url}`, { waitUntil: 'domcontentloaded', timeout: 30000 });
      await page.waitForTimeout(1500);
      await page.screenshot({ path: path.join(__dirname, 'screenshots', `${p.name}.png`), fullPage: true });
      console.log(p.name, '->', await page.title(), page.url());
    } catch (e) {
      console.error(p.name, 'ERROR', e.message);
      await page.screenshot({ path: path.join(__dirname, 'screenshots', `${p.name}-error.png`), fullPage: true });
    }
  }
  await browser.close();
})();
