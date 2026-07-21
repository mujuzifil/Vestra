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
  await page.goto('http://localhost:8000/admin/products/create', { waitUntil: 'domcontentloaded', timeout: 30000 });
  await page.waitForTimeout(3000);
  await page.screenshot({ path: path.join(__dirname, 'screenshots', 'products-create.png'), fullPage: true });
  console.log('Final URL:', page.url());
  console.log('Title:', await page.title());
  const heading = await page.$eval('h1', el => el.innerText).catch(() => null);
  console.log('Heading:', heading);
  await browser.close();
})();
