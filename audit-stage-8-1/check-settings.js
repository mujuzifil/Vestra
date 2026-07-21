const { chromium } = require('playwright');
const path = require('path');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1920, height: 1080 } });
  page.setDefaultNavigationTimeout(60000);
  await page.goto('http://localhost:8000/admin/login', { waitUntil: 'domcontentloaded' });
  await page.waitForTimeout(3000);
  await page.fill('input[type="email"]', 'admin@vestra.com');
  await page.fill('input[type="password"]', 'Admin@12345');
  await page.click('button[type="submit"]');
  await page.waitForURL('http://localhost:8000/admin', { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.waitForTimeout(2000);
  await page.goto('http://localhost:8000/admin/settings', { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.waitForTimeout(2000);
  console.log('URL:', page.url());
  console.log('Title:', await page.title());
  const heading = await page.$eval('h1', el => el.innerText).catch(() => null);
  console.log('Heading:', heading);
  await page.screenshot({ path: path.join(__dirname, 'screenshots', 'settings-list-fixed.png'), fullPage: true });
  // Try clicking first edit
  const editLink = await page.locator('a[href*="/admin/settings/"]').first();
  await editLink.click();
  await page.waitForTimeout(3000);
  console.log('Edit URL:', page.url());
  console.log('Edit Title:', await page.title());
  await page.screenshot({ path: path.join(__dirname, 'screenshots', 'settings-edit-fixed.png'), fullPage: true });
  await browser.close();
})();
