const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext();
  const page = await context.newPage();
  const results = [];

  // Unauthenticated admin access
  await page.goto('http://localhost:8000/admin/products', { waitUntil: 'domcontentloaded' });
  results.push({ check: 'unauth-admin-products-redirect', url: page.url(), pass: page.url().includes('/admin/login') });

  await page.goto('http://localhost:8000/admin/users', { waitUntil: 'domcontentloaded' });
  results.push({ check: 'unauth-admin-users-redirect', url: page.url(), pass: page.url().includes('/admin/login') });

  // CSRF token present on login
  await page.goto('http://localhost:8000/admin/login', { waitUntil: 'networkidle' });
  const csrf = await page.inputValue('input[name="_token"]').catch(() => null);
  results.push({ check: 'login-csrf-token', pass: !!csrf, tokenLength: csrf ? csrf.length : 0 });

  await browser.close();
  fs.writeFileSync(path.join(__dirname, 'security-checks.json'), JSON.stringify(results, null, 2));
  console.log(results);
})();
