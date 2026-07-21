const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const BASE_URL = 'http://localhost:8000';
const ADMIN_EMAIL = 'admin@vestra.com';
const ADMIN_PASSWORD = 'Admin@12345';
const SCREENSHOT_DIR = path.join(__dirname, 'screenshots');
const LOG_FILE = path.join(__dirname, 'audit-log.json');

const routes = [
  { name: 'login', url: '/admin/login', needsAuth: false },
  { name: 'dashboard', url: '/admin', needsAuth: true },
  { name: 'products-list', url: '/admin/products', needsAuth: true },
  { name: 'products-create', url: '/admin/products/create', needsAuth: true },
  { name: 'categories-list', url: '/admin/categories', needsAuth: true },
  { name: 'categories-create', url: '/admin/categories/create', needsAuth: true },
  { name: 'orders-list', url: '/admin/orders', needsAuth: true },
  { name: 'customers-list', url: '/admin/customers', needsAuth: true },
  { name: 'reviews-list', url: '/admin/reviews', needsAuth: true },
  { name: 'contact-messages-list', url: '/admin/contact-messages', needsAuth: true },
  { name: 'customer-feedback-list', url: '/admin/customer-feedbacks', needsAuth: true },
  { name: 'distributor-requests-list', url: '/admin/distributor-requests', needsAuth: true },
  { name: 'administrators-list', url: '/admin/users', needsAuth: true },
  { name: 'administrators-create', url: '/admin/users/create', needsAuth: true },
  { name: 'roles-list', url: '/admin/roles', needsAuth: true },
  { name: 'roles-create', url: '/admin/roles/create', needsAuth: true },
  { name: 'permissions-list', url: '/admin/permissions', needsAuth: true },
  { name: 'permissions-create', url: '/admin/permissions/create', needsAuth: true },
  { name: 'settings-list', url: '/admin/settings', needsAuth: true },
];

(async () => {
  fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });
  const results = [];

  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: { width: 1920, height: 1080 } });
  const page = await context.newPage();

  const consoleErrors = [];
  const pageErrors = [];

  page.on('console', msg => {
    if (msg.type() === 'error') {
      consoleErrors.push({ url: page.url(), type: msg.type(), text: msg.text() });
    }
  });
  page.on('pageerror', err => {
    pageErrors.push({ url: page.url(), error: err.message });
  });
  page.on('response', resp => {
    if (resp.status() >= 400) {
      results.push({ url: resp.url(), status: resp.status(), route: 'network-error' });
    }
  });

  // Capture login page
  await page.goto(`${BASE_URL}/admin/login`, { waitUntil: 'networkidle' });
  await page.screenshot({ path: path.join(SCREENSHOT_DIR, 'login.png'), fullPage: true });

  // Fill login form
  await page.fill('input[type="email"]', ADMIN_EMAIL);
  await page.fill('input[type="password"]', ADMIN_PASSWORD);
  await page.click('button[type="submit"]');
  await page.waitForURL(`${BASE_URL}/admin`, { timeout: 15000 });
  await page.waitForLoadState('networkidle');

  for (const route of routes.filter(r => r.needsAuth)) {
    const fullUrl = `${BASE_URL}${route.url}`;
    console.log(`Visiting ${route.name}: ${fullUrl}`);
    try {
      await page.goto(fullUrl, { waitUntil: 'networkidle', timeout: 20000 });
      await page.waitForTimeout(800);
      const finalUrl = page.url();
      const status = await page.evaluate(() => {
        const title = document.title;
        const errorHeading = document.querySelector('h1');
        return { title, heading: errorHeading ? errorHeading.innerText : null };
      });
      await page.screenshot({ path: path.join(SCREENSHOT_DIR, `${route.name}.png`), fullPage: true });
      results.push({
        name: route.name,
        url: route.url,
        finalUrl,
        title: status.title,
        heading: status.heading,
        status: 'visited',
        error: null,
      });
    } catch (e) {
      await page.screenshot({ path: path.join(SCREENSHOT_DIR, `${route.name}-error.png`), fullPage: true });
      results.push({
        name: route.name,
        url: route.url,
        finalUrl: page.url(),
        title: documentTitle(page),
        heading: null,
        status: 'error',
        error: e.message,
      });
    }
  }

  // Responsive screenshots for dashboard
  for (const size of [
    { name: 'tablet', width: 768, height: 1024 },
    { name: 'mobile', width: 375, height: 812 },
  ]) {
    await page.setViewportSize({ width: size.width, height: size.height });
    await page.goto(`${BASE_URL}/admin`, { waitUntil: 'networkidle' });
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, `dashboard-${size.name}.png`), fullPage: true });
  }

  await browser.close();

  fs.writeFileSync(LOG_FILE, JSON.stringify({
    visitedAt: new Date().toISOString(),
    results,
    consoleErrors,
    pageErrors,
  }, null, 2));

  console.log('Audit complete. Results:', LOG_FILE);
})();

function documentTitle(page) {
  try { return page.title(); } catch { return null; }
}
