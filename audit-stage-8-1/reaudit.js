const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const BASE_URL = 'http://localhost:8000';
const SCREENSHOT_DIR = path.join(__dirname, 'screenshots-stage82');
fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });

const routes = [
  { name: 'login', url: '/admin/login' },
  { name: 'dashboard', url: '/admin' },
  { name: 'products-list', url: '/admin/products' },
  { name: 'products-create', url: '/admin/products/create' },
  { name: 'categories-list', url: '/admin/categories' },
  { name: 'orders-list', url: '/admin/orders' },
  { name: 'customers-list', url: '/admin/customers' },
  { name: 'customers-view', url: '/admin/customers/1' },
  { name: 'reviews-list', url: '/admin/reviews' },
  { name: 'contact-messages-list', url: '/admin/contact-messages' },
  { name: 'customer-feedback-list', url: '/admin/customer-feedbacks' },
  { name: 'distributor-requests-list', url: '/admin/distributor-requests' },
  { name: 'administrators-list', url: '/admin/users' },
  { name: 'roles-list', url: '/admin/roles' },
  { name: 'permissions-list', url: '/admin/permissions' },
  { name: 'settings-list', url: '/admin/settings' },
  { name: 'settings-edit', url: '/admin/settings/1/edit' },
];

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1920, height: 1080 } });
  page.setDefaultNavigationTimeout(120000);
  const results = [];

  page.on('console', msg => {
    if (msg.type() === 'error') results.push({ type: 'console', page: page.url(), text: msg.text() });
  });
  page.on('pageerror', err => {
    results.push({ type: 'pageerror', page: page.url(), error: err.message });
  });

  await page.goto(`${BASE_URL}/admin/login`, { waitUntil: 'domcontentloaded' });
  await page.waitForTimeout(3000);
  await page.fill('input[type="email"]', 'admin@vestra.com');
  await page.fill('input[type="password"]', 'Admin@12345');
  await page.click('button[type="submit"]');
  await page.waitForURL(`${BASE_URL}/admin`, { waitUntil: 'domcontentloaded', timeout: 120000 });
  await page.waitForTimeout(2000);

  for (const route of routes.slice(1)) {
    try {
      const start = Date.now();
      await page.goto(`${BASE_URL}${route.url}`, { waitUntil: 'domcontentloaded', timeout: 120000 });
      await page.waitForTimeout(2000);
      const elapsed = Date.now() - start;
      const title = await page.title();
      const heading = await page.$eval('h1', el => el.innerText).catch(() => null);
      await page.screenshot({ path: path.join(SCREENSHOT_DIR, `${route.name}.png`), fullPage: true });
      results.push({ name: route.name, url: route.url, title, heading, elapsed, status: 'ok' });
      console.log(`${route.name}: ${elapsed}ms - ${title}`);
    } catch (e) {
      await page.screenshot({ path: path.join(SCREENSHOT_DIR, `${route.name}-error.png`), fullPage: true });
      results.push({ name: route.name, url: route.url, error: e.message, status: 'error' });
      console.log(`${route.name}: ERROR - ${e.message}`);
    }
  }

  await browser.close();
  fs.writeFileSync(path.join(__dirname, 'reaudit-results.json'), JSON.stringify(results, null, 2));
})();
